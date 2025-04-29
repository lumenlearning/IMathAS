<?php

use PhpParser\Error;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\New_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

/**
 * Override variables declared from question code evals before generating
 * question text and answer boxes.
 *
 * We are currently using this to override answer shuffling globally in OHM.
 */
$onBeforeAnswerBoxGenerator = function () use (
    &$questionWriterVars // [?array] This is the array of variables packaged up by IMathAS.
) {
    if (isset($GLOBALS['CFG']['GEN']['noshuffle'])) {
        $questionWriterVars['noshuffle'] = $GLOBALS['CFG']['GEN']['noshuffle'];
    }
};

/**
 * Include additional feedback in scoring results.
 *
 * This method is defined this way because we need access to variables
 * dynamically created in the parent scope, without specifying those variable
 * names in the calling statement. The calling statement will be committed back
 * to IMathAS and most (all?) other IMathAS users may not define $feedback.
 */
if (!isset($feedback)) $feedback = null;
$onGetQuestion = function () use (
    // $this, // bound by default to any anonymous function in PHP, so long as the function is created within the class context
    &$question, // [Question] The question object to be returned by getQuestion().
    &$feedback, // [?array] The feedback for the question.
    &$evaledqtextwithoutanswerbox, // [string]
    &$quesData
)
{
    $question->setExtraData([
        'lumenlearning' => [
            'feedback' => (isset($feedback)) ? $feedback : null,
            'questionComponents' => [
                # TODO LO-1234: Complete me with more data!
                'text' => $evaledqtextwithoutanswerbox,
            ],
            'functionsCallsInCode' => detectFunctionCalls($quesData['control'])
        ]
    ]);
};


/**
 * Detects all function calls in a PHP code string using PHP-Parser
 *
 * @param string $code The PHP code to analyze
 * @return array List of detected function calls with line numbers and arguments
 */
function detectFunctionCalls($code): array {
    try {
        // Create parser instance
        $parser = (new ParserFactory)->createForHostVersion();

        // Parse the code
        $ast = $parser->parse($code);

        if (!$ast) {
            return ['error' => 'Failed to parse PHP code'];
        }

        // Initialize result array
        $functionCalls = [];

        // Create pretty printer for printing argument nodes
        $prettyPrinter = new PrettyPrinter();

        // Find all function calls in the AST
        $nodeFinder = new NodeFinder();

        // Process all node types in one NodeFinder call
        $allCalls = $nodeFinder->find($ast, function(Node $node) {
            return $node instanceof FuncCall ||
                $node instanceof MethodCall ||
                $node instanceof StaticCall ||
                $node instanceof New_;
        });

        foreach ($allCalls as $call) {
            // Extract arguments (common for all call types)
            $args = [];
            foreach ($call->args as $arg) {
                $args[] = $prettyPrinter->prettyPrintExpr($arg->value);
            }

            // Process based on call type
            if ($call instanceof FuncCall && $call->name instanceof Node\Name) {
                $functionCalls[] = [
                    'name' => $call->name->toString(),
                    'type' => 'function',
                    'line' => $call->getLine(),
                    'arguments' => $args
                ];
            }
            else if ($call instanceof MethodCall && $call->name instanceof Node\Identifier) {
                $functionCalls[] = [
                    'name' => $call->name->toString(),
                    'type' => 'method',
                    'line' => $call->getLine(),
                    'object' => $prettyPrinter->prettyPrintExpr($call->var),
                    'arguments' => $args
                ];
            }
            else if ($call instanceof StaticCall && $call->name instanceof Node\Identifier) {
                $className = $call->class instanceof Node\Name
                    ? $call->class->toString()
                    : $prettyPrinter->prettyPrintExpr($call->class);

                $functionCalls[] = [
                    'name' => $call->name->toString(),
                    'type' => 'static',
                    'class' => $className,
                    'line' => $call->getLine(),
                    'arguments' => $args
                ];
            }
            else if ($call instanceof New_ && $call->class instanceof Node\Name) {
                $functionCalls[] = [
                    'name' => $call->class->toString(),
                    'type' => 'constructor',
                    'line' => $call->getLine(),
                    'arguments' => $args
                ];
            }
        }

        return $functionCalls;
    } catch (Error $e) {
        return ['error' => 'Parse error: ' . $e->getMessage()];
    }
}

<?php

namespace App\Services\ohm;

use App\Services\Interfaces\QuestionCodeParserServiceInterface;

use PhpParser\Error;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\New_;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

class QuestionCodeParserService extends BaseService implements QuestionCodeParserServiceInterface
{
    private $parser;

    // Syntax Tree
    private $ast;

    private $prettyPrinter;

    private $nodeFinder;


    // @param string $code The PHP code to parse
    public function __construct($code) {
        // Create parser instance
        $this->parser = (new ParserFactory)->createForHostVersion();

        // Parse the code
        $this->ast = $this->parser->parse($code);

        // Create pretty printer for printing argument nodes
        $this->prettyPrinter = new PrettyPrinter();

        // Find all function calls in the AST
        $this->nodeFinder = new NodeFinder();
    }

    /**
     * Detects all function calls in a PHP code string using PHP-Parser
     *
     * @return array List of detected function calls with line numbers and arguments
     */
    public function detectFunctionCalls(): array {
        try {
            // Initialize result array
            $functionCalls = [];

            // Process all node types in one NodeFinder call
            $allCalls = $this->nodeFinder->find($this->ast, function(Node $node) {
                return $node instanceof FuncCall ||
                    $node instanceof MethodCall ||
                    $node instanceof StaticCall ||
                    $node instanceof New_;
            });

            foreach ($allCalls as $call) {
                // Extract arguments (common for all call types)
                $args = [];
                foreach ($call->args as $arg) {
                    $args[] = $this->prettyPrinter->prettyPrintExpr($arg->value);
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
                        'object' => $this->prettyPrinter->prettyPrintExpr($call->var),
                        'arguments' => $args
                    ];
                }
                else if ($call instanceof StaticCall && $call->name instanceof Node\Identifier) {
                    $className = $call->class instanceof Node\Name
                        ? $call->class->toString()
                        : $this->prettyPrinter->prettyPrintExpr($call->class);

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

}
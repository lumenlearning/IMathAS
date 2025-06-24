<?php

namespace App\Services\ohm;

use App\Services\Interfaces\QuestionCodeParserServiceInterface;

class QuestionCodeParserService extends BaseService implements QuestionCodeParserServiceInterface
{
    /*
     * NOTE: will match functions enclosed in strings
     *
     * ([^()\s]+)           - Captures function name, ignoring parens and whitespace (name capture group)
     * \( ... \)            - Function calls are wrapped in parens
     * ( ... )              - Capture entire args list (arguments capture group)
     * (?: ... )*           - Unnamed group of >= 0 args
     * \s*,?\s*             - Leading/Trailing comma and whitespaces permitted
     *  [^()]+              - Each argument must match at least 1 non-parens
     *  (?:\((?2)\))?       - followed by parens (indicating a function call) or not (?)
     */
    public const FUNCTION_REGEX = '/
        (
            [^()\s=]+
        )
        \(
        (
            (?:
                \s*,?\s*
                (?:
                    [^()]+
                    (?:
                        \((?2)\)
                    )?
                )
                \s*,?\s*
            )*
        )
        \)
    /x';

    private $code;

    /**
     * @param string $code The question code to parse (not proper PHP code)
     *                      **code is not explicitly parsed for validity**
     */
    public function __construct($code) {
        $this->code = $code;
    }

    /**
     * Detects all function calls in a question code string using Regex
     *
     * @return array List of detected function calls with name and arguments
     */
    public function detectFunctionCalls(): array {
        return $this->detectFunctionCallsRecurs($this->code, []);
    }

    /**
     * Determines if a question is algorithmic (contains randomization)
     * 
     * @return bool True if the question is algorithmic, false otherwise
     */
    public function isAlgorithmic(): bool {
        $functionCalls = $this->detectFunctionCalls();

        foreach ($functionCalls as $call) {
            $functionName = strtolower($call['name']);

            // Check if the function is in any of the randomness-generating function lists
            if (in_array($functionName, $this::RANDOM_NUMBER_FUNCTIONS) ||
                in_array($functionName, $this::RANDOM_SELECT_FUNCTIONS) ||
                in_array($functionName, $this::RANDOM_SHUFFLE_FUNCTIONS) ||
                in_array($functionName, $this::RANDOM_STRING_FUNCTIONS)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively detects all function calls in a question code string using Regex
     * Recursively traverses nested function calls
     *
     * @param $code string
     * @param $functionCalls array Running list of function calls detected
     *
     * @return array List of detected function calls with name and arguments
     */
    private function detectFunctionCallsRecurs($code, $functionCalls): array {
        preg_match_all($this::FUNCTION_REGEX, $code, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $args = $matches[2][$i];
            $functionCalls[] = [
                'name' => $matches[1][$i], // string type
                'arguments' => $args // string type
            ];
            $functionCalls = $this->detectFunctionCallsRecurs($args, $functionCalls);
        }

        return $functionCalls;
    }
}

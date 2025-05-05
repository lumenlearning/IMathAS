<?php

namespace App\Services\Interfaces;

interface QuestionCodeParserServiceInterface
{
    /**
     * Detects all function calls in a question code string using Regex
     *
     * Example return data:
     *   [
     *       {
     *           'name' => 'sum',
     *           'arguments' => "'1', '2', '3'"
     *       },
     *       {
     *           'name' => 'getName',
     *           'arguments' => ""
     *       }
     *   ]
     *
     * @return array List of detected function calls with name and arguments
     */
    public function detectFunctionCalls(): array;
}

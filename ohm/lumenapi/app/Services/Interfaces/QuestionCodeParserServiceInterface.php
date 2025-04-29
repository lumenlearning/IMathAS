<?php

namespace App\Services\Interfaces;

interface QuestionCodeParserServiceInterface
{
    /**
     * Detects all function calls in a PHP code string using PHP-Parser
     *
     *  Example return data:
     *  [
     *      {
     *          'name' => 'sum',
     *          'type' => 'function',
     *          'line' => 12,
     *          'arguments' => ['1', '2', '3']
     *      },
     *      {
     *          'name' => 'getName',
     *          'type' => 'static',
     *          'class' => NameGenerator,
     *          'line' => 6,
     *          'arguments' => []
     *      }
     *  ]
     *
     * @return array List of detected function calls with line numbers and arguments
     */
    public function detectFunctionCalls(): array;
}

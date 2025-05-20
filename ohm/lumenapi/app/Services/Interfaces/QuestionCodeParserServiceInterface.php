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

    /**
     * Determines if a question is algorithmic (contains randomization)
     * 
     * @return bool True if the question is algorithmic, false otherwise
     */
    public function isAlgorithmic(): bool;

    // Functions and methods that generate random numbers
    public const RANDOM_NUMBER_FUNCTIONS = [
        "rand",
        "rrand",
        "rands",
        "rrands",
        "nonzerorand",
        "nonzerorrand",
        "nonzerorands",
        "nonzerorrands",
        "diffrands",
        "diffrrands",
        "nonzerodiffrands",
        "nonzerodiffrrands",
        "randpythag"
    ];

    // Functions and methods that select randomly from values
    public const RANDOM_SELECT_FUNCTIONS = [
        "randfrom",
        "randsfrom",
        "jointrandfrom",
        "diffrandsfrom"
    ];

    // Functions and methods that shuffle randomly
    public const RANDOM_SHUFFLE_FUNCTIONS = [
        "singleshuffle",
        "jointshuffle"
    ];

    // Functions and methods that randomly generate strings
    public const RANDOM_STRING_FUNCTIONS = [
        "randname",
        "randnamewpronouns",
        "randmalename",
        "randfemalename",
        "randnames",
        "randmalenames",
        "randfemalenames",
        "randcity",
        "randcities",
        "randstate",
        "randstates",
        "randcountry",
        "randcountries"
    ];
}

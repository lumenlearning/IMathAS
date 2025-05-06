<?php

namespace OHM\Services;

use IMathAS\assess2\questions\answerboxes\AnswerBoxParams;

class AnswerBoxOhmUtilService
{

    /**
     * Given an array of variables and their values, as used in question code,
     * build and return a hash of all variable names and their values for use
     * by the question API.
     *
     * @param array $optionVariablesAndValues A hash of question variable names and their values.
     *                  Example: [
     *                      'noshuffle' => 'all',
     *                      'questions' => [
     *                          'First choice',
     *                          'Second choice',
     *                          'Third choice',
     *                          'All of the above'
     *                      ],
     *                  ]
     * @param array $variableNameRemap An array of variable names to rename. This may be empty.
     *                  Example (for a "choices" type question): [
     *                      // Rename "questions" to "choices".
     *                      'questions' => 'choices',
     *                      // Rename "randkeys" to "shuffledChoicesIndex".
     *                      'randkeys' => 'shuffledChoicesIndex',
     *                  ]
     * @param AnswerBoxParams $answerBoxParams An instance of AnswerBoxParams for the question.
     * @return array A hash of variable names and their values.
     *                  Example: [
     *                      'noshuffle' => 'all',
     *                      'answerformat' => '',
     *                      'choices' => [
     *                          'First choice',
     *                          'Second choice',
     *                          'Third choice',
     *                          'All of the above'
     *                      ],
     *                      'shuffledChoicesIndex' => [0, 1, 2, 3],
     *                  ]
     */
    public function formatAndReturnQuestionVariables(
        array           $optionVariablesAndValues,
        array           $variableNameRemap,
        AnswerBoxParams $answerBoxParams
    ): array
    {
        $variableNames = array_keys($optionVariablesAndValues);

        // Populate $optionVariables with all the option variables used for the question.
        $optionVariables = [];
        foreach ($variableNames as $variableName) {
            // Rename variable names if they appear in $variableNameRemap.
            $doRename = array_key_exists($variableName, $variableNameRemap);
            $optionVariableKey = $doRename ? $variableNameRemap[$variableName] : $variableName;

            $optionVariableValue = $optionVariablesAndValues[$variableName];
            $optionVariables[$optionVariableKey] = $optionVariableValue;
        }

        // Build our return data.
        $questionOptionVariables = [];
        if ($answerBoxParams->getIsMultiPartQuestion()) {
            // If these variables are for a part of a multipart question,
            // use a "qn1000" prefixed hash key.
            $partIndex = 1000 + $answerBoxParams->getQuestionPartNumber();
            $partQn = 'qn' . $partIndex;
            $questionOptionVariables[$partQn] = $optionVariables;
            $questionOptionVariables[$partQn]['answerboxPlaceholder'] = 'ANSWERBOX_PLACEHOLDER_QN_' . $partIndex;
        } else {
            // All single part questions will have a "qn0" hash key.
            $questionOptionVariables['qn0'] = $optionVariables;
            $questionOptionVariables['qn0']['answerboxPlaceholder'] = 'ANSWERBOX_PLACEHOLDER';
        }

        return $questionOptionVariables;
    }
}
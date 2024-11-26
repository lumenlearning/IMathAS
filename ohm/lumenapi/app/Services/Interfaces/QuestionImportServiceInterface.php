<?php

namespace App\Services\Interfaces;

use App\Exceptions\RecordNotFoundException;

interface QuestionImportServiceInterface
{
    /**
     * Create multiple questions from MGA question data.
     *
     * Example of MGA question data:
     *
     * [
     *     {
     *         "source_id": "219d3809-6a1b-44d6-a75a-45b856a923a9",
     *         "source_type": "mga_file",
     *         "type": "multiple_choice",
     *         "is_summative": false,
     *         "description": "In the fall of 2019, how many students attended college?",
     *         "text": "In the fall of 2019, how many students attended college?",
     *         "choices": [
     *             "20.1 million.",
     *             "42.0 million.",
     *             "12.3 million.",
     *             "9.8 million."
     *         ],
     *         "correct_answer": 1,
     *         "feedback": {
     *             "type": "per_answer",
     *             "feedbacks": [
     *                 "Incorrect. There were fewer students enrolled in fall 2019.",
     *                 "Correct! There were 42.0 million students enrolled in college in the fall of 2019, a 5% decrease from 2009.",
     *                 "Incorrect. There were many more students enrolled in 2019.",
     *                 "Incorrect. There were many more students enrolled in 2019."
     *             ]
     *         },
     *         "outcome": {
     *             "guid": "05a8773b-7c6d-4cf5-8f7c-ae4cb91e7b6e",
     *             "number": "1.1.1",
     *             "title": "Categories of Students"
     *         }
     *     }
     * ]
     *
     * Example return data:
     * [
     *     {
     *         'source_id' => '49fe45bd-c319-4813-b012-cbecaa663e5f',
     *         'status' => 'created',
     *         'questionset_id' => '5413',
     *         'errors' => [],
     *     },
     *     {
     *         'source_id' => '7f5137ee-2b92-44c7-abf0-7e930952b871',
     *         'status' => 'error',
     *         'questionset_id' => null,
     *         'errors' => ['Error message goes here.'],
     *     }
     * ]
     *
     * @param string $questionImportMode One of: quiz, practice
     * @param array $mgaQuestionArray An array of questions from an MGA file.
     * @param int $ownerId The OHM user ID to use for the owner of all questions.
     * @return array An array of source question IDs mapped to created OHM question IDs.
     * @throws RecordNotFoundException Thrown if the specified User ID is not found.
     * @see QuestionImportServiceTest constants for $mgaQuestionArray examples.
     */
    public function createMultipleQuestions(
        string $questionImportMode,
        array  $mgaQuestionArray,
        int    $ownerId
    ): array;
}

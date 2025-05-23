<?php

namespace App\Services\ohm;

use App\Exceptions\InvalidQuestionImportType;
use App\Exceptions\InvalidQuestionType;
use App\Exceptions\RecordNotFoundException;
use App\Repositories\Interfaces\LibraryItemRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\QuestionImportServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Sanitize;

/**
 * This is used by the MGA question import script in the "align" repo. (source_type = 'mga_file')
 * See: https://github.com/lumenlearning/align/blob/main/ohm_questions/ohm_question_importer.rb
 *
 * This is also accessed by the Skeletor system (in Lumen One) for assessment authoring via form submission (source_type = 'form_input')
 * See:https://github.com/lumenlearning/skeletor/blob/main/app/services/ohm/question_authoring_service.rb
 *
 * In places where $questionImportMode is used, currently two values are valid:
 *
 * - "quiz" - For Lumen One assessments. OHM-specific macros beginning with
 *            "ohm_" will be used.
 * - "practice" - For embedded practice or OHM 1 questions. Normal feedback
 *                macros will be used.
 */
class QuestionImportService extends BaseService implements QuestionImportServiceInterface
{
    private QuestionSetRepositoryInterface $questionSetRepository;
    private LibraryItemRepositoryInterface $libraryItemRepository;
    private UserRepositoryInterface $userRepository;

    private $defaultUseRights;

    public function __construct(
        QuestionSetRepositoryInterface $questionSetRepository,
        LibraryItemRepositoryInterface $libraryItemRepository,
        UserRepositoryInterface        $userRepository
    )
    {
        $this->questionSetRepository = $questionSetRepository;
        $this->libraryItemRepository = $libraryItemRepository;
        $this->userRepository = $userRepository;
        $this->defaultUseRights = getenv('NEW_QUESTION_DEFAULT_USE_RIGHTS') ?: 4;
    }

    /**
     * Create multiple questions from question data.
     *
     * Example of question data:
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
     *             "16.6 million.",
     *             "12.3 million.",
     *             "9.8 million."
     *         ],
     *         "correct_answer": 1,
     *         "feedback": {
     *             "type": "per_answer",
     *             "feedbacks": [
     *                 "Incorrect. There were fewer students enrolled in fall 2019.",
     *                 "Correct! There were 16.6 million students enrolled in college in the fall of 2019, a 5% decrease from 2009.",
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
     * @param array $questionArray An array of questions
     * @param int $ownerId The OHM user ID to use for the owner of all questions.
     * @return array An array of source question IDs mapped to created OHM question IDs.
     * @throws RecordNotFoundException Thrown if the specified User ID is not found.
     * @see QuestionImportServiceTest constants for $questionArray examples.
     */
    public function createMultipleQuestions(
        string $questionImportMode,
        array  $questionArray,
        int    $ownerId
    ): array
    {
        if (!in_array($questionImportMode, ['quiz', 'practice'])) {
            throw new InvalidQuestionImportType('Invalid question import mode "'
                . $questionImportMode . '". Must be one of: quiz, practice');
        }

        $user = $this->userRepository->findById($ownerId);
        if (!$user) {
            throw new RecordNotFoundException('User not found for user ID: ' . $ownerId);
        }

        $questionIds = [];
        foreach ($questionArray as $question) {
            try {
                $qid = $this->createSingleQuestion($questionImportMode, $question, $user);
                $questionIds[] = [
                    'source_id' => $question['source_id'],
                    'status' => 'created',
                    'questionset_id' => $qid,
                    'errors' => [],
                ];
            } catch (Exception $e) {
                $questionIds[] = [
                    'source_id' => $question['source_id'],
                    'status' => 'error',
                    'questionset_id' => null,
                    'errors' => [
                        $e->getMessage(),
                        $e->getTraceAsString()
                    ],
                ];
            }
        }

        return $questionIds;
    }

    /**
     * Create a single question and insert it into the imas_questionset table.
     *
     * @param string $questionImportMode One of: quiz, practice
     * @param array $question The question data.
     * @param array $user A row from imas_users representing the question author.
     * @return int The imas_questionset ID for the created question.
     * @throws InvalidQuestionType Thrown on unknown question types.
     */
    private function createSingleQuestion(string $questionImportMode,
                                          array  $question,
                                          array  $user
    ): int
    {
        $questionType = $question['type'];
        if ('multiple_choice' == $questionType) {
            $question = $this->buildMultipleChoiceQuestion($questionImportMode, $question);
        } else {
            throw new InvalidQuestionType('Unknown question type: ' . $questionType);
        }

        return $this->insertNewQuestion($question, $user);
    }

    /**
     * Build a multiple choice question.
     *
     * Notes:
     * - This only generates a question's description, text, and control.
     * - It does not persist anything to a DB.
     *
     * Example return data:
     * [
     *     'qtype' => 'choices',
     *     'description' => 'Question description goes here.',
     *     'qtext' => 'When is a good time for pizza?',
     *     'control' => 'Question $code and $feedback goes here'
     * ]
     *
     * @param string $questionImportMode One of: quiz, practice
     * @param array $questionData The question data.
     * @return array The question type, description, text, and control.
     */
    private function buildMultipleChoiceQuestion(string $questionImportMode, array $questionData): array
    {
        $questionDescription = $this->sanitizeInputText($questionData['description']);
        // imas_questionset.description is currently VARCHAR(254)
        $questionDescription = substr($questionDescription, 0, 200);
        
        // Allows distinction between source type in the question description
        // Helpful to embed the source_id for connecting the questions in OHM back to their source
        if ('mga_file' == $questionData['source_type']) {
            $questionDescription .= ' -- MGA_GUID:' . $questionData['source_id'];
        }
        else if ('form_input' == $questionData['source_type']) {
            $questionDescription .= ' -- FORM_SUBMISSION_GUID:' . $questionData['source_id'];
        }
        else {
            $questionDescription .= ' -- SOURCE_ID:' . $questionData['source_id'];
        }
        

        /*
         * Question text.
         */

        $questionText = $this->sanitizeInputText($questionData['text']);
        $questionText .= "\n\n" . '$answerbox' . "\n";

        if ('practice' == $questionImportMode) {
            $questionText .= '$feedback' . "\n";
        }

        /*
         * Question control.
         */

        $questionControl = '';

        // Load the OHM 2 macro library.
        if ('quiz' == $questionImportMode) {
            $questionControl .= 'loadlibrary("ohm_macros");' . "\n\n";
        }

        // Add question choices.
        for ($idx = 0; $idx < count($questionData['choices']); $idx++) {
            $rawChoice = $questionData['choices'][$idx];
            $safeChoice = $this->sanitizeInputText($rawChoice);
            $questionControl .= sprintf('$questions[%d] = \'%s\';%s',
                $idx, $safeChoice, "\n");
        }
        $questionControl .= "\n";

        // Define the correct answer.
        $questionControl .= '$answer = ' . $questionData['correct_answer'] . ";\n";

        /*
         * Feedback macros.
         */

        $macroPrefix = 'quiz' == $questionImportMode ? 'ohm_' : '';

        // Feedback macros.
        if ($this->hasPerAnswerFeedback($questionData)) {
            // Add a per-answer feedback macro if feedback is present.
            $feedbackMacro = $this->buildMultipleChoicePerAnswerFeedback(
                $questionImportMode, $questionData);
            $questionControl .= "\n" . $feedbackMacro;
        } else if (!$this->hasFeedback($questionData)) {
            // Add a basic feedback macro if no feedback is present.
            $feedbackMacro = '$feedback = ' . $macroPrefix . 'getfeedbackbasic('
                . '$stuanswers[$thisq], "Correct!", "Incorrect.", $answer);' . "\n";
            $questionControl .= "\n" . $feedbackMacro;
        }

//        $sourceId = $questionData['source_id'];
//        Log::debug(sprintf('(source ID: %s) Generated question control: %s',
//            $sourceId, $questionControl));
//        Log::debug(sprintf('(source ID: %s) Generated question text: %s',
//            $sourceId, $questionText));

        return [
            'qtype' => 'choices',
            'description' => $questionDescription,
            'qtext' => $questionText,
            'control' => $questionControl,
        ];
    }

    /**
     * Build a question code snippet that uses the OHM 2 macro
     * "ohm_getfeedbacktxt" with the provided question feedback.
     *
     * @param string $questionImportMode One of: quiz, practice
     * @param array $questionData The question data.
     * @return string The feedback text and macro snippet.
     */
    private function buildMultipleChoicePerAnswerFeedback(
        string $questionImportMode, array $questionData): string
    {
        if (!$this->hasPerAnswerFeedback($questionData)) {
            return '';
        }

        $macroPrefix = 'quiz' == $questionImportMode ? 'ohm_' : '';

        $allFeedback = $questionData['feedback']['feedbacks'];
        $correctAnswerIndex = $questionData['correct_answer'];
        $allFeedback = $this->replaceEmptyFeedback($allFeedback, $correctAnswerIndex);

        $macroCode = '';
        for ($idx = 0; $idx < count($allFeedback); $idx++) {
            $rawFeedback = $allFeedback[$idx];
            $safeFeedback = $this->sanitizeInputText($rawFeedback);
            $macroCode .= sprintf('$feedbacktxt[%d] = \'%s\';%s',
                $idx, $safeFeedback, "\n");
        }
        $macroCode .= "\n";

        $macroCode .= '$feedback = ' . $macroPrefix . 'getfeedbacktxt('
            . '$stuanswers[$thisq], $feedbacktxt, $answer);' . "\n";

        return $macroCode;
    }

    /**
     * Replace null or empty feedback strings with "Correct." or
     * "Incorrect.".
     *
     * @param array $feedbacks An array of feedback strings.
     * @param int $correctAnswerIndex The correct answer index, 0-indexed.
     * @return array The array of feedback strings with nulls replaced.
     */
    private function replaceEmptyFeedback(array $feedbacks, int $correctAnswerIndex): array
    {
        $processedFeedback = [];
        foreach ($feedbacks as $idx => $singleFeedback) {
            if (empty($singleFeedback)) {
                $processedFeedback[$idx] = $idx == $correctAnswerIndex
                    ? 'Correct.' : 'Incorrect.';
            } else {
                $processedFeedback[$idx] = $singleFeedback;
            }
        }
        return $processedFeedback;
    }

    /**
     * Insert a new question into imas_questionset as a question without
     * an assigned library.
     *
     * @param array $questionData The question's type, description, text, and control.
     *                            Example array:
     *                             [
     *                                 'qtype' => 'choices',
     *                                 'description' => 'Question description goes here.',
     *                                 'qtext' => 'When is a good time for pizza?',
     *                                 'control' => 'Question $code and $feedback goes here'
     *                             ]
     * @param array $user A row from imas_users representing the question author.
     * @return int The inserted question's ID.
     * @throws Exception Thrown on errors while inserting rows into the DB.
     */
    private function insertNewQuestion(array $questionData, array $user): int
    {
        $authorId = $user['id'];
        $authorName = $user['FirstName'] . ' ' . $user['LastName'];

        $uniqueId = $this->generateUniqueId();
        $useRights = $this->defaultUseRights;

        try {
            DB::beginTransaction();

            // Insert the new question.
            $imasQuestionsetData = [
                'uniqueid' => $uniqueId,
                'adddate' => time(),
                'lastmoddate' => time(),
                'ownerid' => $authorId,
                'author' => $authorName,
                'userights' => $useRights,
                'license' => 1,
                'description' => $questionData['description'],
                'qtype' => $questionData['qtype'],
                'control' => $questionData['control'],
                'qcontrol' => '',
                'qtext' => $questionData['qtext'],
                'answer' => '',
                'solution' => '',
                'extref' => '',
                'ancestors' => '',
                'ancestorauthors' => '',
                'otherattribution' => '',
                'isrand' => false,
            ];
            $questionSetId = $this->questionSetRepository->create($imasQuestionsetData);

            // Questions are not searchable in the OHM UI without also
            // inserting a row into imas_library_items.
            $libraryItem = [
                'libid' => 0, // 0 is the "unassigned" library.
                'qsetid' => $questionSetId,
                'ownerid' => $authorId,
                'junkflag' => 0,
                'lastmoddate' => $imasQuestionsetData['lastmoddate'],
                'deleted' => 0,
            ];
            $libraryItemId = $this->libraryItemRepository->create($libraryItem);

//            Log::debug(sprintf('Inserted question ID: %d, Library item id: %d',
//                $questionSetId, $libraryItemId));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(
                sprintf(
                    'Failed to insert new question. Error: %s, Trace: %s',
                    $e->getMessage(), $e->getTraceAsString()
                )
            );
            throw $e;
        }

        return $questionSetId;
    }

    /**
     * Generate a unique question ID as generated by MOM.
     *
     * @return int A unique question ID.
     */
    private function generateUniqueId(): int
    {
        // Source: course/moddataset.php
        $mt = microtime();
        return substr($mt, 11) . substr($mt, 2, 6);
    }

    /**
     * Escape and encode a string for safe usage in question code.
     *
     * @param string $data The string to encode.
     * @return string The encoded string.
     */
    private function sanitizeInputText(string $data): string
    {
        $transformations = [
            // Replaces smart quotes (aka curly quotes) with normal (straight) quotes
            // This should be done before quotes are escaped
            fn(string $text) => Sanitize::replaceSmartQuotes($text),

            // Escape single and double quotes. This escaping method is
            // easier for question writers to edit later.
            fn(string $text) => addslashes($text),

            // Escape $ characters to avoid unintentional references to
            // non-existent variables.
            fn(string $text) => str_replace('$', '\$', $text),

            // Remove HTML tags that are not supported (uses allow list)
            // This helps protect against malicious HTML potentially input by users
            fn(string $text) => strip_tags($text, $GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'])
        ];

        return array_reduce($transformations, fn($carry, $fn) => $fn($carry), $data);
    }

    /**
     * Determine if question data has per_answer feedback.
     *
     * @param array $questionData The question data as a JSON array.
     * @return bool True if question has per_answer feedback. False if not.
     * @see QuestionImportServiceTest constants for $questionData examples.
     */
    private function hasPerAnswerFeedback(array $questionData): bool
    {
        return !empty($questionData['feedback'])
            && 'per_answer' == $questionData['feedback']['type'];
    }

    /**
     * Determine if question data has ANY type of feedback.
     *
     * @param array $questionData The question data as a JSON array.
     * @return bool True if feedback exists. False if not.
     * @see QuestionImportServiceTest constants for $questionData examples.
     */
    private function hasFeedback(array $questionData): bool
    {
        return !empty($questionData['feedback']);
    }
}
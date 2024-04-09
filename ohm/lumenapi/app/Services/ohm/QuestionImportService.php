<?php

namespace App\Services\ohm;

use App\Exceptions\InvalidQuestionType;
use App\Exceptions\RecordNotFoundException;
use App\Repositories\Interfaces\LibraryItemRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\QuestionImportServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Create multiple questions
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
     * @param array $mgaQuestionArray An array of questions from an MGA file.
     * @param int $ownerId The OHM user ID to use for the owner of all questions.
     * @return array An array of source question IDs mapped to created OHM question IDs.
     * @throws RecordNotFoundException Thrown if the specified User ID is not found.
     * @see QuestionImportServiceTest constants for $mgaQuestionArray examples.
     */
    public function createMultipleQuestions(array $mgaQuestionArray, int $ownerId): array
    {
        $user = $this->userRepository->findById($ownerId);
        if (!$user) {
            throw new RecordNotFoundException('User not found for user ID: ' . $ownerId);
        }

        $questionIds = [];
        foreach ($mgaQuestionArray as $mgaQuestion) {
            try {
                $qid = $this->createSingleQuestion($mgaQuestion, $user);
                $questionIds[] = [
                    'source_id' => $mgaQuestion['source_id'],
                    'status' => 'created',
                    'questionset_id' => $qid,
                    'errors' => [],
                ];
            } catch (Exception $e) {
                $questionIds[] = [
                    'source_id' => $mgaQuestion['source_id'],
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
     * @param array $mgaQuestion The question data.
     * @param array $user A row from imas_users representing the question author.
     * @return int The imas_questionset ID for the created question.
     * @throws InvalidQuestionType Thrown on unknown question types.
     */
    private function createSingleQuestion(array $mgaQuestion, array $user): int
    {
        $questionType = $mgaQuestion['type'];
        if ('multiple_choice' == $questionType) {
            $question = $this->buildMultipleChoiceQuestion($mgaQuestion);
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
     * @param array $mgaQuestionData The question data.
     * @return array The question type, description, text, and control.
     */
    private function buildMultipleChoiceQuestion(array $mgaQuestionData): array
    {
        $questionDescription = $this->replaceSmartQuotes($mgaQuestionData['description']);
        // imas_questionset.description is currently VARCHAR(254)
        $questionDescription = substr($questionDescription, 0, 200);
        $questionDescription .= ' -- MGA_GUID:' . $mgaQuestionData['source_id'];

        $questionText = $this->replaceSmartQuotes($mgaQuestionData['text']);
        $questionText .= "\n\n" . '$answerbox' . "\n";

        // Load the OHM 2 macro library.
        $questionControl = 'loadlibrary("ohm_macros");' . "\n\n";

        // Add question choices.
        for ($idx = 0; $idx < count($mgaQuestionData['choices']); $idx++) {
            $rawChoice = $mgaQuestionData['choices'][$idx];
            $choiceWithoutSmarts = $this->replaceSmartQuotes($rawChoice);
            $safeChoice = $this->encodeForQuestionCode($choiceWithoutSmarts);
            $questionControl .= sprintf('$questions[%d] = \'%s\';%s',
                $idx, $safeChoice, "\n");
        }
        $questionControl .= "\n";

        // Define the correct answer.
        $questionControl .= '$answer = ' . $mgaQuestionData['correct_answer'] . ";\n";

        // Feedback macros.
        if ($this->hasPerAnswerFeedback($mgaQuestionData)) {
            // Add a per-answer feedback macro if feedback is present.
            $feedbackMacro = $this->buildMultipleChoicePerAnswerFeedback($mgaQuestionData);
            $questionControl .= "\n" . $feedbackMacro;
        } else if (!$this->hasFeedback($mgaQuestionData)) {
            // Add a basic feedback macro if no feedback is present.
            $feedbackMacro = '$feedback = ohm_getfeedbackbasic('
                . '$stuanswers[$thisq], "Correct!", "Incorrect.", $answer);' . "\n";
            $questionControl .= "\n" . $feedbackMacro;
        }

        $mgaSourceId = $mgaQuestionData['source_id'];
        Log::debug(sprintf('(MGA source ID: %s) Generated question control: %s',
            $mgaSourceId, $questionControl));
        Log::debug(sprintf('(MGA source ID: %s) Generated question text: %s',
            $mgaSourceId, $questionText));

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
     * @param array $questionData The question data.
     * @return string The feedback text and macro snippet.
     */
    private function buildMultipleChoicePerAnswerFeedback(array $questionData): string
    {
        if (!$this->hasPerAnswerFeedback($questionData)) {
            return '';
        }

        $macroCode = '';

        $allFeedback = $questionData['feedback']['feedbacks'];
        for ($idx = 0; $idx < count($allFeedback); $idx++) {
            $rawFeedback = $allFeedback[$idx];
            $feedbackNoSmarts = $this->replaceSmartQuotes($rawFeedback);
            $safeFeedback = $this->encodeForQuestionCode($feedbackNoSmarts);
            $macroCode .= sprintf('$feedbacktxt[%d] = \'%s\';%s',
                $idx, $safeFeedback, "\n");
        }
        $macroCode .= "\n";

        $macroCode .= '$feedback = ohm_getfeedbacktxt('
            . '$stuanswers[$thisq], $feedbacktxt, $answer);' . "\n";

        return $macroCode;
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

            Log::debug(sprintf('Inserted question ID: %d, Library item id: %d',
                $questionSetId, $libraryItemId));

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
    private function encodeForQuestionCode(string $data): string
    {
        // Escape single and double quotes. This escaping method is
        // easier for question writers to edit later.
        $escapedData = addslashes($data);

        // Escape $ characters to avoid unintentional references to
        // non-existent variables.
        $escapedData = str_replace('$', '\$', $escapedData);

        // Convert all characters which have HTML character entity
        // equivalents HTML entities. Example: & becomes &amp;
        // Single and double quotes are excluded because we escaped
        // them earlier.
        $entitiesFlags = ENT_SUBSTITUTE | ENT_HTML401;
        $sanitizedData = htmlentities($escapedData, $entitiesFlags, null, false);
        return $sanitizedData;
    }

    /**
     * Determine if MGA question data has per_answer feedback.
     *
     * @param array $mgaQuestionData The MGA question data as a JSON array.
     * @return bool True if question has per_answer feedback. False if not.
     * @see QuestionImportServiceTest constants for $mgaQuestionData examples.
     */
    private function hasPerAnswerFeedback(array $mgaQuestionData): bool
    {
        return !empty($mgaQuestionData['feedback'])
            && 'per_answer' == $mgaQuestionData['feedback']['type'];
    }

    /**
     * Determine if MGA question data has ANY type of feedback.
     *
     * @param array $mgaQuestionData The MGA question data as a JSON array.
     * @return bool True if feedback exists. False if not.
     * @see QuestionImportServiceTest constants for $mgaQuestionData examples.
     */
    private function hasFeedback(array $mgaQuestionData): bool
    {
        return !empty($mgaQuestionData['feedback']);
    }

    /**
     * Replace "smart quotes" and related "smart" characters with their
     * "normal" equivalents.
     *
     * All question descriptions, controls, and texts must be run through
     * this method before escaping, sanitizing, and saving to the DB.
     *
     * This method is a direct copy of the function named "stripsmartquotes"
     * found in course/moddataset.php.
     *
     * @param string $text
     * @return string The provided text with "smart" characters removed.
     */
    private function replaceSmartQuotes(string $text): string
    {
        return str_replace(
            [
                "\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d",
                "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"
            ],
            [
                "'", "'", '"', '"',
                '-', '--', '...'
            ],
            $text
        );
    }
}
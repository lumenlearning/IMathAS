<?php

namespace App\Services\ohm;

require_once __DIR__ . '/../../../../../assess2/AssessStandalone.php';
require_once __DIR__ . '/../../../../../assess2/questions/models/QuestionParams.php';
require_once __DIR__ . '/../../../../../assess2/questions/models/ShowAnswer.php';
require_once __DIR__ . '/../../../../../assess2/questions/QuestionGenerator.php';
require_once __DIR__ . '/../../../../../filter/filter.php';

use App\Dtos\QuestionScoreDto;
use AssessStandalone;
use App\Services\Interfaces\QuestionServiceInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use IMathAS\assess2\questions\models\Question;
use IMathAS\assess2\questions\models\QuestionParams;
use IMathAS\assess2\questions\QuestionGenerator;
use PDO;
use Rand;
use Tests\fixtures\Services\QuestionServiceFixtures;

class QuestionService extends BaseService implements QuestionServiceInterface
{

    private PDO $DBH;

    private Rand $RND;

    private QuestionSetRepositoryInterface $questionSetRepository;

    /**
     * Constructor.
     * @param QuestionSetRepositoryInterface $questionSetRepository
     */
    public function __construct(QuestionSetRepositoryInterface $questionSetRepository)
    {
        $this->DBH = app('db')->getPdo();
        $this->RND = $GLOBALS['RND'];
        $this->questionSetRepository = $questionSetRepository;
    }

    /**
     * Get the answers for multiple questions.
     *
     * @param array $questionSetIdsAndSeeds imas_questionset IDs and their seeds.
     *                  Example: [
     *                      [
     *                          "questionSetId" => 42,
     *                          "seed" => 1234,
     *                      ],
     *                      [
     *                          "questionSetId" => 43,
     *                          "seed" => 4321,
     *                      ]
     *                  ]
     * @return array An associative array with answers to all questions.
     * @see QuestionServiceFixtures::QUESTIONS_WITH_ANSWERS for full example return data.
     */
    public function getQuestionsWithAnswers(array $questionSetIdsAndSeeds): array
    {
        // Load question data for all questions from the DB.
        $questionSetIds = array_map(fn($idsAndSeeds): int => $idsAndSeeds['questionSetId'],
            $questionSetIdsAndSeeds);
        $questionSetRows = $this->questionSetRepository->getAllByQuestionId($questionSetIds);

        // Process each question and seed, one at a time.
        $allQuestionAnswers = [];
        foreach ($questionSetIdsAndSeeds as $idAndSeed) {
            $id = $idAndSeed['questionSetId'];
            $seed = $idAndSeed['seed'];

            $questionSetRow = $this->getQuestionSetRowFromResultSet($questionSetRows, $id);

            // If the client provided an invalid imas_questionset ID, skip it.
            if (is_null($questionSetRow)) {
                $allQuestionAnswers[] = [
                    'questionSetId' => $id,
                    'seed' => $seed,
                    'errors' => 'This questionSetId does not exist.',
                ];
                continue;
            }

            $questionType = $questionSetRow['qtype'];
            $uniqueId = $questionSetRow['uniqueid'];
            $ohmUniqueId = base_convert($uniqueId, 10, 32);
            $isAlgorithmic = 1 == $questionSetRow['isrand'];

            // Get question and correct answers. This evals the question code twice.
            $questionAndAnswers = $this->getQuestionAndAnswers($id, $seed, $questionSetRow);
            $question = $questionAndAnswers['question'];
            $correctAnswers = $questionAndAnswers['correctAnswers'];

            // We use this to get all the part types later (for multi-part
            // questions), so this needs to be sorted to match question part order.
            $jsParamsSorted = $question->getJsParams();
            ksort($jsParamsSorted, SORT_NATURAL);

            $editableValidations = $this->validateIsEditable($question, $questionSetRow);

            $hasExtraData = $question->getExtraData() !== null && is_array($question->getExtraData());
            $lumenlearningData = $hasExtraData && $question->getExtraData()['lumenlearning'] != null ? $question->getExtraData()['lumenlearning'] : [];
            $questionComponents = $lumenlearningData['questionComponents'] ?? [];

            // Build the question answer(s) and/or error(s) array.
            $answerData = [
                'questionSetId' => $id,
                'ohmUniqueId' => $ohmUniqueId,
                'questionType' => $questionType,
                'seed' => $seed,
                'html' => $question->getQuestionContent(),
                'jsParams' => $jsParamsSorted,
                'correctAnswers' => $correctAnswers,
                'showAnswerText' => $question->getCorrectAnswersForParts(),
                'uniqueid' => $uniqueId,
                'isAlgorithmic' => $isAlgorithmic,
                'feedback' => null,
                'questionComponents' => $this->cleanQuestionComponents($questionComponents),
                'errors' => $question->getErrors(),
                'editableValidations' => $editableValidations,
                'isEditable' => count($editableValidations) == 0
            ];

            // Get OHM 2 feedback macro text, if any exists.
            $extraData = $question->getExtraData();
            if (isset($extraData['lumenlearning']['feedback'])) {
                $answerData['feedback'] = $extraData['lumenlearning']['feedback'];
            }

            // For multi-part type questions, get all the part types.
            if ('multipart' == $questionType) {
                $answerData['partTypes'] = [];
                foreach ($jsParamsSorted as $jsParams) {
                    if (!isset($jsParams['qtype'])) continue; // Skip things like "submitall".
                    $answerData['partTypes'][] = $jsParams['qtype'];
                }
            }

            // Provide correct answers in an alternate format.
            $answerData['answerDataByQnIdentifier'] =
                $this->generateAnswerDataByQnIdentifier($answerData);

            $allQuestionAnswers[] = $answerData;
        }

        return $allQuestionAnswers;
    }

    /**
     * Generate question answer data by "qn" identifier. ("qn0", "qn1000", "qn1001", etc)
     *
     * Example:
     *     {
     *         "qn1000": {
     *             "questionType": "calculated",
     *             "correctAnswer": 300,
     *             "showAnswerText": "300"
     *         },
     *         "qn1001": {
     *             "questionType": "choices",
     *             "correctAnswer": "0",
     *             "showAnswerText": "100"
     *         },
     *         "qn1002": {
     *             "questionType": "essay",
     *             "correctAnswer": null,
     *             'showAnswerText": ""
     *         },
     *         "qn1003": {
     *             "questionType": "matching",
     *             "correctAnswer": [1,2,3],
     *             "showAnswerText": [
     *                 "1",
     *                 "2",
     *                 "3"
     *             ]
     *         },
     *         "qn1004": {
     *             "questionType": "multans",
     *             "correctAnswer": "0,2,4,5",
     *             "showAnswerText": [
     *                 "60% of product folks are under-caffeinated",
     *                 "10% of people do not like candy",
     *                 "95% of campers are happy",
     *                 "4 out of 5 sloths say disco is their favorite music genre"
     *             ]
     *         },
     *         "qn1006": {
     *             "questionType": "numfunc",
     *             "correctAnswers": 9,
     *             "showAnswerText": "`9`"
     *         },
     *         "qn1007": {
     *             "questionType": "string",
     *             "correctAnswers": "A,B,C,D,E,F",
     *             "showAnswerText": "A,B,C,D,E,F"
     *         }
     *     }
     *
     * @param array $answerData The complete answer data for a question.
     * @return array The answer data by "qn" identifier.
     */
    private function generateAnswerDataByQnIdentifier(array $answerData): array
    {
        $dataByQnIdentifier = [];

        /*
         * Handle single-part questions.
         */

        if ('multipart' != $answerData['questionType']) {
            $dataByQnIdentifier = [
                'qn0' => [
                    'questionType' => $answerData['questionType'],
                    'correctAnswer' => $answerData['correctAnswers'],
                    'showAnswerText' => $answerData['showAnswerText'],
                ]
            ];
            return $dataByQnIdentifier;
        }

        /*
         * Handle multi-part questions.
         */

        $jsParamsKeys = array_keys($answerData['jsParams']);
        foreach ($jsParamsKeys as $paramIdx => $partIdx) {
            if (!is_numeric($partIdx)) {
                continue; // Skip things like "submitall".
            }

            $qnIdx = 'qn' . $partIdx;
            $dataByQnIdentifier[$qnIdx]['questionType'] = $answerData['partTypes'][$paramIdx];
            $dataByQnIdentifier[$qnIdx]['correctAnswer'] = $answerData['correctAnswers'][$paramIdx];

            if (isset($answerData['showAnswerText'][$paramIdx])) {
                $dataByQnIdentifier[$qnIdx]['showAnswerText'] = $answerData['showAnswerText'][$paramIdx];
            }
        }

        return $dataByQnIdentifier;
    }

    /**
     * Get a specific imas_questionset row from an array of rows.
     *
     * @param array $resultSet An array of rows from imas_questionset, as associative arrays.
     * @param int $questionSetId The questionset ID to retrieve.
     * @return array|null The specified row. Null if not found.
     */
    private function getQuestionSetRowFromResultSet(array $resultSet, int $questionSetId): ?array
    {
        // This is currently performant enough for our needs.
        foreach ($resultSet as $row) {
            if ($questionSetId == $row['id']) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Get a Question object using provided imas_questionset row data.
     *
     * @param array $questionSetRowData The complete imas_questionset row as an associative array.
     * @param int $seed The question's seed.
     * @return Question A Question object.
     */
    private function getQuestionFromRowData(array $questionSetRowData, int $seed): Question
    {
        // Get an instance of the question generator setup with the question.
        $questionParams = $this->generateQuestionParams($questionSetRowData, $seed);
        $questionGenerator = new QuestionGenerator($this->DBH, $this->RND, $questionParams);

        // This evals the question code and stores the answer.
        $question = $questionGenerator->getQuestion();
        return $question;
    }

    /**
     * Generate a QuestionParams object for use with QuestionGenerator.
     *
     * @param array $questionData The complete imas_questionset row as an associative array.
     * @param int $seed The question's seed.
     * @return QuestionParams An instance of QuestionParams.
     */
    private function generateQuestionParams(array $questionData,
                                            int   $seed
    ): QuestionParams
    {
        $questionSetId = $questionData['id'];

        $questionParams = new QuestionParams();
        $questionParams
            ->setDbQuestionSetId($questionSetId)
            ->setQuestionData($questionData)
            ->setQuestionNumber(0)
            ->setDisplayQuestionNumber(0)
            ->setQuestionId(0)
            ->setAssessmentId(0)
            ->setQuestionSeed($seed)
            ->setShowHints(7)
            ->setShowAnswer(1)
            ->setShowAnswerParts([])
            ->setShowAnswerButton(true)
            ->setStudentAttemptNumber(0)
            ->setStudentPartAttemptCount([])
            ->setAllQuestionAnswers([])
            ->setAllQuestionAnswersAsNum([])
            ->setScoreNonZero([28 => -1])
            ->setScoreIsCorrect([28 => -1])
            ->setLastRawScores([])
            ->setSeqPartDone([])
            ->setTeacherInGb(false)
            ->setCorrectAnswerWrongFormat([]);

        return $questionParams;
    }

    /**
     * Get all question data and the correct answers for a question and its seed.
     *
     * The question code will be eval'd twice, using QuestionGenerator and ScoreEngine.
     *
     * @param int $questionSetId The imas_questionset row ID for the question.
     * @param int|string $seed The question's seed.
     * @param array $questionSetRowData The question's row data from imas_questionset
     *                                  as an associative array.
     * @param int $questionPartCount The number of parts in this question. 0 for single part.
     * @return array An array of correct answers for all parts.
     */
    private function getQuestionAndAnswers(int        $questionSetId,
                                           int|string $seed,
                                           array      $questionSetRowData,
                                           int        $questionPartCount = 0
    ): array
    {
        if (0 == $questionPartCount) {
            // For QuestionScoreDto constructor.
            $inputState = [
                'questionSetId' => $questionSetId,
                'seed' => $seed,
                'studentAnswers' => [],
                'studentAnswerValues' => [],
                'post' => [
                    [
                        'name' => 'qn0',
                        'value' => '0',
                    ],
                ],
            ];
        } else {
            $partsToScore = array_fill(0, $questionPartCount, 1);
            $studentAnswers = array_fill(0, $questionPartCount, 0);

            // For QuestionScoreDto constructor.
            $inputState = [
                'questionSetId' => $questionSetId,
                'seed' => $seed,
                'studentAnswers' => $studentAnswers,
                'studentAnswerValues' => $studentAnswers,
                'post' => [
                    [
                        'name' => 'qn0',
                        'value' => '0',
                    ],
                    [
                        'name' => 'qn1000',
                        'value' => 0,
                    ],
                ],
                'partAttemptNumber' => $partsToScore,
                'partsToScore' => $partsToScore,
            ];
        }
        $scoreDto = new QuestionScoreDto($inputState);

        $assessStandalone = new AssessStandalone($this->DBH);
        $assessStandalone->setQuestionData($questionSetId, $questionSetRowData);
        $assessStandalone->setState($scoreDto->getState());

        // Eval question code for non-scoring data.
        $assessStandalone->displayQuestion(0);

        // Eval question code again for scoring.
        $score = $assessStandalone->scoreQuestion(0, $scoreDto->getPartsToScore());

        $questionCode = [
            'questionType' => $questionSetRowData['qtype'],
            'questionControl' => $questionSetRowData['control'],
        ];
        $scoreResponse = $scoreDto->getScoreResponse($score, $questionCode, $assessStandalone->getState(), null);

        $returnData = [
            'correctAnswers' => $scoreResponse['correctAnswers'],
            'question' => $assessStandalone->getQuestion(),
        ];
        return $returnData;
    }

    /**
     * Validate against question data to determine editability
     *
     * @param Question $question A Question obect.
     * @param array $questionSetRow Associative array of imas_questionset row data
     *
     * @return array Validation messages explaining why a question cannot be edited (empty for editable questions).
     */
    private function validateIsEditable($question, $questionSetRow): array {
        $extraData = $question->getExtraData();
        $lumenlearningData = $extraData['lumenlearning'] ?? [];
        $questionComponents = $lumenlearningData['questionComponents'] ?? [];

        $questionTypeAndSettingsValidations = $this->validateQuestionTypeAndSettings($questionSetRow['qtype'], $questionComponents['componentsByQnIdentifier'] ?? []);
        $questionSetRowValidations = $this->validateQuestionSetRow($questionSetRow);
        $questionTextValidations = $this->validateQuestionText($questionComponents['text'] ?? '');
        $questionCodeValidations = $this->validateQuestionCode($questionSetRow['control'] ?? '');

        return array_unique(array_merge(
            $questionTypeAndSettingsValidations,
            $questionSetRowValidations,
            $questionTextValidations,
            $questionCodeValidations
        ));
    }

    private function validateQuestionCode($code): array {
        $validationErrors = [];
        $questionCodeParser = new QuestionCodeParserService($code);

        $functionCalls = $questionCodeParser->detectFunctionCalls();
        foreach ($functionCalls as $functionCall) {
            if (str_contains($functionCall['name'], 'includecodefrom')) {
                $validationErrors[] = 'Cannot edit a question that uses the `includecodefrom` function';
            }
        }

        return $validationErrors;
    }


    /**
     * Validate against question text HTML to determine editability
     *
     * @param array $htmlTags The set of HTML tags found in the question text
     *
     * @return array Validation messages explaining why a question cannot be edited.
     */
    private function validateQuestionText($qtext): array {
        $validationErrors = [];

        // validation for use of HTML in question text
        foreach (QuestionService::detectHtmlTags($qtext) as $htmlTag) {
            if (!in_array($htmlTag, $GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'])) {
                $validationErrors[] = "Cannot edit a question with a <$htmlTag/> HTML tag in the question text";
            }
        }

        // validation for placement of naswerbox in question text
        $answerbox = 'ANSWERBOX_PLACEHOLDER';
        $indexOfAnswerbox = strpos($qtext, $answerbox);

        // allow an arbitrary amount of spacing after the answerbox and allow a single closing HTML tag, so long as the end of the question text is reached (\z)
        $answerboxRegex = "/$answerbox(?:\s|&nbsp;|\\\\n|<br><\/br>|<br\/>|<br>)*(?:<\/div>|<\/p>|<\/span>)?(?:\s|&nbsp;|\\\\n|<br><\/br>|<br\/>|<br>)*\z/";
        preg_match_all($answerboxRegex, $qtext, $matches);

        // if the answerbox is in the question text but not at the end of it
        if ($indexOfAnswerbox !== false && count($matches[0]) == 0) {
            $validationErrors[] = "Cannot edit a question in which the answer box is not at the end of the question text";
        }

        return $validationErrors;
    }

    /**
     * Validate against imas_questionset data to determine editability
     *
     * @param array $questionSetRow Associative array of imas_questionset row data
     *
     * @return array Validation messages explaining why a question cannot be edited.
     */
    private function validateQuestionSetRow($questionSetRow): array {
        $validationErrors = [];

        if (1 == $questionSetRow['isrand']) {
            $validationErrors[] = "Cannot edit an algorithmic question";
        }

        return $validationErrors;
    }

    /**
     * Validate against question type data to determine editability
     *
     * @param string $qtype Question type
     * @param array<array> $questionSettings associative array of question settings from the evaluated question control
     *
     * @return array Validation messages explaining why a question cannot be edited.
     */
    private function validateQuestionTypeAndSettings($qtype, $questionSettings): array {
        $validationErrors = [];

        // validate that the question type is generally supported
        if (!in_array($qtype, $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'])) {
            $validationErrors[] = "Cannot edit a $qtype type question";
        } else {
            // validations specific to a question type & its settings
            switch ($qtype) {
                case 'choices':
                    $displayformat = $questionSettings['qn0']['displayformat'] ?? '';
                    if (
                        $displayformat == 'select' &&
                        !in_array('dropdown', $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'])
                    ) {
                        // a dropdown occurs when the 'displayformat' setting of a choices question is 'select'
                        // (i.e. dropdowns are a subtype of choices type questions)
                        $validationErrors[] = "Cannot edit a dropdown type question";
                    }
                default:
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Detects HTML tags in a given string and returns a unique set of tags found
     *
     * @param string $input The string to check for HTML tags
     * @return array An array containing unique HTML tags found
     */
    public static function detectHtmlTags($input): array
    {
        // Pattern matches opening tags, closing tags, and self-closing tags
        $pattern = '/<\/?([a-z][a-z0-9]*)\b[^>]*>/i';
        $matches = [];

        // Find all matches
        if (preg_match_all($pattern, $input, $matches)) {
            // Return unique tags by using array_unique
            return array_values(array_unique($matches[1]));
        }

        return [];
    }

    /*
     * Intended to be used to clean the question json (array type)
     * Current cleaning functions:
     *  - strips <script></script> HTML tags from string values
     */
    private function cleanQuestionComponents(array $json) : array {
        $strippedJson = [];
        if (!isset($json)) return $strippedJson;

        foreach ($json as $key => $value) {
            $newValue = null;
            if ($key == 'scripts') {
                $newValue = $value; # preserve scripts value
            } else if (is_array($value)) {
                // nested array
                $newValue = $this->cleanQuestionComponents($value);
            } else if (is_string($value)) {
                # Remove any embedded scripts for strings
                list($newValue, $scripts) = AssessStandalone::parseScripts($value);
            } else {
                $newValue = $value;
            }
            // indexed array
            if (is_int($key)) { $strippedJson[] = $newValue ; }
            // associative array
            else { $strippedJson[$key] = $newValue; }
        }
        return $strippedJson;
    }
}
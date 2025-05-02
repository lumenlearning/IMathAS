<?php

namespace Tests\unit\Services;

use App\Repositories\ohm\QuestionSetRepository;
use App\Services\ohm\QuestionService;
use IMathAS\assess2\questions\models\Question;
use IMathAS\assess2\questions\models\QuestionParams;
use Mockery;
use ReflectionClass;
use Tests\fixtures\Repositories\ohm\QuestionSetRepositoryFixtures;
use Tests\fixtures\Services\QuestionServiceFixtures;
use Tests\TestCase;

class QuestionServiceTest extends TestCase
{

    private QuestionService $questionService;
    private QuestionSetRepository $questionSetRepository;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->questionSetRepository = Mockery::mock(QuestionSetRepository::class);

        $this->questionService = new QuestionService(
            $this->questionSetRepository
        );

        $GLOBALS['myrights'] = 100; // admin user rights
        $GLOBALS['_SESSION'] = [
            'mathdisp' => null,
            'graphdisp' => null,
        ];
    }

    /*
     * generateAnswerDataByQnIdentifier
     */

    public function testGenerateAnswerDataByQnIdentifier(): void
    {
        // Get the private method under test.
        $class = new ReflectionClass(QuestionService::class);
        $generateAnswerDataByQnIdentifier = $class->getMethod('generateAnswerDataByQnIdentifier');

        // This will be passed to the method under test.
        $questionOriginalData = QuestionServiceFixtures::QUESTIONS_WITH_ANSWERS[0];
        $questionDataWithoutQnIdentifier = $questionOriginalData;
        unset($questionDataWithoutQnIdentifier['answerDataByQnIdentifier']);

        // Call the method under test.
        $answersByQnIdentifier = $generateAnswerDataByQnIdentifier->invokeArgs($this->questionService, [
            $questionDataWithoutQnIdentifier]);

        // Assertions.
        for ($i = 0; $i < 8; $i++) {
            $qn = 'qn' . str(1000 + $i);
            $this->assertEquals(
                $questionOriginalData['answerDataByQnIdentifier'][$qn]['questionType'],
                $answersByQnIdentifier[$qn]['questionType']);
            $this->assertEquals(
                $questionOriginalData['answerDataByQnIdentifier'][$qn]['correctAnswer'],
                $answersByQnIdentifier[$qn]['correctAnswer']);
            $this->assertEquals(
                $questionOriginalData['answerDataByQnIdentifier'][$qn]['showAnswerText'],
                $answersByQnIdentifier[$qn]['showAnswerText']);
        }

    }

    /*
     * generateQuestionParams
     */

    public function testGenerateQuestionParams(): void
    {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $generateQuestionParams = $class->getMethod('generateQuestionParams');

        // Call the method under test.
        /* @var QuestionParams $questionParams */
        $questionParams = $generateQuestionParams->invokeArgs($this->questionService, [
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES, 42
        ]);

        // Assertions.
        $this->assertEquals('5485', $questionParams->getDbQuestionSetId());
        $this->assertEquals('42', $questionParams->getQuestionSeed());

        $questionData = $questionParams->getQuestionData();
        $this->assertEquals(
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES['id'],
            5485);
        $this->assertEquals(
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES['control'],
            $questionData['control']);
        $this->assertEquals(
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES['qtext'],
            $questionData['qtext']);
    }

    /*
     * getQuestionAndAnswers
     */

    public function testGetQuestionAndAnswers(): void
    {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $getQuestionAndAnswers = $class->getMethod('getQuestionAndAnswers');

        // Call the method under test.
        $questionAndAnswers = $getQuestionAndAnswers->invokeArgs($this->questionService, [
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES['id'],
            42, // seed
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES
        ]);

        // Assertions.
        $correctAnswers = [
            300,
            "0",
            null,
            [
                "1",
                "2",
                "3"
            ],
            "0,2,4,5",
            1,
            9,
            "A,B,C,D,E,F"
        ];
        $this->assertnotempty($questionAndAnswers['correctAnswers']);
        $this->assertEquals($correctAnswers, $questionAndAnswers['correctAnswers']);
        $this->assertInstanceOf(Question::class, $questionAndAnswers['question']);
    }

    /*
     * getQuestionFromRowData
     */

    public function testGetQuestionFromRowData(): void
    {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $getQuestionFromRowData = $class->getMethod('getQuestionFromRowData');

        // Call the method under test.
        /* @var Question $question */
        $question = $getQuestionFromRowData->invokeArgs($this->questionService, [
            QuestionSetRepositoryFixtures::SINGLE_QUESTION_MULTIPART_ALL_TYPES,
            42
        ]);

        // Assertions.
        $this->assertInstanceOf(Question::class, $question);
        $this->assertStringContainsString('What is the distance between the numbers -200 and 100',
            $question->getQuestionContent());
    }

    /*
     * getQuestionSetRowFromResultSet
     */

    public function testGetQuestionSetRowFromResultSet(): void
    {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $getQuestionSetRowFromResultSet = $class->getMethod('getQuestionSetRowFromResultSet');

        // Call the method under test.
        $questionSetRow = $getQuestionSetRowFromResultSet->invokeArgs($this->questionService, [
            QuestionSetRepositoryFixtures::RESULTSET_WITH_TWO_QUESTIONS,
            3261,
        ]);

        // Assertions.
        $this->assertEquals(3261, $questionSetRow['id']);
        $this->assertEquals(
            QuestionSetRepositoryFixtures::RESULTSET_WITH_TWO_QUESTIONS[1]['control'],
            $questionSetRow['control']);
        $this->assertEquals(
            QuestionSetRepositoryFixtures::RESULTSET_WITH_TWO_QUESTIONS[1]['qtext'],
            $questionSetRow['qtext']);
    }

    /*
     * getQuestionsWithAnswers
     */

    public function testGetQuestionsWithAnswers(): void
    {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $getQuestionsWithAnswers = $class->getMethod('getQuestionsWithAnswers');

        // Mock DB calls.
        $this->questionSetRepository
            ->shouldReceive('getAllByQuestionId')
            ->withAnyArgs()
            ->andReturn(QuestionSetRepositoryFixtures::RESULTSET_WITH_TWO_QUESTIONS);

        // Call the method under test.
        $questionsWithAnswers = $getQuestionsWithAnswers->invokeArgs($this->questionService, [
            [
                [
                    "questionSetId" => 5485,
                    "seed" => 1234,
                ],
                [
                    "questionSetId" => 3261,
                    "seed" => 4321,
                ]
            ]
        ]);

        /*
         * Assertions -- first question (multi-part)
         */

        $this->assertEquals(5485, $questionsWithAnswers[0]['questionSetId']);
        $this->assertEquals(1234, $questionsWithAnswers[0]['seed']);
        $this->assertFalse($questionsWithAnswers[0]['isAlgorithmic']);
        $this->assertFalse($questionsWithAnswers[0]['isEditable']);
        $this->assertNotEmpty($questionsWithAnswers[0]['editableValidations']);
        $this->assertNotEmpty($questionsWithAnswers[0]['json']);
        $this->assertNotEmpty($questionsWithAnswers[0]['html']);
        $this->assertEquals([
            300,
            "0",
            null,
            [
                "1",
                "2",
                "3"
            ],
            "0,2,4,5",
            1,
            9,
            "A,B,C,D,E,F"
        ], $questionsWithAnswers[0]['correctAnswers']);

        // Answers by qn identifier.
        $this->assertEquals(300,
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1000']['correctAnswer']);
        $this->assertEquals('0',
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1001']['correctAnswer']);
        $this->assertEquals(null,
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1002']['correctAnswer']);
        $this->assertEquals(['1', '2', '3'],
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1003']['correctAnswer']);
        $this->assertEquals('0,2,4,5',
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1004']['correctAnswer']);
        $this->assertEquals(1,
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1005']['correctAnswer']);
        $this->assertEquals(9,
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1006']['correctAnswer']);
        $this->assertEquals('A,B,C,D,E,F',
            $questionsWithAnswers[0]['answerDataByQnIdentifier']['qn1007']['correctAnswer']);

        /*
         * Assertions -- second question (single part)
         */

        $this->assertEquals(3261, $questionsWithAnswers[1]['questionSetId']);
        $this->assertEquals(4321, $questionsWithAnswers[1]['seed']);
        $this->assertFalse($questionsWithAnswers[1]['isAlgorithmic']);
        $this->assertFalse($questionsWithAnswers[1]['isEditable']);
        $this->assertNotEmpty($questionsWithAnswers[1]['editableValidations']);
        $this->assertNotEmpty($questionsWithAnswers[1]['json']);
        $this->assertNotEmpty($questionsWithAnswers[1]['html']);

        // Answers by qn identifier.
        $this->assertEquals([11], $questionsWithAnswers[1]['correctAnswers']);
        $this->assertEquals([11],
            $questionsWithAnswers[1]['answerDataByQnIdentifier']['qn0']['correctAnswer']);
    }

    /*
     * validateIsEditable
     */
    public function testValidateIsEditable_aggregatesValidationsFromHelperMethods(): void {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'] = [];
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = ['choices'];

        // fill with garbage values
        $question = new Question(
            'questionContent',
            ['jsparams'],
            ['answerPartWeights'],
            'solutionContent',
            'solutionContentDetailed',
            ['correctAnswersForParts'],
            ['externalReferences']
        );
        $question->setExtraData([
            'lumenlearning' => [
                'questionComponents' => [
                    'text' => '<p>What is the answer?</p>ANSWERBOX_PLACEHOLDER',
                    'components' => [[
                        'displayformat' => 'select'
                    ]]
                ]
            ]
        ]);
        $questionSetRow = [
            'isrand' => 1,
            'qtype' => 'choices'
        ];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateIsEditable = $class->getMethod('validateIsEditable');

        $validationErrors = $validateIsEditable->invokeArgs($this->questionService, [$question, $questionSetRow]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(3, count($validationErrors));

        // validateQuestionText
        $this->assertContains('Cannot edit a question with a <p/> HTML tag in the question text', $validationErrors);
        // validateQuestionTypeAndSettings
        $this->assertContains('Cannot edit a dropdown type question', $validationErrors);
        // validateQuestionSetRow
        $this->assertContains('Cannot edit an algorithmic question', $validationErrors);
    }


    /*
     * detectHtmlTags
     */
    public function testDetectHtmlTags(): void
    {
        $evaledqtextwithoutanswerbox = '<div class="container">This is <b>bold</b> text with an <img src="image.jpg" /> and a <br> line break.</div><p>What is the answer?</p>ANSWERBOX_PLACEHOLDER';

        $expectedTags = ['div', 'b', 'img', 'br', 'p'];
        $this->assertEquals($expectedTags, QuestionService::detectHtmlTags($evaledqtextwithoutanswerbox));
    }

    /*
     * validateQuestionText
     */

    public function testValidateQuestionText_allowsEditableTagsAndAnswerbox(): void
    {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'] = ['p'];
        $evaledqtextwithoutanswerbox = '<p>What is the answer?</p>ANSWERBOX_PLACEHOLDER';

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionText = $class->getMethod('validateQuestionText');

        $validationErrors = $validateQuestionText->invokeArgs($this->questionService, [$evaledqtextwithoutanswerbox]);

        $this->assertEmpty($validationErrors);
    }

    public function testValidateQuestionText_disallowsNotEditableTags(): void
    {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTEXT_HTML_TAGS'] = [];
        $evaledqtextwithoutanswerbox = '<p>What is the answer?</p>';

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionText = $class->getMethod('validateQuestionText');

        $validationErrors = $validateQuestionText->invokeArgs($this->questionService, [$evaledqtextwithoutanswerbox]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(1, count($validationErrors));
        $this->assertEquals('Cannot edit a question with a <p/> HTML tag in the question text', $validationErrors[0]);
    }

    public function testValidateQuestionText_disallowsAnswerboxNotAtEndOfText(): void
    {
        $evaledqtextwithoutanswerbox = 'ANSWERBOX_PLACEHOLDER&nbsp;What is the answer?';

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionText = $class->getMethod('validateQuestionText');

        $validationErrors = $validateQuestionText->invokeArgs($this->questionService, [$evaledqtextwithoutanswerbox]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(1, count($validationErrors));
        $this->assertEquals('Cannot edit a question in which the answer box is not at the end of the question text', $validationErrors[0]);
    }

    /*
     * validateQuestionTypeAndSettings
     */
    public function testValidateQuestionTypeAndSettings_allowsEditableQtypes(): void
    {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = ['choices'];

        $qtype = 'choices';
        $questionSettings = [[]];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionTypeAndSettings = $class->getMethod('validateQuestionTypeAndSettings');

        $validationErrors = $validateQuestionTypeAndSettings->invokeArgs($this->questionService, [$qtype, $questionSettings]);

        $this->assertEmpty($validationErrors);
    }

    public function testValidateQuestionTypeAndSettings_disallowsNonEditableQtypes(): void {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = [];

        $qtype = 'choices';
        $questionSettings = [[]];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionTypeAndSettings = $class->getMethod('validateQuestionTypeAndSettings');

        $validationErrors = $validateQuestionTypeAndSettings->invokeArgs($this->questionService, [$qtype, $questionSettings]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(1, count($validationErrors));
        $this->assertEquals('Cannot edit a choices type question', $validationErrors[0]);
    }

    public function testValidateQuestionTypeAndSettings_disallowsDropdownStyleChoicesQtypes(): void {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = ['choices'];

        $qtype = 'choices';
        $questionSettings = [['displayformat' => 'select']];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionTypeAndSettings = $class->getMethod('validateQuestionTypeAndSettings');

        $validationErrors = $validateQuestionTypeAndSettings->invokeArgs($this->questionService, [$qtype, $questionSettings]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(1, count($validationErrors));
        $this->assertEquals('Cannot edit a dropdown type question', $validationErrors[0]);
    }

    public function testValidateQuestionTypeAndSettings_allowsDropdownStyleChoicesQtypesWhenEditable(): void {
        $GLOBALS['QUESTIONS_API']['EDITABLE_QTYPES'] = ['choices', 'dropdown'];

        $qtype = 'choices';
        $questionSettings = [['displayformat' => 'select']];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionTypeAndSettings = $class->getMethod('validateQuestionTypeAndSettings');

        $validationErrors = $validateQuestionTypeAndSettings->invokeArgs($this->questionService, [$qtype, $questionSettings]);

        $this->assertEmpty($validationErrors);
    }

    /*
     * validateQuestionSetRow
     */
    public function testValidateQuestionSetRow_allowsNotRand(): void
    {
        $questionSetRow = ['isrand' => 0];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionSetRow = $class->getMethod('validateQuestionSetRow');

        $validationErrors = $validateQuestionSetRow->invokeArgs($this->questionService, [$questionSetRow]);

        $this->assertEmpty($validationErrors);
    }

    public function testValidateQuestionSetRow_disallowsIsRand(): void
    {
        $questionSetRow = ['isrand' => 1];

        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $validateQuestionSetRow = $class->getMethod('validateQuestionSetRow');

        $validationErrors = $validateQuestionSetRow->invokeArgs($this->questionService, [$questionSetRow]);

        $this->assertNotEmpty($validationErrors);
        $this->assertEquals(1, count($validationErrors));
        $this->assertEquals('Cannot edit an algorithmic question', $validationErrors[0]);
    }

    /*
     * cleanQuestionJson
     */
    public function testCleanQuestionJson_retainsGoodData() {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $cleanQuestionJson = $class->getMethod('cleanQuestionJson');

        $inputjson = [
            # Key/Value pair
            'text' => 'abc123',
            # 'Hash' Value
            'associative_array' => ['key' => 'value'],
            # Array Value
            'indexed_array' => [1, 2, 3],
            'nested_arrays' => [
                # Array of 'Hashes' with Array values
                ['key' => ['value', 'value']],
                ['key' => ['value', 'value']]
            ]
        ];

        // Call the method under test.
        $outputjson = $cleanQuestionJson->invokeArgs($this->questionService, [$inputjson]);

        # PHP Unit gracefully compares arrays with nested data
        $this->assertEquals($inputjson, $outputjson);
    }

    public function testCleanQuestionJson_cleansScriptTags() {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $cleanQuestionJson = $class->getMethod('cleanQuestionJson');

        $expectedtextvalue = 'abc123';
        $inputjson = [
            # Key/Value pair
            'text' => "$expectedtextvalue<script>a bunch of top secret script stuff</script>",
            # 'Hash' Value
            'associative_array' => ['key' => 'value'],
            # Array Value
            'indexed_array' => [1, 2, 3],
            'nested_arrays' => [
                # Array of 'Hashes' with Array values
                ['key' => ['value', '<script>a bunch of top secret script stuff</script>value']],
                ['key' => ['value', 'value']]
            ],
            'scripts' => [
                '<script>I will survive!</script>'
            ]
        ];

        // Call the method under test.
        $outputjson = $cleanQuestionJson->invokeArgs($this->questionService, [$inputjson]);

        # Ensure that script tags are removed from all values in the array
        $this->assertEquals($expectedtextvalue, $outputjson['text']);
        $this->assertEquals('value', $outputjson['nested_arrays'][0]['key'][1]);
        # Ensure that script tags are not removed if the key is scripts
        $this->assertEquals($inputjson['scripts'], $outputjson['scripts']);
    }
}
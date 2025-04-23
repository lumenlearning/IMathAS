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
        $this->assertEmpty($questionsWithAnswers[0]['json']); # empty when not available on questions
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
        $this->assertEmpty($questionsWithAnswers[1]['json']); # empty when not available on questions
        $this->assertNotEmpty($questionsWithAnswers[1]['html']);

        // Answers by qn identifier.
        $this->assertEquals([11], $questionsWithAnswers[1]['correctAnswers']);
        $this->assertEquals([11],
            $questionsWithAnswers[1]['answerDataByQnIdentifier']['qn0']['correctAnswer']);
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
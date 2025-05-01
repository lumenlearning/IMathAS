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

        // Question components
        $firstQuestionVars = $questionsWithAnswers[0]['questionComponents'];
        $this->assertEquals('multipart', $firstQuestionVars['type']);
        $this->assertEquals('<p>What is the distance between the numbers -200 and 100?</p>
ANSWERBOX_PLACEHOLDER_QN_1000<br/><br/>

<p>What is 10 * 10?</p>
ANSWERBOX_PLACEHOLDER_QN_1001<br/><br/>
<p>How are you feeling today?</p>
ANSWERBOX_PLACEHOLDER_QN_1002<br/><br/>
<p>What is the correct order of these steps?</p>
ANSWERBOX_PLACEHOLDER_QN_1003<br/><br/>
<p>Select the questions with valid statistics.</p>
ANSWERBOX_PLACEHOLDER_QN_1004<br/><br/>
<p>What is 10 / 10?</p>
ANSWERBOX_PLACEHOLDER_QN_1005<br/><br/>
<p>If `10x = 90`, solve for `x`.</p>
ANSWERBOX_PLACEHOLDER_QN_1006<br/><br/>
<p>List all the letters from A to F as a comma-separated list.</p>
ANSWERBOX_PLACEHOLDER_QN_1007', $firstQuestionVars['text']);
        // Question components - Part 1, calculated
        $firstQuestionVarsByQn1000 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1000'];
        $this->assertEquals('calculated', $firstQuestionVarsByQn1000['partType']);
        $this->assertEquals(300, $firstQuestionVarsByQn1000['answer']);
        // Question components - Part 2, choices
        $firstQuestionVarsByQn1001 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1001'];
        $this->assertEquals('choices', $firstQuestionVarsByQn1001['partType']);
        $this->assertEquals(0, $firstQuestionVarsByQn1001['answer']);
        $this->assertEquals([
            '100',
            'Infinity',
            '0',
            'I was told there would be no math',
        ], $firstQuestionVarsByQn1001['choices']);
        $this->assertEquals([0, 1, 2, 3], $firstQuestionVarsByQn1001['shuffledChoicesIndex']);
        $this->assertEquals('all', $firstQuestionVarsByQn1001['noshuffle']);
        // Question components - Part 3, essay
        $firstQuestionVarsByQn1002 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1002'];
        $this->assertEquals('essay', $firstQuestionVarsByQn1002['partType']);
        $this->assertEquals('', $firstQuestionVarsByQn1002['answer']);
        // Question components - Part 4, matching
        $firstQuestionVarsByQn1003 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1003'];
        $this->assertEquals('matching', $firstQuestionVarsByQn1003['partType']);
        $this->assertEquals([0,1,2], $firstQuestionVarsByQn1003['shuffledAnswerChoicesIndex']);
        $this->assertEquals([0,1,2], $firstQuestionVarsByQn1003['shuffledQuestionChoicesIndex']);
        $this->assertEquals('all', $firstQuestionVarsByQn1003['noshuffle']);
        // Question components - Part 5, multans
        $firstQuestionVarsByQn1004 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1004'];
        $this->assertEquals('multans', $firstQuestionVarsByQn1004['partType']);
        $this->assertEquals('0,2,4,5', $firstQuestionVarsByQn1004['answers']);
        $this->assertEquals([0,1,2,3,4,5], $firstQuestionVarsByQn1004['shuffledChoicesIndex']);
        $this->assertEquals([
            "60% of product folks are under-caffeinated",
            "Statistics is silly",
            "10% of people do not like candy",
            "I was told there would be no math",
            "95% of campers are happy",
            "4 out of 5 sloths say disco is their favorite music genre",
        ], $firstQuestionVarsByQn1004['choices']);
        $this->assertEquals('all', $firstQuestionVarsByQn1004['noshuffle']);
        // Question components - Part 6, number
        $firstQuestionVarsByQn1005 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1005'];
        $this->assertEquals('number', $firstQuestionVarsByQn1005['partType']);
        $this->assertEquals('1', $firstQuestionVarsByQn1005['answer']);
        // Question components - Part 7, numfunc
        $firstQuestionVarsByQn1006 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1006'];
        $this->assertEquals('numfunc', $firstQuestionVarsByQn1006['partType']);
        $this->assertEquals('9', $firstQuestionVarsByQn1006['answer']);
        $this->assertEquals(['x'], $firstQuestionVarsByQn1006['variables']);
        // Question components - Part 8, string
        $firstQuestionVarsByQn1007 = $questionsWithAnswers[0]['questionComponents']['componentsByQnIdentifier']['qn1007'];
        $this->assertEquals('string', $firstQuestionVarsByQn1007['partType']);
        $this->assertEquals('A,B,C,D,E,F', $firstQuestionVarsByQn1007['answer']);

        /*
         * Assertions -- second question (single part)
         */

        $this->assertEquals(3261, $questionsWithAnswers[1]['questionSetId']);
        $this->assertEquals(4321, $questionsWithAnswers[1]['seed']);
        $this->assertFalse($questionsWithAnswers[1]['isAlgorithmic']);
        $this->assertNotEmpty($questionsWithAnswers[1]['json']);
        $this->assertNotEmpty($questionsWithAnswers[1]['html']);

        // Answers by qn identifier.
        $this->assertEquals([11], $questionsWithAnswers[1]['correctAnswers']);
        $this->assertEquals([11],
            $questionsWithAnswers[1]['answerDataByQnIdentifier']['qn0']['correctAnswer']);

        // Question components
        $secondQuestionVars = $questionsWithAnswers[1]['questionComponents'];
        $this->assertEquals('number', $secondQuestionVars['type']);
        $this->assertEquals('<p>What is 1 + 1? ANSWERBOX_PLACEHOLDER</p>', $secondQuestionVars['text']);
        $secondQuestionVarsByQn0 = $questionsWithAnswers[1]['questionComponents']['componentsByQnIdentifier']['qn0'];
        $this->assertEquals('number', $secondQuestionVarsByQn0['partType']);
        $this->assertEquals(11, $secondQuestionVarsByQn0['answer']);
    }

    /*
     * cleanQuestionJson
     */

    public function testCleanQuestionComponents_retainsGoodData() {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $cleanQuestionJson = $class->getMethod('cleanQuestionComponents');

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

    public function testCleanQuestionComponents_cleansScriptTags() {
        // Get the method under test.
        $class = new ReflectionClass(QuestionService::class);
        $cleanQuestionJson = $class->getMethod('cleanQuestionComponents');

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
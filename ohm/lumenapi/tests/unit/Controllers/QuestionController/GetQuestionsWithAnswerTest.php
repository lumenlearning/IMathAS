<?php

namespace Tests\unit\Controllers\QuestionController;

use App\Http\Controllers\QuestionController;
use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use App\Services\Interfaces\QuestionServiceInterface;
use App\Services\ohm\QuestionService;
use Illuminate\Http\Request;
use Mockery;
use PDO;
use Tests\fixtures\Services\QuestionServiceFixtures;
use Tests\TestCase;

class GetQuestionsWithAnswerTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface $assessmentRepository;
    private QuestionSetRepositoryInterface $questionSetRepository;
    private QuestionServiceInterface $questionService;

    private PDO $pdo;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->assessmentRepository = Mockery::mock(AssessmentRepository::class);
        $this->questionSetRepository = Mockery::mock(QuestionSetRepository::class);
        $this->questionService = Mockery::mock(QuestionService::class);
        $this->questionController = new QuestionController($this->assessmentRepository,
            $this->questionSetRepository, $this->questionService);

        $this->pdo = Mockery::mock(PDO::class);
        $this->questionController->setPdo($this->pdo);
    }


    /*
     * getQuestionsWithAnswers
     */

    public function testGetQuestionsWithAnswers(): void
    {
        $this->questionService
            ->shouldReceive('getQuestionsWithAnswers')
            ->withAnyArgs()
            ->andReturn(QuestionServiceFixtures::QUESTIONS_WITH_ANSWERS);

        $request = Request::create('/api/v1/questions', 'POST',
            json_decode('{
                "questions": [
                    {
                        "questionSetId": 5485,
                        "seed": 4321
                    },
                    {
                        "questionSetId": 3261,
                        "seed": 1234
                    }
                ]
            }', true)
        );

        $response = $this->questionController->getQuestionsWithAnswers($request);
        $responseData = $response->getData(true);

        /*
         * Assertions -- first question (multi-part)
         */

        $this->assertEquals(5485, $responseData[0]['questionSetId']);
        $this->assertEquals(1234, $responseData[0]['seed']);
        $this->assertEquals('1gljk2clg9u', $responseData[0]['ohmUniqueId']);
        $this->assertEquals('multipart', $responseData[0]['questionType']);
        $this->assertNotEmpty($responseData[0]['html']);
        $this->assertCount(8, $responseData[0]['jsParams']);
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
        ], $responseData[0]['correctAnswers']);
        $this->assertCount(8, $responseData[0]['showAnswerText']);
        $this->assertEquals('1712613994709310', $responseData[0]['uniqueid']);
        $this->assertCount(0, $responseData[0]['errors']);
        $this->assertEquals([
            'calculated',
            'choices',
            'essay',
            'matching',
            'multans',
            'number',
            'numfunc',
            'string',
        ], $responseData[0]['partTypes']);

        // Feedback

        $this->assertEquals('correct', $responseData[0]['feedback']['qn1001-0']['correctness']);
        $this->assertEquals('This is correct. Way to go.', $responseData[0]['feedback']['qn1001-0']['feedback']);

        $this->assertEquals('incorrect', $responseData[0]['feedback']['qn1001-1']['correctness']);
        $this->assertEquals('Incorrect', $responseData[0]['feedback']['qn1001-1']['feedback']);

        $this->assertEquals('incorrect', $responseData[0]['feedback']['qn1001-2']['correctness']);
        $this->assertEquals('Incorrect.', $responseData[0]['feedback']['qn1001-2']['feedback']);

        $this->assertEquals('incorrect', $responseData[0]['feedback']['qn1001-3']['correctness']);
        $this->assertEquals('Incorrect', $responseData[0]['feedback']['qn1001-3']['feedback']);


        // Answers by qn identifier.

        $this->assertEquals('calculated',
            $responseData[0]['answerDataByQnIdentifier']['qn1000']['questionType']);
        $this->assertEquals(300,
            $responseData[0]['answerDataByQnIdentifier']['qn1000']['correctAnswer']);
        $this->assertEquals('300',
            $responseData[0]['answerDataByQnIdentifier']['qn1000']['showAnswerText']);

        $this->assertEquals('choices',
            $responseData[0]['answerDataByQnIdentifier']['qn1001']['questionType']);
        $this->assertEquals('0',
            $responseData[0]['answerDataByQnIdentifier']['qn1001']['correctAnswer']);
        $this->assertEquals('100',
            $responseData[0]['answerDataByQnIdentifier']['qn1001']['showAnswerText']);

        $this->assertEquals('essay',
            $responseData[0]['answerDataByQnIdentifier']['qn1002']['questionType']);
        $this->assertEquals(null,
            $responseData[0]['answerDataByQnIdentifier']['qn1002']['correctAnswer']);
        $this->assertEquals('',
            $responseData[0]['answerDataByQnIdentifier']['qn1002']['showAnswerText']);

        $this->assertEquals('matching',
            $responseData[0]['answerDataByQnIdentifier']['qn1003']['questionType']);
        $this->assertEquals(['1', '2', '3'],
            $responseData[0]['answerDataByQnIdentifier']['qn1003']['correctAnswer']);
        $this->assertEquals('<br/>1<br/>2<br/>3',
            $responseData[0]['answerDataByQnIdentifier']['qn1003']['showAnswerText']);

        $this->assertEquals('multans',
            $responseData[0]['answerDataByQnIdentifier']['qn1004']['questionType']);
        $this->assertEquals('0,2,4,5',
            $responseData[0]['answerDataByQnIdentifier']['qn1004']['correctAnswer']);
        $this->assertEquals('<br/>60% of product folks are under-caffeinated<br/>10% of people do not like candy<br/>95% of campers are happy<br/>4 out of 5 sloths say disco is their favorite music genre',
            $responseData[0]['answerDataByQnIdentifier']['qn1004']['showAnswerText']);

        $this->assertEquals('number',
            $responseData[0]['answerDataByQnIdentifier']['qn1005']['questionType']);
        $this->assertEquals(1,
            $responseData[0]['answerDataByQnIdentifier']['qn1005']['correctAnswer']);
        $this->assertEquals('1',
            $responseData[0]['answerDataByQnIdentifier']['qn1005']['showAnswerText']);

        $this->assertEquals('numfunc',
            $responseData[0]['answerDataByQnIdentifier']['qn1006']['questionType']);
        $this->assertEquals(9,
            $responseData[0]['answerDataByQnIdentifier']['qn1006']['correctAnswer']);
        $this->assertEquals('`9`',
            $responseData[0]['answerDataByQnIdentifier']['qn1006']['showAnswerText']);

        $this->assertEquals('string',
            $responseData[0]['answerDataByQnIdentifier']['qn1007']['questionType']);
        $this->assertEquals('A,B,C,D,E,F',
            $responseData[0]['answerDataByQnIdentifier']['qn1007']['correctAnswer']);
        $this->assertEquals('A,B,C,D,E,F',
            $responseData[0]['answerDataByQnIdentifier']['qn1007']['showAnswerText']);

        /*
         * Assertions -- second question (single part)
         */

        $this->assertEquals(3261, $responseData[1]['questionSetId']);
        $this->assertEquals(4321, $responseData[1]['seed']);
        $this->assertEquals('1gljntos656', $responseData[1]['ohmUniqueId']);
        $this->assertEquals('number', $responseData[1]['questionType']);
        $this->assertNotEmpty($responseData[1]['html']);
        $this->assertCount(1, $responseData[1]['jsParams']);
        $this->assertEquals([11], $responseData[1]['correctAnswers']);
        $this->assertCount(1, $responseData[1]['showAnswerText']);
        $this->assertEquals('1712618134706342', $responseData[1]['uniqueid']);
        $this->assertCount(0, $responseData[0]['errors']);
        $this->assertArrayNotHasKey('partTypes', $responseData[1]);

        // Answers by qn identifier.

        $this->assertEquals('number',
            $responseData[1]['answerDataByQnIdentifier']['qn0']['questionType']);
        $this->assertEquals([11],
            $responseData[1]['answerDataByQnIdentifier']['qn0']['correctAnswer']);
        $this->assertEquals(['11'],
            $responseData[1]['answerDataByQnIdentifier']['qn0']['showAnswerText']);
    }
}
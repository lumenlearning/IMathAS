<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\QuestionImportController;
use App\Services\ohm\QuestionImportService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;


class QuestionImportControllerTest extends TestCase
{

    const MGA_QUESTION_NO_FEEDBACK = [
        "source_id" => "3d056d98-e0b2-4939-af4c-fe5396bdae98",
        "source_type" => "mga_file",
        "type" => "multiple_choice",
        "is_summative" => false,
        "description" => "Anthony wants to make sure he has a good credit score.  Which of the following actions will have the largest impact on his score?",
        "text" => "Anthony wants to make sure he has a good credit score.  Which of the following actions will have the largest impact on his score?",
        "choices" => [
            "Making on time payments",
            "Waiting as long as possible to get a credit card",
            "Applying for premier credit cards",
            "Establishing a few different types of credit between loans and credit cards"
        ],
        "correct_answer" => 0,
        "feedback" => null,
    ];

    const MGA_QUESTION_WITH_FEEDBACK = [
        "source_id" => "b7568530-f70b-4810-bf5b-af608951da38",
        "source_type" => "mga_file",
        "type" => "multiple_choice",
        "is_summative" => false,
        "description" => "Maya is concerned about her credit problems and is worried about the debt she’s accumulated throughout college. Which of the following strategies is the most advisable for Maya?",
        "text" => "Maya is concerned about her credit problems and is worried about the debt she’s accumulated throughout college. Which of the following strategies is the most advisable for Maya?",
        "choices" => [
            "Defer payments to a later date",
            "Look for a reputable credit counselor",
            "Immediately consolidate her loans",
            "Automatically apply for bankruptcy"
        ],
        "correct_answer" => 1,
        "feedback" => [
            "type" => "per_answer",
            "feedbacks" => [
                "Incorrect. While some student loans allow deferment, Maya must have an accepted reason to defer payments. Credit card debt is much harder and costs more to defer.",
                "Correct! ('singleQuoteTest') (\"doubleQuoteTest\") (\$sanitizeTest) (<htmlTest>) (&amp; test) Credit counselors offer debt management plans in which they work with credit card and loan companies to arrange a deal and ask you for monthly deposits so that they can help you pay off your debts.",
                "Incorrect. While this may give her more time to pay off her debt, consolidating to one payment can cost more and accrue a higher interest rate.",
                "Incorrect. Bankruptcy is an official status obtained through court procedures meaning that you are unable to pay off your debts. Bankruptcy damages your credit score, and the fees for filing paperwork and hiring an attorney are costly, so it should be used only as a last resort."
            ]
        ]
    ];

    const MGA_QUESTIONS = [
        self::MGA_QUESTION_NO_FEEDBACK,
        self::MGA_QUESTION_WITH_FEEDBACK
    ];

    private QuestionImportController $questionImportController;
    private QuestionImportService $questionImportService;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->questionImportService = Mockery::mock(QuestionImportService::class);
        $this->questionImportController = new QuestionImportController($this->questionImportService);
    }

    /*
     * importMgaQuestions
     */

    public function testImportMgaQuestions(): void
    {
        $this->questionImportService
            ->shouldReceive('createMultipleQuestions')
            ->andReturn(
                [
                    [
                        "source_id" => "3d056d98-e0b2-4939-af4c-fe5396bdae98",
                        "status" => "created",
                        "questionset_id" => 5439,
                        "errors" => []
                    ],
                    [
                        "source_id" => "b7568530-f70b-4810-bf5b-af608951da38",
                        "status" => "created",
                        "questionset_id" => 5440,
                        "errors" => []
                    ]
                ]
            );

        $requestBody = [
            'owner_id' => 42,
            'questions' => self::MGA_QUESTIONS,
        ];

        $request = Request::create('/api/dev/v1/questions/mga_imports', 'POST', $requestBody);
        $jsonResponse = $this->questionImportController->importMgaQuestions($request);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(201, $jsonResponse->getStatusCode());

        $questionMapping = $jsonData->question_mappings;
        // First created question.
        $this->assertEquals('3d056d98-e0b2-4939-af4c-fe5396bdae98', $questionMapping[0]->source_id);
        $this->assertEquals('created', $questionMapping[0]->status);
        $this->assertEquals(5439, $questionMapping[0]->questionset_id);
        $this->assertEquals([], $questionMapping[0]->errors);
        // Second created question.
        $this->assertEquals('b7568530-f70b-4810-bf5b-af608951da38', $questionMapping[1]->source_id);
        $this->assertEquals('created', $questionMapping[1]->status);
        $this->assertEquals(5440, $questionMapping[1]->questionset_id);
        $this->assertEquals([], $questionMapping[1]->errors);
    }
}

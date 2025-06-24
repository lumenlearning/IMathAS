<?php

namespace Tests\Unit\Controllers;

use App\Exceptions\InvalidQuestionImportType;
use App\Repositories\ohm\LibraryItemRepository;
use App\Repositories\ohm\QuestionSetRepository;
use App\Repositories\ohm\UserRepository;
use App\Services\ohm\QuestionImportService;
use Mockery;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class QuestionImportServiceTest extends TestCase
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
            "Defer payments to a later date -- it's all good!",
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

    const FORM_QUESTION_NO_FEEDBACK = [
        "source_id" => "9d779655-019a-472a-a28f-1ef06bb35aad",
        "source_type" => "form_input",
        "type" => "multiple_choice",
        "description" => "What is 1 + 2?",
        "text" => "What is 1 + 2?",
        "choices" => [
            "1",
            "2",
            "3"
        ],
        "correct_answer" => 2,
    ];

    const MGA_QUESTION_NEEDS_SANITIZATION = [
        "source_id" => "9d779655-019a-472a-a28f-1ef06bb35aad",
        "source_type" => "form_input",
        "type" => "multiple_choice",
        "description" => "What is 1 + 2?<script>let a = 1;</script>",
        "text" => "What is 1 + 2?<script>let a = 1;</script>",
        "choices" => [
            "1<button>CLICK ME!</button>",
            "<input/>2",
            "3<code>let a = 1</code>"
        ],
        "correct_answer" => 2
    ];

    const QUESTIONS = [
        self::MGA_QUESTION_NO_FEEDBACK,
        self::MGA_QUESTION_WITH_FEEDBACK,
        self::FORM_QUESTION_NO_FEEDBACK
    ];

    const USER = [
        'id' => 1,
        'FirstName' => 'FirstnameHere',
        'LastName' => 'LastnameHere',
    ];

    private QuestionImportService $questionImportService;
    private QuestionSetRepository $questionSetRepository;
    private LibraryItemRepository $libraryItemRepository;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->questionSetRepository = Mockery::mock(QuestionSetRepository::class);
        $this->libraryItemRepository = Mockery::mock(LibraryItemRepository::class);
        $this->userRepository = Mockery::mock(UserRepository::class);

        $this->questionImportService = new QuestionImportService(
            $this->questionSetRepository,
            $this->libraryItemRepository,
            $this->userRepository
        );
    }

    /*
     * createMultipleQuestions
     */

    public function testCreateMultipleQuestions(): void
    {
        $this->userRepository
            ->shouldReceive('findById')
            ->andReturn(self::USER);
        $this->questionSetRepository
            ->shouldReceive('create')
            ->andReturn(42, 43, 44);
        $this->libraryItemRepository
            ->shouldReceive('create')
            ->andReturn(21, 22, 23);

        $questionIds = $this->questionImportService->createMultipleQuestions(
            'quiz', self::QUESTIONS, self::USER['id']);

        $this->assertEquals(self::QUESTIONS[0]['source_id'], $questionIds[0]['source_id']);
        $this->assertEquals('created', $questionIds[0]['status']);
        $this->assertEquals(42, $questionIds[0]['questionset_id']);
        $this->assertEquals([], $questionIds[0]['errors']);

        $this->assertEquals(self::QUESTIONS[1]['source_id'], $questionIds[1]['source_id']);
        $this->assertEquals('created', $questionIds[1]['status']);
        $this->assertEquals(43, $questionIds[1]['questionset_id']);
        $this->assertEquals([], $questionIds[1]['errors']);

        $this->assertEquals(self::QUESTIONS[2]['source_id'], $questionIds[2]['source_id']);
        $this->assertEquals('created', $questionIds[2]['status']);
        $this->assertEquals(44, $questionIds[2]['questionset_id']);
        $this->assertEquals([], $questionIds[2]['errors']);
    }

    public function testCreateMultipleQuestions_InvalidImportMode(): void
    {
        $this->expectException(InvalidQuestionImportType::class);

        $this->questionImportService->createMultipleQuestions(
            'meow', [], 42);
    }

    public function testCreateMultipleQuestions_UserIdNotFound(): void
    {
        $this->expectException(RuntimeException::class);

        $this->userRepository
            ->shouldReceive('findById')
            ->andReturn(null);

        $this->questionImportService->createMultipleQuestions(
            'practice', self::QUESTIONS, self::USER['id']);
    }

    /*
     * createSingleQuestion
     */

    public function testCreateSingleMgaQuestion(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $createSingleQuestion = $class->getMethod('createSingleQuestion');
        $createSingleQuestion->setAccessible(true); // Required for PHP 7.4.

        $this->questionSetRepository
            ->shouldReceive('create')
            ->andReturn(42);
        $this->libraryItemRepository
            ->shouldReceive('create')
            ->andReturn(21);

        $questionsetId = $createSingleQuestion->invokeArgs($this->questionImportService, [
            'quiz', self::MGA_QUESTION_WITH_FEEDBACK, self::USER
        ]);

        $this->assertEquals(42, $questionsetId);
    }

    public function testCreateSingleFormQuestion(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $createSingleQuestion = $class->getMethod('createSingleQuestion');
        $createSingleQuestion->setAccessible(true); // Required for PHP 7.4.

        $this->questionSetRepository
            ->shouldReceive('create')
            ->andReturn(44);
        $this->libraryItemRepository
            ->shouldReceive('create')
            ->andReturn(23);

        $questionsetId = $createSingleQuestion->invokeArgs($this->questionImportService, [
            'quiz', self::FORM_QUESTION_NO_FEEDBACK, self::USER
        ]);

        $this->assertEquals(44, $questionsetId);
    }

    public function testCreateSingleQuestion_UnknownType(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $createSingleQuestion = $class->getMethod('createSingleQuestion');
        $createSingleQuestion->setAccessible(true); // Required for PHP 7.4.

        $this->expectException(RuntimeException::class);
        $createSingleQuestion->invokeArgs($this->questionImportService, [
            'quiz', ['type' => 'meow'], []
        ]);
    }

    /*
     * buildMultipleChoiceQuestion
     */

    public function testBuildMultipleChoiceQuestion_WithPerAnswerFeedback(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $sanitizeInputText = $class->getMethod('sanitizeInputText');
        $sanitizeInputText->setAccessible(true); // Required for PHP 7.4.
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4

        // Method under test.
        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['quiz', self::MGA_QUESTION_WITH_FEEDBACK]);

        /*
         * Assertions
         */

        // Question type
        $this->assertEquals('choices', $question['qtype']);

        // Question description should have no "smart" chars and be and encoded.
        $encodedDescription = $sanitizeInputText->invokeArgs($this->questionImportService, [self::MGA_QUESTION_WITH_FEEDBACK['description']]);
        $this->assertStringContainsString($encodedDescription, $question['description']);

        // Question text should have no "smart" chars, and be encoded
        $encodedQuestionText = $sanitizeInputText->invokeArgs($this->questionImportService, [self::MGA_QUESTION_WITH_FEEDBACK['text']]);
        $this->assertMatchesRegularExpression('/' . preg_quote($encodedQuestionText, '/') . '\s*/s', $question['qtext']);

        // Choices in question code should have no "smart" chars and be encoded.
        for ($idx = 0; $idx < count(self::MGA_QUESTION_WITH_FEEDBACK['choices']); $idx++) {
            $choice = self::MGA_QUESTION_WITH_FEEDBACK['choices'][$idx];
            $encodedChoice = addcslashes($choice, "'\\");
            $encodedChoice = $sanitizeInputText->invokeArgs($this->questionImportService, [$encodedChoice]);

            $varName = sprintf('$questions[%d]', $idx);
            $regexQuotedVarName = preg_quote($varName);
            $regexQuotedChoice = preg_quote($encodedChoice);
            $expectedRegex = sprintf("%s = '%s';", $regexQuotedVarName, $regexQuotedChoice);

            $this->assertMatchesRegularExpression('/' . $expectedRegex . '/s', $question['control']);
        }

        // Full feedback code is tested in a different test.
        // We only assert presence of the correct feedback macro here.
        $expectedFeedbackMacroUsage = preg_quote('$feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer);');
        $this->assertMatchesRegularExpression('/' . $expectedFeedbackMacroUsage . '/s', $question['control']);
    }

    public function testBuildMultipleChoiceQuestion_WithPerAnswerFeedback_QuizTypeImport(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4

        // Method under test.
        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['quiz', self::MGA_QUESTION_WITH_FEEDBACK]);

        /*
         * Assertions specific to "quiz" import mode.
         *
         * Assertions common to testBuildMultipleChoiceQuestion_WithPerAnswerFeedback() are skipped here.
         */

        // Question text should NOT have feedback for "quiz" type import.
        $this->assertStringNotContainsString('$feedback', $question['qtext']);

        // Question code should load OHM-specific macro library for "quiz" type import.
        $this->assertStringContainsString('loadlibrary("ohm_macros");', $question['control']);

        // OHM-specific feedback macros should be used in "quiz" type import.
        $this->assertStringContainsString('ohm_getfeedbacktxt', $question['control']);
    }

    public function testBuildMultipleChoiceQuestion_WithPerAnswerFeedback_PracticeTypeImport(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4

        // Method under test.
        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['practice', self::MGA_QUESTION_WITH_FEEDBACK]);

        /*
         * Assertions specific to "practice" import mode.
         *
         * Assertions common to testBuildMultipleChoiceQuestion_WithPerAnswerFeedback() are skipped here.
         */

        // Question text should have feedback for "quiz" type import.
        $this->assertStringContainsString('$feedback', $question['qtext']);

        // Question code should NOT load OHM-specific macro library for "practice" type import.
        $this->assertStringNotContainsString('loadlibrary("ohm_macros");', $question['control']);

        // OHM-specific feedback macros should NOT be used in "quiz" type import.
        $this->assertStringContainsString('getfeedbacktxt', $question['control']);
    }

    public function testBuildMultipleChoiceQuestion_NoFeedback(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4.

        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['quiz', self::MGA_QUESTION_NO_FEEDBACK]);

        /*
         * If no feedback is present, the generated question code should use
         * a "basic feedback" macro with default feedback text.
         */

        // Full feedback code is tested in a different test.
        // We only assert presence of the correct feedback macro here.
        $this->assertEquals('choices', $question['qtype']);
        $expectedFeedbackMacroUsage = preg_quote('$feedback = ohm_getfeedbackbasic('
            . '$stuanswers[$thisq], "Correct!", "Incorrect.", $answer);');
        $this->assertMatchesRegularExpression('/' . $expectedFeedbackMacroUsage . '/s', $question['control']);
    }

    public function testBuildMultipleChoiceQuestion_LongDescription(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4.

        $questionWithLongDescription = self::MGA_QUESTION_NO_FEEDBACK;
        $questionWithLongDescription['description'] =
            str_repeat($questionWithLongDescription['description'], 10);

        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['quiz', $questionWithLongDescription]);

        $this->assertLessThan(255, strlen($question['description']));
    }

    public function testBuildMultipleChoiceQuestion_SanitizesInputsOfHtml(): void {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoiceQuestion = $class->getMethod('buildMultipleChoiceQuestion');
        $buildMultipleChoiceQuestion->setAccessible(true); // Required for PHP 7.4.

        $questionWithLongDescription = self::MGA_QUESTION_NEEDS_SANITIZATION;

        $question = $buildMultipleChoiceQuestion->invokeArgs($this->questionImportService,
            ['quiz', $questionWithLongDescription]);

        $this->assertStringNotContainsString('<script>', $question['description']);
        $this->assertStringNotContainsString('<script>', $question['qtext']);
        $this->assertStringNotContainsString('<code>', $question['control']);
        $this->assertStringNotContainsString('<input>', $question['control']);
        $this->assertStringNotContainsString('<button>', $question['control']);
    }

    /*
     * buildMultipleChoicePerAnswerFeedback
     */

    public function testBuildMultipleChoicePerAnswerFeedback(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoicePerAnswerFeedback = $class->getMethod('buildMultipleChoicePerAnswerFeedback');
        $buildMultipleChoicePerAnswerFeedback->setAccessible(true); // Required for PHP 7.4.

        $feedbackCode = $buildMultipleChoicePerAnswerFeedback->invokeArgs($this->questionImportService,
            ['quiz', self::MGA_QUESTION_WITH_FEEDBACK]);

        $this->assertMatchesRegularExpression('/\$feedbacktxt\[0\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[1\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[2\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[3\] = \'.*\';/', $feedbackCode);

        $expected = preg_quote('$feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer);');
        $this->assertMatchesRegularExpression('/' . $expected . '/', $feedbackCode);
    }

    public function testBuildMultipleChoicePerAnswerFeedback_withNullCorrect(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoicePerAnswerFeedback = $class->getMethod('buildMultipleChoicePerAnswerFeedback');
        $buildMultipleChoicePerAnswerFeedback->setAccessible(true); // Required for PHP 7.4.

        $feedbacks = self::MGA_QUESTION_WITH_FEEDBACK;
        $feedbacks['feedback']['feedbacks'][1] = null; // Set the correct answer feedback to null.
        $feedbackCode = $buildMultipleChoicePerAnswerFeedback->invokeArgs($this->questionImportService,
            ['quiz', $feedbacks]);

        $this->assertMatchesRegularExpression('/\$feedbacktxt\[0\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[1\] = \'Correct.\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[2\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[3\] = \'.*\';/', $feedbackCode);

        $expected = preg_quote('$feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer);');
        $this->assertMatchesRegularExpression('/' . $expected . '/', $feedbackCode);
    }

    public function testBuildMultipleChoicePerAnswerFeedback_withNullIncorrect(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoicePerAnswerFeedback = $class->getMethod('buildMultipleChoicePerAnswerFeedback');
        $buildMultipleChoicePerAnswerFeedback->setAccessible(true); // Required for PHP 7.4.

        $feedbacks = self::MGA_QUESTION_WITH_FEEDBACK;
        $feedbacks['feedback']['feedbacks'][2] = null; // Set the incorrect answer feedback to null.
        $feedbackCode = $buildMultipleChoicePerAnswerFeedback->invokeArgs($this->questionImportService,
            ['quiz', $feedbacks]);

        $this->assertMatchesRegularExpression('/\$feedbacktxt\[0\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[1\] = \'.*\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[2\] = \'Incorrect.\';/', $feedbackCode);
        $this->assertMatchesRegularExpression('/\$feedbacktxt\[3\] = \'.*\';/', $feedbackCode);

        $expected = preg_quote('$feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer);');
        $this->assertMatchesRegularExpression('/' . $expected . '/', $feedbackCode);
    }

    public function testBuildMultipleChoicePerAnswerFeedback_NoFeedback(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $buildMultipleChoicePerAnswerFeedback = $class->getMethod('buildMultipleChoicePerAnswerFeedback');
        $buildMultipleChoicePerAnswerFeedback->setAccessible(true); // Required for PHP 7.4.

        $feedbackCode = $buildMultipleChoicePerAnswerFeedback->invokeArgs($this->questionImportService,
            ['quiz', self::MGA_QUESTION_NO_FEEDBACK]);

        $this->assertEquals('', $feedbackCode);
    }

    /*
     * replaceEmptyFeedback
     */

    public function testReplaceEmptyFeedback_NoNulls(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $replaceFeedbackNulls = $class->getMethod('replaceEmptyFeedback');
        $replaceFeedbackNulls->setAccessible(true); // Required for PHP 7.4.

        $feedbacks = ['one', 'two', 'three', 'four'];
        $processedFeedbacks = $replaceFeedbackNulls->invokeArgs($this->questionImportService,
            [$feedbacks, 0]);

        $this->assertEquals($feedbacks, $processedFeedbacks);
    }

    public function testReplaceEmptyFeedback_WithNullCorrect(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $replaceFeedbackNulls = $class->getMethod('replaceEmptyFeedback');
        $replaceFeedbackNulls->setAccessible(true); // Required for PHP 7.4.

        $feedbacks = ['one', 'two', '', 'four'];
        $processedFeedbacks = $replaceFeedbackNulls->invokeArgs($this->questionImportService,
            [$feedbacks, 2]);

        $expectedFeedbacks = ['one', 'two', 'Correct.', 'four'];

        $this->assertEquals($expectedFeedbacks, $processedFeedbacks);
    }

    public function testReplaceEmptyFeedback_WithNullIncorrect(): void
    {
        $class = new ReflectionClass(QuestionImportService::class);
        $replaceFeedbackNulls = $class->getMethod('replaceEmptyFeedback');
        $replaceFeedbackNulls->setAccessible(true); // Required for PHP 7.4.

        $feedbacks = ['one', 'two', '', 'four'];
        $processedFeedbacks = $replaceFeedbackNulls->invokeArgs($this->questionImportService,
            [$feedbacks, 1]);

        $expectedFeedbacks = ['one', 'two', 'Incorrect.', 'four'];

        $this->assertEquals($expectedFeedbacks, $processedFeedbacks);
    }
}

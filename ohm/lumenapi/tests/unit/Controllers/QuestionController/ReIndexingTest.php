<?php

namespace Tests\Unit\Controllers\QuestionController;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use Mockery;
use PDO;
use ReflectionClass;
use Tests\TestCase;

use App\Http\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class ReIndexingTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface  $assessmentRepository;
    private QuestionSetRepositoryInterface $questionSetRepository;

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
        $this->questionController = new QuestionController($this->assessmentRepository,
            $this->questionSetRepository);

        $this->pdo = Mockery::mock(PDO::class);
        $this->questionController->setPdo($this->pdo);
    }

    public function testReIndexMultipartMultansAnswers(): void
    {
        $inputPostVars = [
            'qn0' => '',
            'qn1000' =>
                [
                    0 => 1,
                    1 => 2,
                ],
            'qn1001' => 0,
            'qn1002' => 1,
        ];

        $expectedOutputPostVars = [
            'qn0' => '',
            'qn1000' =>
                [
                    1 => 1,
                    2 => 2,
                ],
            'qn1001' => 0,
            'qn1002' => 1,
        ];

        // The many different ways $anstypes could be defined in question code.
        $questionControls = [
            "\$anstypes = \"multans,choices,choices\") // [rest of question code]...",
            "\$anstypes=\"multans,choices,choices\") // [rest of question code]...",
            "\$anstypes = array(\"multans\",\"choices\",\"choices\") // [rest of question code]...",
            "\$anstypes=array(\"multans\",\"choices\",\"choices\") // [rest of question code]...",
            "\$anstypes = [\"multans\",\"choices\",\"choices\"] // [rest of question code]...",
            "\$anstypes=[\"multans\",\"choices\",\"choices\"] // [rest of question code]..."
        ];

        // Set the reIndexMultipartMultansAnswers method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('reIndexMultipartMultansAnswers');
        $method->setAccessible(true);

        foreach ($questionControls as $questionControl) {

            $outputPostVars = $method->invokeArgs($this->questionController, [$inputPostVars, $questionControl, 42]);

            $this->assertEquals($outputPostVars, $expectedOutputPostVars);
        }
    }
}

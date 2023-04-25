<?php

namespace Tests\Unit\Controllers\QuestionController;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use Mockery;
use PDO;
use Tests\TestCase;

use App\Http\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class GetQuestionDisplayTest extends TestCase
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

    /*
     * getQuestionDisplay
     */

    public function testGetQuestionDisplay_ohm1_macro(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_with_ohm1_macro);

        $responseData = $this->questionController->getQuestionDisplay([
            'questionSetId' => 3607,
            'seed' => 3469,
        ]);

        // OHM1 macros return a string of feedback with HTML, which is
        // not usable by the Question API.
        $this->assertContains(
            'Warning: Feedback may be available but is suppressed due to the usage of OHMv1 macros!',
            $responseData['errors']
        );
    }
}

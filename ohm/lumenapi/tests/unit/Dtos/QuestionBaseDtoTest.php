<?php


use App\Dtos\QuestionBaseDto;
use App\Exceptions\MissingIdException;
use Tests\TestCase;

class QuestionBaseDtoTest extends TestCase
{

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }
    }

    public function testConstructor_MissingIds(): void
    {
        $this->expectException(MissingIdException::class);

        $request = ['seed' => 42];
        new QuestionBaseDto($request);
    }

    public function testGetQuestionSetId(): void
    {
        $request = ['seed' => '1234', 'questionSetId' => 42];
        $questionBaseDto = new QuestionBaseDto($request);

        $questionSetId = $questionBaseDto->getQuestionSetId();
        $this->assertEquals(42, $questionSetId);
    }

    public function testGetUniqueId(): void
    {
        $request = ['seed' => '1234', 'uniqueId' => 1491933600157156];
        $questionBaseDto = new QuestionBaseDto($request);

        $uniqueId = $questionBaseDto->getUniqueId();
        $this->assertEquals(1491933600157156, $uniqueId);
    }
}

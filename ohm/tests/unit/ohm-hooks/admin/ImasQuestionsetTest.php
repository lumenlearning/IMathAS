<?php

namespace OHM\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\ohm\QuestionCodeParserService;

require_once __DIR__ . '/../../../../../ohm/lumenapi/app/Services/Interfaces/QuestionCodeParserServiceInterface.php';
require_once __DIR__ . '/../../../../../ohm/lumenapi/app/Services/ohm/BaseService.php';
require_once __DIR__ . '/../../../../../ohm/lumenapi/app/Services/ohm/QuestionCodeParserService.php';

/**
 * Covers OHM hook file: /ohm-hooks/admin/imas_questionset.php
 *
 * The file under test contains only function definitions.
 * In other words, the tested file has no class.
 */
final class ImasQuestionsetTest extends TestCase
{
    /**
     * Test the onQuestionSave function
     *
     */
    public function testOnQuestionSaveIsAlgorithmic(): void
    {
        // Algorithmic question (isrand = 1)
        $this->runOnQuestionSaveTest(true, 1);
    }

    public function testOnQuestionSaveIsNotAlgorithmic(): void
    {
        // Non-algorithmic question (isrand = 0)
        $this->runOnQuestionSaveTest(false, 0);
    }

    public function testOnQuestionSaveGlobalDB(): void
    {

        // Using global $DBH when no db is injected
        $this->runOnQuestionSaveTest(true, 1, true);
    }

    /**
     * Helper method to run a test for onQuestionSave with a specific db
     * 
     * @param bool $isAlgorithmic Whether the question is algorithmic
     * @param int $expectedIsrand The expected isrand value (0 or 1)
     */
    private function runOnQuestionSaveTest(bool $isAlgorithmic, int $expectedIsrand, bool $useGlobalDB = false): void
    {
        // Test data
        $questionId = 123;
        $questionCode = 'test question code';

        // Create mocks
        $mockParser = $this->createMock(QuestionCodeParserService::class);
        $mockDb = $this->createMock(\PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

        // Set global $DBH
        $GLOBALS['DBH'] = $mockDb;

        // Configure mocks
        $mockParser->expects($this->once())
            ->method('isAlgorithmic')
            ->willReturn($isAlgorithmic);

        $mockDb->expects($this->once())
            ->method('prepare')
            ->with("UPDATE imas_questionset SET isrand = :isrand WHERE id = :id")
            ->willReturn($mockStmt);

        $mockStmt->expects($this->once())
            ->method('execute')
            ->with([':isrand' => $expectedIsrand, ':id' => $questionId]);

        // Call the function with the DB
        require(__DIR__ . '/../../../../../ohm-hooks/admin/imas_questionset.php');
        $onQuestionSave($questionId, $questionCode, $useGlobalDB ? null : $mockDb, $mockParser);
    }
}

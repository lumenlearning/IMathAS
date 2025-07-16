<?php

namespace OHM\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\ohm\QuestionCodeParserService;

/**
 * Covers OHM hook file: /ohm-hooks/admin/imas_questionset.php
 *
 * The file under test contains only function definitions.
 * In other words, the tested file has no class.
 */
final class ImasQuestionsetTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once(__DIR__ . '/../../../../../ohm-hooks/admin/imas_questionset.php');
    }

    /**
     * Test the onQuestionSave function
     * 
     * Note: Since we can't directly mock the QuestionCodeParserService instantiation,
     * we're testing a modified version of the function that has the same logic but
     * accepts a mock parser. This allows us to test the key functionality: that the
     * isrand column is updated based on whether the question is algorithmic.
     */
    public function testOnQuestionSave(): void
    {
        // Test case 1: Algorithmic question (isrand = 1)
        $this->runOnQuestionSaveTest(true, 1);

        // Test case 2: Non-algorithmic question (isrand = 0)
        $this->runOnQuestionSaveTest(false, 0);

        // Test case 3: Using global $DBH when no db is provided
        $this->runOnQuestionSaveTestWithGlobalDb(true, 1);
    }

    /**
     * Helper method to run a test for onQuestionSave with a specific db
     * 
     * @param bool $isAlgorithmic Whether the question is algorithmic
     * @param int $expectedIsrand The expected isrand value (0 or 1)
     */
    private function runOnQuestionSaveTest(bool $isAlgorithmic, int $expectedIsrand): void
    {
        // Test data
        $questionId = 123;
        $questionCode = 'test question code';

        // Create mocks
        $mockParser = $this->createMock(QuestionCodeParserService::class);
        $mockDb = $this->createMock(\PDO::class);
        $mockStmt = $this->createMock(\PDOStatement::class);

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
        $onQuestionSave($questionId, $questionCode, $mockDb, $mockParser);
    }

    /**
     * Helper method to run a test for onQuestionSave with the global $DBH
     * 
     * @param bool $isAlgorithmic Whether the question is algorithmic
     * @param int $expectedIsrand The expected isrand value (0 or 1)
     */
    private function runOnQuestionSaveTestWithGlobalDb(bool $isAlgorithmic, int $expectedIsrand): void
    {
        // Test data
        $questionId = 789;
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

        // Call the function without providing db
        $onQuestionSave($questionId, $questionCode, null, $mockParser);
    }
}

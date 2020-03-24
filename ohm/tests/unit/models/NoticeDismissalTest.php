<?php

namespace OHM\Tests;

use DateTime;
use Exception;
use OHM\Exceptions\DatabaseWriteException;
use OHM\Models\NoticeDismissal;
use PDO;
use PHPUnit\Framework\TestCase;


/**
 * @covers NoticeDismissal
 */
final class NoticeDismissalTest extends TestCase
{
    private $noticeDismissal;

    /* @var PDO */
    private $dbh;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);

        $this->noticeDismissal = new NoticeDismissal($this->dbh);
    }

    /*
     * find
     */

    public function testFind(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn([
            'id' => 42,
            'userid' => 1234,
            'noticeid' => 589,
            'dismissed_at' => '2019-10-02 18:41:17',
        ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->noticeDismissal->find(42);

        $this->assertTrue($result, 'should return true if NoticeDismissal was found.');
        $this->assertEquals(42, $this->noticeDismissal->getId(), 'should return the correct value from the DB');
        $this->assertEquals(1234, $this->noticeDismissal->getUserId(), 'should return the correct value from the DB');
        $this->assertEquals(589, $this->noticeDismissal->getNoticeId(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2019-10-02 18:41:17'), $this->noticeDismissal->getDismissedAt(), 'should return the correct value from the DB');
    }

    public function testFind_NotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->noticeDismissal->find(42);

        $this->assertFalse($result, 'should return false if ID was not found.');
    }

    /*
     * findByUserIdAndNoticeId
     */

    public function testFindByUserIdAndNoticeId(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn([
            'id' => 42,
            'userid' => 1234,
            'noticeid' => 589,
            'dismissed_at' => '2019-10-02 18:41:17',
        ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->noticeDismissal->findByUserIdAndNoticeId(42, 589);

        $this->assertTrue($result, 'should return true if NoticeDismissal was found.');
        $this->assertEquals(42, $this->noticeDismissal->getId(), 'should return the correct value from the DB');
        $this->assertEquals(1234, $this->noticeDismissal->getUserId(), 'should return the correct value from the DB');
        $this->assertEquals(589, $this->noticeDismissal->getNoticeId(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2019-10-02 18:41:17'), $this->noticeDismissal->getDismissedAt(), 'should return the correct value from the DB');
    }

    public function testFindByUserIdAndNoticeId_NotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->noticeDismissal->findByUserIdAndNoticeId(42, 589);

        $this->assertFalse($result, 'should return false if ID was not found.');
    }

    /*
     * dismissNoticeNow
     */

    public function testDismissNoticeNow_MissingUserId(): void
    {
        $this->noticeDismissal->setNoticeId(589);

        $this->expectException(Exception::class);

        $this->noticeDismissal->dismissNoticeNow();
    }

    public function testDismissNoticeNow_MissingBannerId(): void
    {
        $this->noticeDismissal->setUserId(42);

        $this->expectException(Exception::class);

        $this->noticeDismissal->dismissNoticeNow();
    }

    public function testDismissNoticeNow_MissingAll(): void
    {
        $this->expectException(Exception::class);

        $this->noticeDismissal->dismissNoticeNow();
    }

    /*
     * save
     */

    public function testSave(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $this->dbh->method('prepare')->willReturn($pdoStatement);
        $this->dbh->method('lastInsertId')->willReturn(43);

        // A light check for a DB write.
        $pdoStatement->expects($this->once())->method('execute');

        $result = $this->noticeDismissal->save();

        $this->assertTrue($result, 'should return true on successful save.');
        $this->assertEquals(43, $this->noticeDismissal->getId(), 'the last inserted row ID should be set.');
    }

    public function testSave_Failed(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(false);
        $pdoStatement->method('errorInfo')->willReturn(['oops', 'it', 'failed']);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        // An exception should be thrown if we can't write to the DB.
        $this->expectException(DatabaseWriteException::class);

        $this->noticeDismissal->save();
    }
}

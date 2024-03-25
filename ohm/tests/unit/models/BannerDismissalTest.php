<?php

namespace OHM\Tests;

use DateTime;
use Exception;
use OHM\Exceptions\DatabaseWriteException;
use OHM\Models\BannerDismissal;
use PDO;
use PHPUnit\Framework\TestCase;


/**
 * @covers BannerDismissal
 */
final class BannerDismissalTest extends TestCase
{
    private $bannerDismissal;

    /* @var PDO */
    private $dbh;

    function setUp(): void
    {
        $this->dbh = $this->createMock(\PDO::class);

        $this->bannerDismissal = new BannerDismissal($this->dbh);
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

        $result = $this->bannerDismissal->find(42);

        $this->assertTrue($result, 'should return true if BannerDismissal was found.');
        $this->assertEquals(42, $this->bannerDismissal->getId(), 'should return the correct value from the DB');
        $this->assertEquals(1234, $this->bannerDismissal->getUserId(), 'should return the correct value from the DB');
        $this->assertEquals(589, $this->bannerDismissal->getBannerId(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2019-10-02 18:41:17'), $this->bannerDismissal->getDismissedAt(), 'should return the correct value from the DB');
    }

    public function testFind_NotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->bannerDismissal->find(42);

        $this->assertFalse($result, 'should return false if ID was not found.');
    }

    /*
     * findByUserIdAndBannerId
     */

    public function testFindByUserIdAndBannerId(): void
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

        $result = $this->bannerDismissal->findByUserIdAndBannerId(42, 589);

        $this->assertTrue($result, 'should return true if BannerDismissal was found.');
        $this->assertEquals(42, $this->bannerDismissal->getId(), 'should return the correct value from the DB');
        $this->assertEquals(1234, $this->bannerDismissal->getUserId(), 'should return the correct value from the DB');
        $this->assertEquals(589, $this->bannerDismissal->getBannerId(), 'should return the correct value from the DB');
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2019-10-02 18:41:17'), $this->bannerDismissal->getDismissedAt(), 'should return the correct value from the DB');
    }

    public function testFindByUserIdAndBannerId_NotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->bannerDismissal->findByUserIdAndBannerId(42, 589);

        $this->assertFalse($result, 'should return false if ID was not found.');
    }

    /*
     * dismissBannerNow
     */

    public function testDismissBannerNow_MissingUserId(): void
    {
        $this->bannerDismissal->setBannerId(589);

        $this->expectException(Exception::class);

        $this->bannerDismissal->dismissBannerNow();
    }

    public function testDismissBannerNow_MissingBannerId(): void
    {
        $this->bannerDismissal->setUserId(42);

        $this->expectException(Exception::class);

        $this->bannerDismissal->dismissBannerNow();
    }

    public function testDismissBannerNow_MissingAll(): void
    {
        $this->expectException(Exception::class);

        $this->bannerDismissal->dismissBannerNow();
    }

    /*
     * save
     */

    public function testSave(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(true);
        $this->dbh->method('prepare')->willReturn($pdoStatement);
        $this->dbh->method('lastInsertId')->willReturn('43');

        // A light check for a DB write.
        $pdoStatement->expects($this->once())->method('execute');

        $result = $this->bannerDismissal->save();

        $this->assertTrue($result, 'should return true on successful save.');
        $this->assertEquals(43, $this->bannerDismissal->getId(), 'the last inserted row ID should be set.');
    }

    public function testSave_Failed(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('execute')->willReturn(false);
        $pdoStatement->method('errorInfo')->willReturn(['oops', 'it', 'failed']);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        // An exception should be thrown if we can't write to the DB.
        $this->expectException(DatabaseWriteException::class);

        $this->bannerDismissal->save();
    }
}

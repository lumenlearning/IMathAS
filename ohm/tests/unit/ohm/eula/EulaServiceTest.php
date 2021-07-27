<?php

namespace OHM\Tests;

use OHM\Eula\EulaService;
use OHM\Exceptions\DatabaseReadException;
use OHM\Exceptions\DatabaseWriteException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;


/**
 * @covers EulaService
 */
final class EulaServiceTest extends TestCase
{
    /* @var EulaService */
    private $eulaService;

    /* @var PDO */
    private $dbh;

    function setUp(): void
    {
        $this->dbh = $this->createMock(PDO::class);
        $this->eulaServiceMock = $this->createMock(EulaService::class);

        $this->eulaService = new EulaService($this->dbh);

        putenv('EULA_ENABLED=true');
        unset($GLOBALS['isLmsUser']);
    }

    /*
     * isAcceptanceRequired
     */

    public function testIsAcceptanceRequired_FeatureDisabled(): void
    {
        putenv('EULA_ENABLED'); // This unsets the environment variable.

        $result = $this->eulaService->isAcceptanceRequired(1);
        $this->assertFalse($result);
    }

    public function testIsAcceptanceRequired_ExcludedFalse_AcceptedFalse(): void
    {
        $_SERVER['REQUEST_URI'] = 'meow';

        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('fetch')->willReturn([0]);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $result = $this->eulaService->isAcceptanceRequired(1);
        $this->assertTrue($result);
    }

    public function testIsAcceptanceRequired_ExcludedFalse_AcceptedTrue(): void
    {
        $_SERVER['REQUEST_URI'] = 'meow';

        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('fetch')->willReturn([EulaService::EULA_LATEST_VERSION]);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $result = $this->eulaService->isAcceptanceRequired(1);
        $this->assertFalse($result);
    }

    public function testIsAcceptanceRequired_ExcludedTrue_AcceptedFalse(): void
    {
        $_SERVER['REQUEST_URI'] = '/ohm/eula/index.php';

        $result = $this->eulaService->isAcceptanceRequired(1);
        $this->assertFalse($result);
    }

    public function testIsAcceptanceRequired_ExcludedTrue_AcceptedTrue(): void
    {
        $_SERVER['REQUEST_URI'] = '/ohm/eula/index.php';

        $result = $this->eulaService->isAcceptanceRequired(1);
        $this->assertFalse($result);
    }

    /*
     * getUserAcceptanceVersion
     */

    public function testGetUserAcceptanceVersion(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(1);
        $pdoStatement->expects($this->once())
            ->method('fetch')->willReturn([42]);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $acceptedVersion = $this->eulaService->getUserAcceptanceVersion(1);
        $this->assertEquals(42, $acceptedVersion);
    }

    public function testGetUserAcceptanceVersion_Exception(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('rowCount')->willReturn(0);
        $pdoStatement->expects($this->once())
            ->method('errorInfo')->willReturn(['Intentional mock DB error. Meow!']);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $this->expectException(DatabaseReadException::class);
        $this->eulaService->getUserAcceptanceVersion(1);
    }

    /*
     * updateUserAcceptanceToLatest
     */

    public function testUpdateUserAcceptanceToLatest(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(true);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $result = $this->eulaService->updateUserAcceptanceToLatest(1);
        $this->assertTrue($result);
    }

    public function testUpdateUserAcceptanceToLatest_Exception(): void
    {
        $pdoStatement = $this->createMock(PDOStatement::class);
        $pdoStatement->expects($this->once())
            ->method('execute')->willReturn(false);
        $pdoStatement->expects($this->once())
            ->method('errorInfo')->willReturn(['Intentional mock DB error. Meow!']);
        $this->dbh->expects($this->once())
            ->method('prepare')->willReturn($pdoStatement);

        $this->expectException(DatabaseWriteException::class);
        $this->eulaService->updateUserAcceptanceToLatest(1);
    }

    /*
     * isCurrentPageExcludedFromEula
     */

    public function testIsCurrentPageExcludedFromEula_NotExcluded(): void
    {
        $_SERVER['REQUEST_URI'] = 'meow';
        $result = $this->eulaService->isCurrentPageExcludedFromEula();
        $this->assertFalse($result);
    }

    public function testIsCurrentPageExcludedFromEula_IsExcluded(): void
    {
        $_SERVER['REQUEST_URI'] = '/ohm/eula/index.php';
        $result = $this->eulaService->isCurrentPageExcludedFromEula();
        $this->assertTrue($result);
    }

    /*
     * isLmsUser
     */

    public function testisLmsUser_True(): void
    {
        $GLOBALS['isLmsUser'] = true;
        $result = $this->eulaService->isLmsUser();
        $this->assertTrue($result);
    }

    public function testisLmsUser_NotSet(): void
    {
        $result = $this->eulaService->isLmsUser();
        $this->assertFalse($result);
    }

    public function testisLmsUser_False(): void
    {
        $GLOBALS['isLmsUser'] = 'meow';
        $result = $this->eulaService->isLmsUser();
        $this->assertFalse($result);
    }


}

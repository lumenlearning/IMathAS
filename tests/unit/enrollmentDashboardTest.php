<?php

/**
 * This unit test is designed to alert any breaking changes to the database schema, 
 * from the perspective of enrollment reporting.
 * These database columns and types are expected by the reporting process, as-is.
 * If any of these tests fail - alert and examine the enrollment reporting lambda in lumen-analytics.
 */

use PHPUnit\Framework\TestCase;

class EnrollmentDashboardTest extends TestCase
{
    /** @var PDO */
    protected $DBH;

    protected function setUp(): void
    {
        // Copied directly from /config/local.php.
        $dbserver = getenv('DB_SERVER') ?: '127.0.0.1';
        $dbname = getenv('DB_NAME') ?: 'ohm';
        $dbusername = getenv('DB_USERNAME') ?: 'ohm';
        $dbpassword = getenv('DB_PASSWORD') ?: 'ohm';
        $dbcharset = getenv('DB_CHARSET') ?: 'latin1';

        // Copied directly from /config.php.
        try {
            $DBH = new PDO("mysql:host=$dbserver;dbname=$dbname;charset=$dbcharset", $dbusername, $dbpassword);
            $DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("<p>Could not connect to database: <b>" . $e->getMessage() . "</b></p></div></body></html>");
        }

        $DBH->query("set session sql_mode=''");

        unset($dbserver);
        unset($dbname);
        unset($dbusername);
        unset($dbpassword);
        unset($dbcharset);

        $this->DBH = $DBH;

        try {
            $this->DBH->beginTransaction();
        } catch (PDOException $e) {
            $this->fail("Failed to begin transaction: " . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if ($this->DBH && $this->DBH->inTransaction()) {
            $this->DBH->rollBack();
        }
    }

    protected function assertColumnType(
        string $tableName,
        string $columnName,
        string $expectedType,
    ): void {
        $query = "
            SELECT COLUMN_NAME, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column
        ";

        try {
            $stmt = $this->DBH->prepare($query);
            $stmt->execute([
                ':table' => $tableName,
                ':column' => $columnName,
            ]);
            $column = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->fail("Query failed: " . $e->getMessage());
            return;
        }

        $this->assertNotFalse($column, "Column '$columnName' does not exist on table '$tableName'.");
        $this->assertEquals($expectedType, strtolower($column['DATA_TYPE']), "Expected type '$expectedType' for column '$columnName'.");
    }


    public function testColumnsTypesExistsOnImasCourses()
    {
        $this->assertColumnType('imas_courses', 'id', 'int');
        $this->assertColumnType('imas_courses', 'name', 'varchar');
        $this->assertColumnType('imas_courses', 'id', 'int');
        $this->assertColumnType('imas_courses', 'level', 'varchar');
    }

    public function testColumnsTypesExistsOnImasUsers()
    {
        $this->assertColumnType('imas_users', 'id', 'int');
        $this->assertColumnType('imas_users', 'rights', 'smallint');
        $this->assertColumnType('imas_users', 'FirstName', 'varchar');
        $this->assertColumnType('imas_users', 'LastName', 'varchar');
        $this->assertColumnType('imas_users', 'email', 'varchar');
    }

    public function testColumnsTypesExistsOnImasGroups()
    {
        $this->assertColumnType('imas_groups', 'id', 'int');
        $this->assertColumnType('imas_groups', 'parent', 'int');
        $this->assertColumnType('imas_groups', 'name', 'varchar');
        $this->assertColumnType('imas_groups', 'lumen_guid', 'varchar');
    }

    public function testColumnsTypesExistsOnImasStudents()
    {
        $this->assertColumnType('imas_students', 'id', 'int');
        $this->assertColumnType('imas_students', 'courseid', 'int');
        $this->assertColumnType('imas_students', 'userid', 'int');
        $this->assertColumnType('imas_students', 'created_at', 'int');
        $this->assertColumnType('imas_students', 'lastaccess', 'int');
    }

    public function testColumnsTypesExistsOnImasLITCourses()
    {
        $this->assertColumnType('imas_lti_courses', 'courseid', 'int');
        $this->assertColumnType('imas_lti_courses', 'copiedfrom', 'int');
    }

    public function testColumnsTypesExistsOnImasTeacherAuditLog()
    {
        $this->assertColumnType('imas_teacher_audit_log', 'metadata', 'blob');
        $this->assertColumnType('imas_teacher_audit_log', 'courseid', 'int');
    }

    public function testColumnsTypesExistsOnImasTeachers()
    {
        $this->assertColumnType('imas_teachers', 'userid', 'int');
        $this->assertColumnType('imas_teachers', 'courseid', 'int');
    }
}

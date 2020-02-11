<?php

namespace OHM\Tests;

require_once(__DIR__ . '/../../../includes/OAuth.php');
require_once(__DIR__ . '/../../../includes/ltioauthstore.php');
require_once(__DIR__ . '/../../../includes/ltiroles.php');

use Desmos\Lti\BasicLti;
use IMathASLTIOAuthDataStore;
use OAuthServer;
use OAuthSignatureMethod_HMAC_SHA1;
use PHPUnit\Framework\TestCase;


/**
 * @covers BasicLti
 */
final class BasicLtiTest extends TestCase
{
    private $dbh;
    private $basicLti;
    private $request;

    private $iMathASLTIOAuthDataStoreMock;
    private $oAuthServerMock;
    private $oAuthSignatureMethod_HMAC_SHA1_mock;

    private const REQUIRED_LTI_DATA = ['lti_version', 'user_id', 'context_id',
        'roles', 'oauth_consumer_key'];

    function setUp(): void
    {
        $this->iMathASLTIOAuthDataStoreMock = $this->createMock(IMathASLTIOAuthDataStore::class);
        $this->oAuthServerMock = $this->createMock(OAuthServer::class);
        $this->oAuthSignatureMethod_HMAC_SHA1_mock = $this->createMock(OAuthSignatureMethod_HMAC_SHA1::class);
        $this->dbh = $this->createMock(\PDO::class);

        $this->request = [
            'lti_version' => 'LTI-1p0',
            'user_id' => '2ff1063639d168179de19548b97e965321ae1812',
            'context_id' => 'c743af80219d32673225edc80d87cf4b3a3ddb66',
            'roles' => 'Instructor,urn:lti:instrole:ims/lis/Administrator',
            'oauth_consumer_key' => 'lumenltitest',
            'lis_outcome_service_url' => 'http://127.0.01/',
            'custom_item_id' => '2983925',
        ];

        $this->basicLti = new BasicLti($this->request, $this->dbh);
        $this->basicLti->setOauthDependencies(
            $this->iMathASLTIOAuthDataStoreMock,
            $this->oAuthServerMock,
            $this->oAuthSignatureMethod_HMAC_SHA1_mock
        );
    }

    /*
     * setRequest
     */

    public function testSetRequest(): void
    {
        $this->assertEquals($this->request['custom_item_id'], $this->basicLti->getItemId(),
            'Custom item ID (from imas_items) should be set for the Desmos item');
    }

    /*
     * hasValidLtiData
     */

    public function testHasValidLtiData(): void
    {
        $result = $this->basicLti->hasValidLtiData();

        $this->assertEmpty($result, 'should return an empty array.');
    }

    public function testHasValidLtiData_MissingData(): void
    {
        foreach (self::REQUIRED_LTI_DATA as $requiredParam) {
            $request = $this->request;
            $request[$requiredParam] = '';
            $this->basicLti->setRequest($request);

            $result = $this->basicLti->hasValidLtiData();

            $this->assertCount(1, $result,
                'should return an array with an error message for missing data: ' . $requiredParam);
        }
    }

    public function testHasValidLtiData_EmptyData(): void
    {
        foreach (self::REQUIRED_LTI_DATA as $requiredLtiData) {
            $this->request[$requiredLtiData] = '';
        }
        $this->basicLti->setRequest($this->request);

        $result = $this->basicLti->hasValidLtiData();

        $dataCountRequired = count(self::REQUIRED_LTI_DATA);
        $this->assertCount($dataCountRequired, $result,
            sprintf('should return an array with %d error messages.', $dataCountRequired)
        );
    }

    /*
     * authenticate
     */

    public function testAuthenticate(): void
    {
        // FIXME: Leaving this here as a reminder. Be annoyed. Be very annoyed
        //        by a warning message every time tests run until this is fixed.
        $this->markTestIncomplete('Test broken due to usage of static method in tested class');

        $this->oAuthServerMock->method('verify_request')->willReturn([['groupid' => 42]]);

        $this->basicLti->authenticate();

        $this->assertEquals(42, $this->basicLti->getOhmCourseGroupId(),
            'OHM course group ID should be 42.');
    }

    /*
     * assignRoleFromRequest
     */

    public function testAssignRoleFromRequest_Teacher(): void
    {
        $this->request['roles'] = 'Instructor,urn:lti:instrole:ims/lis/Administrator';

        $this->basicLti->getRoleFromRequest();

        $this->assertEquals('instructor', $this->basicLti->getRoleFromRequest(),
            'should get role "instructor" based on request data');
    }

    public function testAssignRoleFromRequest_Student(): void
    {
        $this->request['roles'] = 'Learner,urn:lti:role:ims/lis/learner';
        $this->basicLti->setRequest($this->request);

        $this->basicLti->getRoleFromRequest();

        $this->assertEquals('learner', $this->basicLti->getRoleFromRequest(),
            'should get role "learner" based on request data');
    }

    public function testAssignRoleFromRequest_Unknown(): void
    {
        $this->request['roles'] = 'meow';
        $this->basicLti->setRequest($this->request);

        $this->basicLti->getRoleFromRequest();

        $this->assertEquals('learner', $this->basicLti->getRoleFromRequest(),
            'should get role "learner" role is unknown');
    }

    public function testAssignRoleFromRequest_Missing(): void
    {
        $this->request['roles'] = '';
        $this->basicLti->setRequest($this->request);

        $this->basicLti->getRoleFromRequest();

        $this->assertEquals('learner', $this->basicLti->getRoleFromRequest(),
            'should get role "learner" if role data is missing in request');
    }

    /*
     * formatOrgFromRequest
     */

    public function testSetOrgFromRequest_TypeG(): void
    {
        $this->request['tool_consumer_instance_guid'] = 'c473adc17af0f60ca71e6760389c51cdae8d0fee.canvas.instructure.com';
        $this->basicLti->setRequest($this->request);
        $expected = 'lumenltitest:c473adc17af0f60ca71e6760389c51cdae8d0fee.canvas.instructure.com';

        $result = $this->basicLti->setOrgFromRequest();

        // test the returned string.
        $this->assertEquals($expected, $result, 'org should contain a valid value.');
        // test the class field.
        $this->assertEquals($expected, $this->basicLti->getOrg(), 'org should be "Unknown".');
    }

    public function testSetOrgFromRequest_TypeG_Unknown(): void
    {
        $this->request['tool_consumer_instance_guid'] = '';
        $this->basicLti->setRequest($this->request);
        $expected = 'lumenltitest:Unknown';

        $result = $this->basicLti->setOrgFromRequest();

        // test the returned string.
        $this->assertEquals($expected, $result, 'org should be "Unknown".');
        // test the class field.
        $this->assertEquals($expected, $this->basicLti->getOrg(), 'org should be "Unknown".');
    }

    public function testSetOrgFromRequest_TypeGC(): void
    {
        $this->request['oauth_consumer_key'] = 'LTIkey_lumenltitest';
        $this->request['tool_consumer_instance_guid'] = 'c473adc17af0f60ca71e6760389c51cdae8d0fee.canvas.instructure.com';
        $this->basicLti->setRequest($this->request);
        $expected = 'lumenltitest:c473adc17af0f60ca71e6760389c51cdae8d0fee.canvas.instructure.com';

        $result = $this->basicLti->setOrgFromRequest();

        // test the returned string.
        $this->assertEquals($expected, $result, 'org should contain a valid value.');
        // test the class field.
        $this->assertEquals($expected, $this->basicLti->getOrg(), 'org should be "Unknown".');
    }

    public function testSetOrgFromRequest_TypeGC_Unknown(): void
    {
        $this->request['oauth_consumer_key'] = 'LTIkey_lumenltitest';
        $this->basicLti->setRequest($this->request);
        $expected = 'lumenltitest:Unknown';

        $result = $this->basicLti->setOrgFromRequest();

        // test the returned string.
        $this->assertEquals($expected, $result, 'org should be "Unknown".');
        // test the class field.
        $this->assertEquals($expected, $this->basicLti->getOrg(), 'org should be "Unknown".');
    }

    /*
     * assignOhmUserFromLaunch
     */

    public function testAssignOhmUserFromLaunch(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn(['userid' => 42]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->basicLti->assignOhmUserFromLaunch();

        // test the returned value.
        $this->assertEquals(42, $result, 'the OHM user ID should be 42.');
        // test the class field.
        $this->assertEquals(42, $this->basicLti->getOhmUserId(), 'the OHM user ID should be 42.');
    }

    public function testAssignOhmUserFromLaunch_UserNotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $this->expectException(\Exception::class);

        $this->basicLti->assignOhmUserFromLaunch();
    }

    /*
     * assignDesmosItemDataFromLaunch
     */

    public function testAssignDesmosItemDataFromLaunch(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn([
            'courseid' => 1234,
            'course_name' => 'Cats are cute!',
            'desmos_id' => 84,
            'desmos_title' => 'Desmos Meow',
        ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $result = $this->basicLti->assignDesmosItemDataFromLaunch();

        // test the returned value.
        $this->assertEquals(84, $result, 'the Desmos ID should be set.');
        // test the class fields.
        $this->assertEquals(1234, $this->basicLti->getOhmCourseId(), 'the OHM course ID should be set.');
        $this->assertEquals('Cats are cute!', $this->basicLti->getOhmCourseName(), 'the OHM course name should be set.');
        $this->assertEquals(84, $this->basicLti->getDesmosItemId(), 'the Desmos ID should be set.');
        $this->assertEquals('Desmos Meow', $this->basicLti->getDesmosTitle(), 'the Desmos item title should be set');
    }

    public function testAssignDesmosItemDataFromLaunch_CourseNotFound(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(0);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $this->expectException(\Exception::class);

        $this->basicLti->assignDesmosItemDataFromLaunch();
    }

    /*
     * assignOhmDataFromLaunch
     */

    // Error cases are tested in assignOhmCourseFromLaunch and assignOhmUserFromLaunch.
    public function testAssignOhmDataFromLaunch(): void
    {
        $pdoStatement = $this->createMock(\PDOStatement::class);
        $pdoStatement->method('rowCount')->willReturn(1);
        $pdoStatement->method('fetch')->willReturn([
            'courseid' => 1234,                 // for assignOhmCourseFromLaunch()
            'course_name' => 'Cats are cute!',  // for assignOhmCourseFromLaunch()
            'desmos_id' => 84,                  // for assignOhmCourseFromLaunch()
            'desmos_title' => 'Desmos Meow',    // for assignOhmCourseFromLaunch()
            'userid' => 42,                     // for assignOhmUserFromLaunch()
        ]);
        $this->dbh->method('prepare')->willReturn($pdoStatement);

        $this->basicLti->assignOhmDataFromLaunch();

        $this->assertEquals(42, $this->basicLti->getOhmUserId(), 'the OHM user ID should be 42.');
        $this->assertEquals(1234, $this->basicLti->getOhmCourseId(), 'the OHM course ID should be set.');
        $this->assertEquals('Cats are cute!', $this->basicLti->getOhmCourseName(), 'the OHM course name should be set.');
        $this->assertEquals(84, $this->basicLti->getDesmosItemId(), 'the Desmos item ID should be set.');
        $this->assertEquals('Desmos Meow', $this->basicLti->getDesmosTitle(), 'the Desmos item title should be set');
    }
}


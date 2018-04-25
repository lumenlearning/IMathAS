<?php

require(__DIR__ . '/../../includes/ltiroles.php');

use PHPUnit\Framework\TestCase;

/**
 * @covers LTIRoles
 */
final class LTIRolesTest extends TestCase
{

	/*
	 * getOriginalLtiRoleData
	 */

	public function testGetOriginalLtiRoleData()
	{
		$roleData = 'urn:lti:instrole:ims/lis/Student,urn:lti:role:ims/lis/Learner,urn:lti:sysrole:ims/lis/User';
		$ltiRoles = new LTIRoles($roleData);

		$this->assertEquals($roleData, $ltiRoles->getOriginalLtiRoleData());
	}

	/*
	 * getRoles
	 */

	public function testGetRoles()
	{
		$roleData = 'urn:lti:instrole:ims/lis/Student,urn:lti:role:ims/lis/Learner,urn:lti:sysrole:ims/lis/User,Learner';
		$ltiRoles = new LTIRoles($roleData);
		$ltiRoleList = $ltiRoles->getRoles();

		$this->assertCount(4, $ltiRoleList);
		$this->assertContains('urn:lti:instrole:ims/lis/Student', $ltiRoleList);
		$this->assertContains('urn:lti:role:ims/lis/Learner', $ltiRoleList);
		$this->assertContains('urn:lti:sysrole:ims/lis/User', $ltiRoleList);
		$this->assertContains('Learner', $ltiRoleList);
	}

	/*
	 * getRolesLowerCase
	 */

	public function testGetRolesLowerCase()
	{
		$roleData = 'urn:lti:instrole:ims/lis/Student,urn:lti:role:ims/lis/Learner,urn:lti:sysrole:ims/lis/User,Learner';
		$ltiRoles = new LTIRoles($roleData);
		$ltiRoleList = $ltiRoles->getRolesLowerCase();

		$this->assertCount(4, $ltiRoleList);
		$this->assertContains('urn:lti:instrole:ims/lis/student', $ltiRoleList);
		$this->assertContains('urn:lti:role:ims/lis/learner', $ltiRoleList);
		$this->assertContains('urn:lti:sysrole:ims/lis/user', $ltiRoleList);
		$this->assertContains('learner', $ltiRoleList);
	}

	/*
	 * isInstructorForOurPurposes
	 */

	public function testIsInstructorForOurPurposes1()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/Instructor');
		$this->assertTrue($ltiRoles->isInstructorForOurPurposes());
	}

	public function testIsInstructorForOurPurposes2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/ContentDeveloper');
		$this->assertTrue($ltiRoles->isInstructorForOurPurposes());
	}

	public function testIsInstructorForOurPurposes3()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/Administrator');
		$this->assertTrue($ltiRoles->isInstructorForOurPurposes());
	}

	public function testIsInstructorForOurPurposes4()
	{
		$ltiRoles = new LTIRoles('urn:lti:instrole:ims/lis/Administrator');
		$this->assertTrue($ltiRoles->isInstructorForOurPurposes());
	}

	public function testIsInstructorForOurPurposesFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isInstructorForOurPurposes());
	}

	/*
	 * isInstitutionAdmin
	 */

	public function testIsInstitutionAdmin1()
	{
		$ltiRoles = new LTIRoles('urn:lti:instrole:ims/lis/Administrator');
		$this->assertTrue($ltiRoles->isInstitutionAdmin());
	}

	public function testIsInstitutionAdminFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isInstitutionAdmin());
	}

	/*
	 * isContextStudent
	 */

	public function testIsContextStudent1()
	{
		$ltiRoles = new LTIRoles('Learner');
		$this->assertTrue($ltiRoles->isContextStudent());
	}

	public function testIsContextStudent2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/Learner');
		$this->assertTrue($ltiRoles->isContextStudent());
	}

	public function testIsContextStudent3()
	{
		$ltiRoles = new LTIRoles('Student');
		$this->assertTrue($ltiRoles->isContextStudent());
	}

	public function testIsContextStudentFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isContextStudent());
	}

	/*
	 * isContextInstructor
	 */

	public function testIsContextInstructor1()
	{
		$ltiRoles = new LTIRoles('Instructor');
		$this->assertTrue($ltiRoles->isContextInstructor());
	}

	public function testIsContextInstructor2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/Instructor');
		$this->assertTrue($ltiRoles->isContextInstructor());
	}

	public function testIsContextInstructorFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isContextInstructor());
	}

	/*
	 * isContextContentDeveloper
	 */

	public function testIsContextContentDeveloper1()
	{
		$ltiRoles = new LTIRoles('ContentDeveloper');
		$this->assertTrue($ltiRoles->isContextContentDeveloper());
	}

	public function testIsContextContentDeveloper2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/ContentDeveloper');
		$this->assertTrue($ltiRoles->isContextContentDeveloper());
	}

	public function testIsContextContentDeveloperFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isContextContentDeveloper());
	}

	/*
	 * isContextAdmin
	 */

	public function testIsContextAdmin1()
	{
		$ltiRoles = new LTIRoles('Administrator');
		$this->assertTrue($ltiRoles->isContextAdmin());
	}

	public function testIsContextAdmin2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/Administrator');
		$this->assertTrue($ltiRoles->isContextAdmin());
	}

	public function testIsContextAdminFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isContextAdmin());
	}

	/*
	 * isTeachingAssistant
	 */

	public function testIsTeachingAssistant1()
	{
		$ltiRoles = new LTIRoles('TeachingAssistant');
		$this->assertTrue($ltiRoles->isTeachingAssistant());
	}

	public function testIsTeachingAssistant2()
	{
		$ltiRoles = new LTIRoles('urn:lti:role:ims/lis/TeachingAssistant');
		$this->assertTrue($ltiRoles->isTeachingAssistant());
	}

	public function testIsTeachingAssistantFalse()
	{
		$ltiRoles = new LTIRoles('asdf');
		$this->assertFalse($ltiRoles->isTeachingAssistant());
	}

}

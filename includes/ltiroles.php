<?php

/**
 * Class LTIRoles Represents an immutable collection of LTI roles.
 *
 * The roles are obtained by parsing the raw roles string received via LTI.
 */
class LTIRoles
{

	private $originalLtiRoleData;
	private $roles;
	private $rolesLowerCase;

	/**
	 * @param $ltiRoleData string An unmodified string of roles received via LTI.
	 */
	public function __construct($ltiRoleData = '')
	{
		$this->originalLtiRoleData = $ltiRoleData;

		$this->roles = explode(',', $ltiRoleData);
		$this->rolesLowerCase = array_map('strtolower', $this->roles);
	}

	/**
	 * @return string
	 */
	public function getOriginalLtiRoleData()
	{
		return $this->originalLtiRoleData;
	}

	/**
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @return array
	 */
	public function getRolesLowerCase()
	{
		return $this->rolesLowerCase;
	}

	/**
	 * @return bool
	 */
	function isInstitutionAdmin()
	{
		$validRoles = array(
			'urn:lti:instrole:ims/lis/administrator'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

	/**
	 * @return bool
	 */
	function isContextStudent()
	{
		// 'Student' isn't in the LTI docs as a standard context role, but some TCs send it.
		$validRoles = array(
			'learner',
			'urn:lti:role:ims/lis/learner',
			'student'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

	/**
	 * @return bool
	 */
	function isContextInstructor()
	{
		$validRoles = array(
			'instructor',
			'urn:lti:role:ims/lis/instructor'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

	/**
	 * @return bool
	 */
	function isContextContentDeveloper()
	{
		$validRoles = array(
			'contentdeveloper',
			'urn:lti:role:ims/lis/contentdeveloper'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

	/**
	 * @return bool
	 */
	function isContextAdmin()
	{
		$validRoles = array(
			'administrator',
			'urn:lti:role:ims/lis/administrator'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

	/**
	 * @return bool
	 */
	function isTeachingAssistant()
	{
		$validRoles = array(
			'teachingassistant',
			'urn:lti:role:ims/lis/teachingassistant'
		);
		$validRolesFound = array_intersect($validRoles, $this->rolesLowerCase);
		return count($validRolesFound) > 0;
	}

}

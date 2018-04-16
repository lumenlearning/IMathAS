<?php

namespace OHM;

/**
 * Class LumenistrationInstitution Represents institution data returned from the Lumenistration API.
 * @package OHM
 */
class LumenistrationInstitution
{

	private $id; // string (UUID)
	private $name; // string
	private $externalIds; // an array of strings (UUIDs)
	private $bookstoreInformation; // string (raw message or html for the user)
	private $bookstoreUrl; // string
	private $schoolLogoUrl; // string

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getExternalIds()
	{
		return $this->externalIds;
	}

	/**
	 * @param mixed $externalIds
	 */
	public function setExternalIds($externalIds)
	{
		$this->externalIds = $externalIds;
	}

	/**
	 * A message or raw html to be displated to the user.
	 *
	 * @return string
	 */
	public function getBookstoreInformation()
	{
		return $this->bookstoreInformation;
	}

	/**
	 * A message or raw html to be displated to the user.
	 *
	 * @param string $bookstoreInformation
	 */
	public function setBookstoreInformation($bookstoreInformation)
	{
		$this->bookstoreInformation = $bookstoreInformation;
	}

	/**
	 * @return string
	 */
	public function getBookstoreUrl()
	{
		return $this->bookstoreUrl;
	}

	/**
	 * @param string $bookstoreUrl
	 */
	public function setBookstoreUrl($bookstoreUrl)
	{
		$this->bookstoreUrl = $bookstoreUrl;
	}

	/**
	 * @return mixed
	 */
	public function getSchoolLogoUrl()
	{
		return $this->schoolLogoUrl;
	}

	/**
	 * @param mixed $schoolLogoUrl
	 */
	public function setSchoolLogoUrl($schoolLogoUrl)
	{
		$this->schoolLogoUrl = $schoolLogoUrl;
	}

}

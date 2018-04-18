<?php

namespace OHM\Tests\Api;

use ApiTester;

class BaseCestCase
{

	private $loggedIn = false;

	/**
	 * @param ApiTester $I
	 */
	protected function userLogin($I)
	{
		$I->amOnPage('/../../../index.php');
		$I->fillField('username', 'root');
		$I->fillField('password', 'root');
		$I->click('Login');
		$I->see('Welcome to Lumen OHM');

		$this->loggedIn = true;
	}

	/**
	 * @param ApiTester $I
	 */
	protected function userLogout($I)
	{
		if (!$this->loggedIn) {
			return;
		}

		$I->amOnPage('/../../../index.php');
		$I->click(['link' => 'Log Out']);
		$I->see('Request an instructor account');

		$this->loggedIn = false;
	}
}

<?php

namespace OHM\Tests\Api;

use ApiTester;
use Codeception\Util\HttpCode;

class BaseCestCase
{

	private $loggedIn = false;

	/**
	 * @param ApiTester $I
	 */
	protected function userLogin($I)
	{
		$I->amOnPage('/../../../index.php');
		$I->see('Request an instructor account');
		$I->fillField('username', 'root');
		$I->fillField('password', 'root');
		$I->click('Login');
		$I->seeResponseCodeIs(HttpCode::OK);
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
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->see('Request an instructor account');

		$this->loggedIn = false;
	}
}

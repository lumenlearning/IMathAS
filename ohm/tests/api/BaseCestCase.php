<?php

namespace OHM\Tests\Api;

use ApiTester;
use Codeception\Util\HttpCode;

class BaseCestCase
{

	private $loggedIn = false;

	protected function apiAuthenticated($I)
	{
		// DON'T COMMIT REAL JWTs INTO THE REPO!
		// (this one is 'jwt_secret_goes_here' signed)
		$I->amBearerAuthenticated('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpc3MiOiJvaG0tYXBpIiwiYXVkIjoibG9jYWwgZGV2IHRlc3RpbmciLCJpYXQiOjE1Mjc2NDE1MzR9.1IUZD7I2svvldltJ_uKf_ZVQdLjNQWuci9GqmvX0Ngrn7Nz-rq8rEM0OVc9jVi2IUf21DsVM_4IbFxGanP0OUA');
	}

	protected function notApiAuthenticated($I)
	{
		$I->amBearerAuthenticated(null);
	}

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

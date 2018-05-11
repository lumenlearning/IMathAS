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
		$I->amBearerAuthenticated('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MjYwNjI1NDQsImF1ZCI6Im9obSJ9.NrRB5XdXNe8txsd4rYrfxTx4EW3vOaw4IwanV_TBK7naNRsEfEWdgJT2I_buKz5e1rj769ghabLwUAx99sBZMQ');
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

<?php

namespace OHM\Tests\Api;

use ApiTester;
use Codeception\Util\HttpCode;

class UserControllerCest extends BaseCestCase
{
	/**
	 * @param ApiTester $I
	 */
	public function _before($I)
	{
		$this->userLogin($I);
	}

	/**
	 * @param ApiTester $I
	 */
	public function _after($I)
	{
		$this->userLogout($I);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findAll($I)
	{
		$I->sendGet('/v1/users');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$[*].SID');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'SID' => 'string',
			'rights' => 'integer', // integer (as string on PHP 5)
			'FirstName' => 'string',
			'LastName' => 'string',
			'email' => 'string',
			'lastaccess' => 'integer', // integer (as string on PHP 5)
			'groupid' => 'integer', // integer (as string on PHP 5)
			'msgnotify' => 'integer', // tinyint(1) (as string on PHP 5)
			'qrightsdef' => 'integer', // tinyint(1) (as string on PHP 5)
			'deflib' => 'integer', // integer (as string on PHP 5)
			'usedeflib' => 'integer', // tinyint(1) (as string on PHP 5)
			'homelayout' => 'string',
			'hasuserimg' => 'integer', // tinyint(1) (as string on PHP 5)
			'remoteaccess' => 'string',
			'theme' => 'string',
			'listperpage' => 'integer', // integer (as string on PHP 5)
			'hideonpostswidget' => 'string',
			'specialrights' => 'integer', // integer (as string on PHP 5)
			'FCMtoken' => 'string',
			'jsondata' => 'string',
			'created_at' => 'integer|null', // integer (as string on PHP 5)
			'forcepwreset' => 'integer', // integer (as string on PHP 5)
		], '$[*]');
	}
}

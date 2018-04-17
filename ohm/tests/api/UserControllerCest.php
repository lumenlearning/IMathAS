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
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseJsonMatchesJsonPath('$[*].SID');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'SID' => 'string',
			'rights' => 'integer|string', // integer (as string on PHP 5)
			'FirstName' => 'string',
			'LastName' => 'string',
			'email' => 'string',
			'lastaccess' => 'integer|string', // integer (as string on PHP 5)
			'groupid' => 'integer|string', // integer (as string on PHP 5)
			'msgnotify' => 'integer|string', // tinyint(1) (as string on PHP 5)
			'qrightsdef' => 'integer|string', // tinyint(1) (as string on PHP 5)
			'deflib' => 'integer|string', // integer (as string on PHP 5)
			'usedeflib' => 'integer|string', // tinyint(1) (as string on PHP 5)
			'homelayout' => 'string|string',
			'hasuserimg' => 'integer|string', // tinyint(1) (as string on PHP 5)
			'remoteaccess' => 'string|string',
			'theme' => 'string',
			'listperpage' => 'integer|string', // integer (as string on PHP 5)
			'hideonpostswidget' => 'string',
			'specialrights' => 'integer|string', // integer (as string on PHP 5)
			'FCMtoken' => 'string',
			'jsondata' => 'string',
			'created_at' => 'integer|null|string', // integer (as string on PHP 5)
			'forcepwreset' => 'integer|string', // integer (as string on PHP 5)
		], '$[*]');
	}
}

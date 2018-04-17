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
			'rights' => 'string', // integer (as string on PHP 5)
			'FirstName' => 'string',
			'LastName' => 'string',
			'email' => 'string',
			'lastaccess' => 'string', // integer (as string on PHP 5)
			'groupid' => 'string', // integer (as string on PHP 5)
			'msgnotify' => 'string', // tinyint(1) (as string on PHP 5)
			'qrightsdef' => 'string', // tinyint(1) (as string on PHP 5)
			'deflib' => 'string', // integer (as string on PHP 5)
			'usedeflib' => 'string', // tinyint(1) (as string on PHP 5)
			'homelayout' => 'string',
			'hasuserimg' => 'string', // tinyint(1) (as string on PHP 5)
			'remoteaccess' => 'string',
			'theme' => 'string',
			'listperpage' => 'string', // integer (as string on PHP 5)
			'hideonpostswidget' => 'string',
			'specialrights' => 'string', // integer (as string on PHP 5)
			'FCMtoken' => 'string',
			'jsondata' => 'string',
			'created_at' => 'string|null', // integer (as string on PHP 5)
			'forcepwreset' => 'string', // integer (as string on PHP 5)
		], '$[*]');
	}
}

<?php

namespace OHM\Tests\Api;

use ApiTester;
use Codeception\Util\HttpCode;
use Faker\Factory as FakerFactory;

/**
 * Class GroupControllerCest
 *
 * These tests depend on a freshly seeded database with the correct User and
 * Group IDs. ("composer seed-ohm")
 *
 * @package OHM\Tests\Api
 * @covers GroupController
 */
class LtiCredentialControllerCest extends BaseCestCase
{
	/**
	 * @param ApiTester $I
	 */
	public function _before($I)
	{
		$this->apiAuthenticated($I);
	}

	/**
	 * @param ApiTester $I
	 */
	public function _after($I)
	{
		$this->notApiAuthenticated($I);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findAllWithoutAuth($I)
	{
		$this->notApiAuthenticated($I);
		$I->sendGet('/v1/groups/1/lti_credentials');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findAll($I)
	{
		$I->sendGet('/v1/groups/1/lti_credentials');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseJsonMatchesJsonPath('$[*].id');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'domain' => 'string',
			'key' => 'string',
			'can_create_instructors' => 'boolean',
			'group_id' => 'integer',
			'created_at' => 'integer',
		], '$[*]');
	}

	/**
	 * @param ApiTester $I
	 */
	public function findOne_ById($I)
	{
		$I->sendGet('/v1/groups/1/lti_credentials/2');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'domain' => 'string',
			'key' => 'string',
			'can_create_instructors' => 'boolean',
			'group_id' => 'integer',
			'created_at' => 'integer',
		]);
	}
}

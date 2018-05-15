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
	public function findOne($I)
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

	/**
	 * @param ApiTester $I
	 */
	public function findOne_Missing($I)
	{
		$I->sendGet('/v1/groups/1/lti_credentials/857472947');
		$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
	}

	/**
	 * @param ApiTester $I
	 */
	public function createUpdateDelete($I)
	{
		/*
		 * create
		 */
		$I->sendPost('/v1/groups/1/lti_credentials', [
			'domain' => 'test84341.example.com',
			'key' => 'test84341',
			'secret' => 'T3s+84341',
			'can_create_instructors' => true,
			'group_id' => 1,
		]);
		$I->seeResponseCodeIs(201);
		$created = json_decode($I->grabResponse(), true);

		$I->assertEquals('test84341.example.com', $created['domain']);
		$I->assertEquals('test84341', $created['key']);
		$I->assertTrue($created['can_create_instructors']);
		$I->assertEquals(1, $created['group_id']);
		$I->assertFalse(isset($created['secret']));
		$I->assertFalse(isset($created['password']));

		/*
		 * update
		 */
		$I->sendPut('/v1/groups/1/lti_credentials/' . $created['id'], [
			'domain' => 'test84341-new.example.com',
			'key' => 'test84341-new',
			'secret' => 'T3s+84341-new',
			'can_create_instructors' => false,
			'group_id' => 0,
		]);
		$I->seeResponseCodeIs(200);
		$updated = json_decode($I->grabResponse(), true);

		$I->assertEquals('test84341-new.example.com', $updated['domain']);
		$I->assertEquals('test84341-new', $updated['key']);
		$I->assertFalse($updated['can_create_instructors']);
		$I->assertEquals(0, $updated['group_id']);
		$I->assertFalse(isset($updated['secret']));
		$I->assertFalse(isset($updated['password']));

		/*
		 * delete
		 */
		$I->sendDelete('/v1/groups/1/lti_credentials/' . $created['id']);
		$I->seeResponseCodeIs(204);

		$I->sendGet('/v1/groups/1/lti_credentials/' . $created['id']);
		$I->seeResponseCodeIs(404);
	}

	/**
	 * @param ApiTester $I
	 */
	public function create_withNulls($I)
	{
		$I->sendPost('/v1/groups/1/lti_credentials', [
			'domain' => 'test84341.example.com',
			'key' => 'test84341',
			// missing "secret" key/value
			'can_create_instructors' => true,
			'group_id' => 1,
		]);
		$I->seeResponseCodeIs(400);
		$response = json_decode($I->grabResponse(), true);

		$I->assertEquals('Null values are not permitted.',
			$response['errors'][0]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function create_existingKey($I)
	{
		$I->sendPost('/v1/groups/1/lti_credentials', [
			'domain' => 'test84341.example.com',
			'key' => 'ltiKeyOne',
			'secret' => 'T3s+84341-new',
			'can_create_instructors' => true,
			'group_id' => 1,
		]);
		$I->seeResponseCodeIs(409);
		$response = json_decode($I->grabResponse(), true);

		$I->assertEquals('The specified LTI key already exists.',
			$response['errors'][0]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function update_nonExistent($I)
	{
		$I->sendPut('/v1/groups/1/lti_credentials/57823845', []);
		$I->seeResponseCodeIs(404);
	}

	/**
	 * @param ApiTester $I
	 */
	public function update_withNulls($I)
	{
		$I->sendPut('/v1/groups/1/lti_credentials/2', [
			'domain' => 'test84341.example.com',
			'key' => 'ltiKeyOne',
			'secret' => 'T3s+84341-new',
			// missing "can_create_instructors" key/value
			'group_id' => 1,
		]);
		$I->seeResponseCodeIs(400);
		$response = json_decode($I->grabResponse(), true);

		$I->assertEquals('Null values are not permitted.',
			$response['errors'][0]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function delete_nonExistent($I)
	{
		$I->sendDelete('/v1/groups/1/lti_credentials/57823845');
		$I->seeResponseCodeIs(204);
	}
}

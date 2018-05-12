<?php

namespace OHM\Tests\Api;

use ApiTester;
use Codeception\Util\HttpCode;
use Faker\Factory as FakerFactory;

/**
 * Class GroupControllerCest
 * @package OHM\Tests\Api
 * @covers GroupController
 */
class GroupControllerCest extends BaseCestCase
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
		$I->sendGet('/v1/groups');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findAll($I)
	{
		$I->sendGet('/v1/groups');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseJsonMatchesJsonPath('$[*].id');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'grouptype' => 'integer',
			'name' => 'string',
			'parent' => 'integer',
			'student_pay_enabled' => 'integer',
			'lumen_guid' => 'string|null',
			'created_at' => 'string|null',
		], '$[*]');
	}

	/**
	 * @param ApiTester $I
	 */
	public function findOne_ById($I)
	{
		$I->sendGet('/v1/groups/1');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$I->seeResponseMatchesJsonType([
			'id' => 'integer',
			'grouptype' => 'integer',
			'name' => 'string',
			'parent' => 'integer',
			'student_pay_enabled' => 'integer',
			'lumen_guid' => 'string|null',
			'created_at' => 'string|null',
		]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findOne_ByLumenGuid($I)
	{
		$faker = FakerFactory::create();
		$uuid = $faker->uuid;
		$name = 'Codeception Test Group - DELETE ME';

		/*
		 * create group
		 */
		$I->sendPost('/v1/groups', [
			'grouptype' => 1,
			'name' => $name,
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $uuid,
		]);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');

		/*
		 * find by Lumen guid
		 */
		$I->sendGet('/v1/groups/' . $uuid);
		$I->seeResponseCodeIs(200);
		$group = json_decode($I->grabResponse(), true);
		$I->assertEquals($group['name'], $name);

		/*
		 * cleanup
		 */
		$I->sendDelete('/v1/groups/' . $group['id']);
		$I->seeResponseCodeIs(204);
	}

	/**
	 * @param ApiTester $I
	 */
	public function findByName($I)
	{
		$faker = FakerFactory::create();
		$name = 'Codeception Test Group - DELETE ME';

		/*
		 * create groups to search for
		 */

		// group 1
		$I->sendPost('/v1/groups', [
			'grouptype' => 1,
			'name' => $name . ' 1',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $faker->uuid,
		]);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$wantGroup1 = json_decode($I->grabResponse(), true);

		// group 2
		$I->sendPost('/v1/groups', [
			'grouptype' => 1,
			'name' => $name . ' 2',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $faker->uuid,
		]);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$wantGroup2 = json_decode($I->grabResponse(), true);

		// group 3
		$I->sendPost('/v1/groups', [
			'grouptype' => 1,
			'name' => 'Codecept Test Group - DELETE ME',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $faker->uuid,
		]);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$badGroup = json_decode($I->grabResponse(), true);

		/*
		 * find groups by name
		 */
		$I->sendGet('/v1/groups?name_filter=Codeception');
		$I->seeResponseCodeIs(200);
		$results = json_decode($I->grabResponse(), true);

		$I->assertCount(2, $results);
		$I->assertContains('Codeception', $results[0]['name']);
		$I->assertContains('Codeception', $results[1]['name']);
		$I->assertNotContains('Codecept Test', $results[0]['name']);
		$I->assertNotContains('Codecept Test', $results[1]['name']);

		/*
		 * cleanup
		 */
		$I->sendDelete('/v1/groups/' . $wantGroup1['id']);
		$I->seeResponseCodeIs(204);
		$I->sendDelete('/v1/groups/' . $wantGroup2['id']);
		$I->seeResponseCodeIs(204);
		$I->sendDelete('/v1/groups/' . $badGroup['id']);
		$I->seeResponseCodeIs(204);
	}

	/**
	 * @param ApiTester $I
	 */
	public function createUpdateDelete($I)
	{
		$faker = FakerFactory::create();
		$uuid = $faker->uuid;
		$name = 'Codeception Test Group - DELETE ME';

		/*
		 * create
		 */
		$I->sendPost('/v1/groups', [
			'grouptype' => 1,
			'name' => $name,
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $uuid,
		]);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$group = json_decode($I->grabResponse(), true);
		$I->assertEquals($group['name'], $name);

		$groupId = $group['id'];

		/*
		 * update
		 */
		$I->sendPut('/v1/groups/' . $groupId, [
			'grouptype' => 1,
			'name' => $name . ' :)',
			'parent' => 0,
			'student_pay_enabled' => 1,
			'lumen_guid' => $uuid,
		]);
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.id');

		$I->sendGet('/v1/groups/' . $groupId);
		$group = json_decode($I->grabResponse(), true);
		$I->assertEquals($group['name'], $name . ' :)');

		/*
		 * delete
		 */
		$I->sendDelete('/v1/groups/' . $groupId);
		$I->seeResponseCodeIs(204);

		$I->sendGet('/v1/groups/' . $groupId);
		$I->seeResponseCodeIs(404);
	}

	/**
	 * @param ApiTester $I
	 */
	public function createDuplicate($I)
	{
		$faker = FakerFactory::create();
		$name = 'Codeception Test Group - DELETE ME';

		$groupData = [
			'grouptype' => 1,
			'name' => $name,
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => $faker->uuid,
		];

		/*
		 * create first group
		 */
		$I->sendPost('/v1/groups', $groupData);
		$I->seeResponseCodeIs(HttpCode::CREATED);
		$I->seeResponseJsonMatchesJsonPath('$.id');
		$group = json_decode($I->grabResponse(), true);
		$I->assertEquals($group['name'], $name);

		$groupId = $group['id'];

		/*
		 * create second identical group (this should fail)
		 */
		$I->sendPost('/v1/groups', $groupData);
		$I->seeResponseCodeIs(HttpCode::CONFLICT);

		/*
		 * cleanup
		 */
		$I->sendDelete('/v1/groups/' . $groupId);
		$I->seeResponseCodeIs(204);
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteMissing($I)
	{
		$I->sendDelete('/v1/groups/' . time());
		$I->seeResponseCodeIs(204);
	}

	/**
	 * @param ApiTester $I
	 */
	public function updateMissing_ById($I)
	{
		$faker = FakerFactory::create();

		$I->sendPut('/v1/groups/' . time(), [
			'grouptype' => 1,
			'name' => 'Codeception Test Group - DELETE ME',
			'parent' => 0,
			'student_pay_enabled' => 1,
			'lumen_guid' => $faker->uuid,
		]);
		$I->seeResponseCodeIs(404);
	}

	/**
	 * @param ApiTester $I
	 */
	public function updateMissing_ByGuid($I)
	{
		$faker = FakerFactory::create();
		$guid = $faker->uuid;

		$I->sendPut('/v1/groups/' . $guid, [
			'grouptype' => 1,
			'name' => 'Codeception Test Group - DELETE ME',
			'parent' => 0,
			'student_pay_enabled' => 1,
			'lumen_guid' => $guid,
		]);
		$I->seeResponseCodeIs(404);
	}
}

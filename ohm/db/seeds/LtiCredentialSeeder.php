<?php


use Phinx\Seed\AbstractSeed;
use Faker\Factory AS FakerFactory;

class LtiCredentialSeeder extends AbstractSeed
{
	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * http://docs.phinx.org/en/latest/seeding.html
	 */
	public function run(): void
	{
		// https://github.com/fzaninotto/Faker
		$faker = FakerFactory::create();

		$row = $this->fetchRow(sprintf('SELECT * FROM imas_groups WHERE name = "%s"',
			'Hogwarts School of Witchcraft and Wizardry'));
		$groupId = $row['id'];

		$data = [];
		$data[] = [
			'SID' => 'ltiKeyOne',
			'password' => 'SupahSekr1t',
			'rights' => 11,
			'FirstName' => 'one.example.com',
			'LastName' => 'LTIcredential',
			'email' => 'one@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'mfa' => 'dd57b5ece110bf997d744ce726fba0116d5b',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'SID' => 'ltiKeyTwo',
			'password' => 'DaPassw0rd',
			'rights' => 11,
			'FirstName' => 'two.example.com',
			'LastName' => 'LTIcredential',
			'email' => 'two@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'mfa' => '7a052b86970c79b079c5621e189519c8f79c',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'SID' => 'ltiKeyThree',
			'password' => 'Secur3P1z2a',
			'rights' => 76,
			'FirstName' => 'three.example.com',
			'LastName' => 'LTIcredential',
			'email' => 'three@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'mfa' => 'd632e8a97d080d38d04e4d04cd5c73031c38',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];

		$this->table('imas_users')->insert($data)->save();
	}
}

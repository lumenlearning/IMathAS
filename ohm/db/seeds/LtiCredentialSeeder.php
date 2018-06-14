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
	public function run()
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
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];

		$this->insert('imas_users', $data);
	}
}

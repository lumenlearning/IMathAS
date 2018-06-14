<?php


use Phinx\Seed\AbstractSeed;
use Faker\Factory AS FakerFactory;

class UserSeeder extends AbstractSeed
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
			'SID' => 'ssnape',
			'password' => '$2y$10$QX31UP3FpxzrdkryXCSPReHogJuMZ34jD6Kt3l5Xk2/lantIsbneK', // ssnape
			'rights' => 40,
			'FirstName' => 'Severus',
			'LastName' => 'Snape',
			'email' => 'ssnape@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'SID' => 'dmalfoy',
			'password' => '$2y$10$AjDYWV9WojO8ZutvJ5tSbuuVD3wE4V2gkH9ElNAqzPBJVSZ8U7aJO', // dmalfoy
			'rights' => 10,
			'FirstName' => 'Draco',
			'LastName' => 'Malfoy',
			'email' => 'dmalfoy@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'SID' => 'afilch',
			'password' => '$2y$10$oTxtJjMNIyPj8wCVC4Mb4ewZkVVIqy8ObVnNKkMGCgK3VpdVRNWCi', // afilch
			'rights' => 12,
			'FirstName' => 'Argus',
			'LastName' => 'Filch',
			'email' => 'afilch@example.com',
			'groupid' => $groupId,
			'jsondata' => '',
			'hideonpostswidget' => '',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];

		// More users at Hogwarts
		for ($i = 0; $i < 7; $i++) {
			$username = $faker->unique()->userName;
			$data[] = [
				'SID' => $username,
				'password' => password_hash($username, PASSWORD_BCRYPT),
				'rights' => 10,
				'FirstName' => $faker->firstName,
				'LastName' => $faker->lastName,
				'email' => $faker->email,
				'groupid' => $groupId,
				'jsondata' => '',
				'hideonpostswidget' => '',
				'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
			];
		}

		// More users not in a group
		for ($i = 0; $i < 40; $i++) {
			$username = $faker->unique()->userName;
			$data[] = [
				'SID' => $username,
				'password' => password_hash($username, PASSWORD_BCRYPT),
				'rights' => 10,
				'FirstName' => $faker->firstName,
				'LastName' => $faker->lastName,
				'email' => $faker->email,
				'groupid' => 0, // "default" group
				'jsondata' => '',
				'hideonpostswidget' => '',
				'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
			];
		}

		$this->insert('imas_users', $data);
	}
}

<?php


use Phinx\Seed\AbstractSeed;
use Faker\Factory AS FakerFactory;

class GroupSeeder extends AbstractSeed
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

		$data = [];
		$data[] = [
			'grouptype' => 0,
			'name' => 'Hogwarts School of Witchcraft and Wizardry',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => '757b6819-a8e4-4313-91be-7cf782c93b36',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'grouptype' => 0,
			'name' => 'Isothermal Community College',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => '44ffd636-ce21-48e3-a533-e5cf0b4f8bdc',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];
		$data[] = [
			'grouptype' => 0,
			'name' => 'Everett Community College',
			'parent' => 0,
			'student_pay_enabled' => 0,
			'lumen_guid' => 'a6561bed-1d5e-4344-a073-0c60fd671420',
			'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
		];

		for ($i = 0; $i < 47; $i++) {
			$data[] = [
				'grouptype' => 0,
				'name' => $faker->unique()->company,
				'parent' => 0,
				'student_pay_enabled' => 0,
				'lumen_guid' => $faker->uuid,
				'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
			];
		}

		$this->table('imas_groups')->insert($data)->save();
	}
}

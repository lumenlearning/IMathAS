<?php


use Phinx\Migration\AbstractMigration;

class CreateApiConsumersTable extends AbstractMigration
{
	/**
	 * Migrate Up.
	 */
	public function up()
	{
		echo "* Creating table: ohm_api_consumers\n";
		$table = $this->table('ohm_api_consumers', ['id' => false,
			'primary_key' => 'id']);
		$table
			->addColumn('id', 'string', ['limit' => 36])
			->addColumn('name', 'string', ['limit' => 63])
			->addColumn('description', 'string', ['limit' => 60])
			->addColumn('created_at', 'integer', ['null' => false])
			->addColumn('updated_at', 'integer', ['null' => true])
			->save();
	}

	/**
	 * Migrate Down.
	 */
	public function down()
	{
		echo "* Dropping table: ohm_api_consumers\n";
		$this->dropTable('ohm_api_consumers');
	}
}

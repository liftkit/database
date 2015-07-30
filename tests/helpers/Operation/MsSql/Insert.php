<?php

	namespace LiftKit\Tests\Helpers\Database\Operation\MsSql;


	/**
	 * Disables foreign key checks temporarily.
	 */
	class Insert extends \PHPUnit_Extensions_Database_Operation_Insert
	{
		public function execute(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
		{
			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("Alter Table $tableName NOCHECK Constraint All");
			}

			parent::execute($connection, $dataSet);

			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("Alter Table $tableName CHECK Constraint All");
			}
		}
	}
<?php

	namespace LiftKit\Tests\Helpers\Database\Operation\MsSql;


	/**
	 * Disables foreign key checks temporarily.
	 */
	class Truncate extends \PHPUnit_Extensions_Database_Operation_Truncate
	{
		public function execute(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
		{
			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("Alter Table $tableName NOCHECK Constraint All");
			}

			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("Delete From $tableName");
			}

			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("DBCC CHECKIDENT ($tableName, RESEED, 0)");
			}

			foreach ($dataSet->getTableNames() as $tableName) {
				$connection->getConnection()->query("Alter Table $tableName CHECK Constraint All");
			}
		}
	}
<?php

	namespace LiftKit\Tests\Helpers\Database\Operation;


	/**
	 * Disables foreign key checks temporarily.
	 */
	class Truncate extends \PHPUnit_Extensions_Database_Operation_Truncate
	{
		public function execute(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
		{
			$connection->getConnection()->query("SET foreign_key_checks = 0");
			parent::execute($connection, $dataSet);
			$connection->getConnection()->query("SET foreign_key_checks = 1");
		}
	}
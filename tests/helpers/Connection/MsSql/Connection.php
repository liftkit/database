<?php


	namespace LiftKit\Tests\Helpers\Database\Connection\MsSql;


	use PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection;


	class Connection extends PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	{


		/**
		 * Disables primary keys if connection does not allow setting them otherwise
		 *
		 * @param string $tableName
		 */
		public function disablePrimaryKeys($tableName)
		{
			$this->getConnection()->query("SET IDENTITY_INSERT $tableName ON");
		}

		/**
		 * Reenables primary keys after they have been disabled
		 *
		 * @param string $tableName
		 */
		public function enablePrimaryKeys($tableName)
		{
			$this->getConnection()->query("SET IDENTITY_INSERT $tableName OFF");
		}
	}
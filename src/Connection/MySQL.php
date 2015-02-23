<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\Database\Query\Identifier\MySQL as Identifier;


	/**
	 * Class MySQL
	 *
	 * @package LiftKit\Database\Connection
	 */
	class MySQL extends Connection
	{


		/**
		 * @param string $host
		 * @param string $user
		 * @param string $password
		 * @param string $schema
		 *
		 * @return string
		 */
		protected function buildConnectionString ($host, $user, $password, $schema)
		{
			return 'mysql:dbname=' . $schema . ';host=' . $host;
		}


		/**
		 * @param string $identifier
		 *
		 * @return Identifier
		 */
		public function quoteIdentifier ($identifier)
		{
			return new Identifier($identifier);
		}


		/**
		 * primaryKey function.
		 *
		 * @access public
		 *
		 * @param string $tableName
		 *
		 * @return string
		 */
		public function primaryKey ($tableName)
		{
			if (!isset($this->primaryKeys[$tableName])) {
				$sql = "SHOW INDEX FROM " . $this->quoteIdentifier($tableName) . " WHERE Key_name = 'PRIMARY'";

				$keyResult                      = $this->query($sql);
				$key                            = $keyResult->fetchRow();
				$this->primaryKeys[$tableName]  = $key['Column_name'];
			}

			return $this->primaryKeys[$tableName];
		}
	}



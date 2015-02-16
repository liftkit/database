<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\DatabaseQueries\Base as DatabaseQuery;
	use LiftKit\DatabaseQueryConditions\Base as DatabaseQueryCondition;
	use LiftKit\DatabaseTables\Base as DatabaseTable;
	use LiftKit\DatabaseResults\Base as DatabaseResult;
	use LiftKit\Exceptions\Database as DatabaseException;
	use LiftKit\DatabaseCaches\Base as DatabaseCache;


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
		 * quote function.
		 *
		 * @access public
		 *
		 * @param string $string
		 *
		 * @return string
		 */

		public function quote ($string)
		{
			if (!is_null($string)) {
				return "'".$this->escape($string)."'";
			} else {
				return 'NULL';
			}
		}


		/**
		 * escape function.
		 *
		 * @access public
		 *
		 * @param string $string
		 *
		 * @return string
		 */

		public function escape ($string)
		{
			return $this->database->real_escape_string($string);
		}


		/**
		 * @param string $identifier
		 *
		 * @return string
		 */
		public function quoteIdentifier ($identifier)
		{
			if (!preg_match('#^([A-Za-z0-9\._])+$#', $identifier)) {
				return $identifier;
			}

			$split = explode('.', $identifier);

			foreach ($split as &$segment) {
				$segment = '`'.$segment.'`';
			}

			return implode('.', $split);
		}


		/**
		 * lastId function.
		 *
		 * @access public
		 * @return int
		 */

		public function insertId ()
		{
			return $this->database->insert_id;
		}


		/**
		 * primaryKey function.
		 *
		 * @access public
		 *
		 * @param string $table_name
		 *
		 * @return string
		 */

		public function primaryKey ($table_name)
		{
			if (!isset($this->primaryKeys[$table_name])) {
				$sql = "SHOW INDEX FROM `".$table_name."` WHERE Key_name = 'PRIMARY'";

				$key_result                       = $this->query($sql);
				$key                              = $key_result->fetchRow();
				$this->primaryKeys[$table_name] = $key['Column_name'];
			}

			return $this->primaryKeys[$table_name];
		}
	}



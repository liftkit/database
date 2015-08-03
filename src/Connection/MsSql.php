<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\Database\Query\Identifier\MsSql as Identifier;

	use LiftKit\Database\Query\MsSql as DatabaseQuery;
	use LiftKit\Database\Query\Condition\Condition as DatabaseQueryCondition;


	/**
	 * Class MySQL
	 *
	 * @package LiftKit\Database\Connection
	 */
	class MsSql extends Connection
	{


		/**
		 * @return DatabaseQuery
		 */
		public function createQuery ()
		{
			return new DatabaseQuery($this);
		}


		/**
		 * @return DatabaseQueryCondition
		 */
		public function createCondition ()
		{
			return new DatabaseQueryCondition($this);
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


		public function quote ($value)
		{
			if (is_null($value)) {
				return 'NULL';
			} else {
				return "'" . str_replace("'", "''", $value) . "'";
			}
		}


		public function insertId ($name = null)
		{
			$sql = "SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS int)";

			return $this->query($sql)->fetchField();
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
			if (! isset($this->primaryKeys[$tableName])) {
				$sql = "SELECT *
					    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
					        JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu ON tc.CONSTRAINT_NAME = ccu.Constraint_name
					    WHERE tc.CONSTRAINT_TYPE = 'Primary Key'
					        AND tc.TABLE_NAME = " . $this->quote($tableName);

				$keyResult                      = $this->query($sql);
				$key                            = $keyResult->fetchRow();
				$this->primaryKeys[$tableName]  = $key['COLUMN_NAME'];
			}

			return $this->primaryKeys[$tableName];
		}


		/**
		 * getFields function.
		 *
		 * @access public
		 *
		 * @param string $table
		 *
		 * @return array
		 */
		public function getFields ($table)
		{
			$sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = " . $this->quote($table);

			return $this->query($sql)->fetchColumn('COLUMN_NAME');
		}
	}



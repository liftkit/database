<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\Database\Query\Identifier\MsSql as Identifier;


	/**
	 * Class MySQL
	 *
	 * @package LiftKit\Database\Connection
	 */
	class MsSql extends Connection
	{


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
	}



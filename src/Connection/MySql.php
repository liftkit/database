<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\Database\Query\Identifier\MySql as Identifier;
	use LiftKit\Database\Query\MySql as DatabaseQuery;
	use LiftKit\Database\Query\Condition\Condition as DatabaseQueryCondition;


	/**
	 * Class MySQL
	 *
	 * @package LiftKit\Database\Connection
	 */
	class MySql extends Connection
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
				$sql = "SHOW INDEX FROM " . $this->quoteIdentifier($tableName) . " WHERE Key_name = 'PRIMARY'";

				$keyResult                      = $this->query($sql);
				$key                            = $keyResult->fetchRow();
				$this->primaryKeys[$tableName]  = $key['Column_name'];
			}

			return $this->primaryKeys[$tableName];
		}
	}



<?php


	namespace LiftKit\Database\Schema;

	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Table\Table;
	use LiftKit\Database\Schema\Exception\NonexistentTable as NonexistentTableException;


	class Schema
	{
		/**
		 * @var Connection
		 */
		protected $connection;


		/**
		 * @var Table[]
		 */
		protected $tables = array();


		public function __construct (Connection $connection)
		{
			$this->connection = $connection;
		}


		public function defineTable ($tableName)
		{
			$this->tables[$tableName] = new Table($this->connection, $this, $tableName);

			return $this->tables[$tableName];
		}


		public function getTable ($tableName, $force = false)
		{
			if (isset($this->tables[$tableName])) {
				return $this->tables[$tableName];
			} else {
				if ($force) {
					return $this->defineTable($tableName);
				} else {
					throw new NonexistentTableException('No such table ' . var_export($tableName, true));
				}
			}
		}
	}
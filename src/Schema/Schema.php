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
			$this->tables[$tableName] = new Table($this->connection, $tableName);
			
			return $this->tables[$tableName];
		}
		
		
		public function getTable ($tableName)
		{
			if (isset($this->tables[$tableName])) {
				return $this->tables[$tableName];
			} else {
				throw new NonexistentTableException('No such table ' . var_export($tableName, true));
			}
		}
	}
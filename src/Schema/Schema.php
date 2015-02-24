<?php


	namespace LiftKit\Database\Schema;
	
	use LiftKit\Database\Connection;
	use LiftKit\Database\Schema\Table;
	
	
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
			return $this->tables[$tableName];
		}
	}
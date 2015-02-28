<?php


	namespace LiftKit\Database\Query\Join;
	
	use LiftKit\Database\Connection\Connection;
	
	
	class Join
	{
		/**
		 * @var string
		 */
		protected $table;
		
		
		/**
		 * @var Connection
		 */
		protected $database;
		
		
		/**
		 * @var string
		 */
		protected $type;
		
		
		/**
		 * @var string
		 */
		protected $relation;
		
		
		/**
		 * @var string
		 */
		protected $condition;
		
		
		/**
		 * @param string $table
		 * @param string $type
		 * @param string $relation
		 * @param string $condition
		 */
		public function __construct (Connection $database, $type, $table, $relation, $condition)
		{
			$this->database  = $database;
			$this->table     = $table;
			$this->type      = $type;
			$this->relation  = $relation;
			$this->condition = $condition;
		}
		
		
		public function getTable ()
		{
			return $this->table;
		}
		
		
		public function toString ()
		{
			return $this->type . ' ' . $this->database->quoteIdentifier($this->table) . ' ' . $this->relation . ' (' . $this->condition . ')';
		}
		
		
		public function __toString ()
		{
			return $this->toString();
		}
	}
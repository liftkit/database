<?php


	namespace LiftKit\Database\Schema\Table\Relation;
	
	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Table\Table;
	
	
	abstract class Relation
	{
		/**
		 * @var Table
		 */
		protected $table;
		
		
		/**
		 * @var Table
		 */
		protected $relatedTable;
		
		
		/**
		 * @var string
		 */
		protected $key;
		
		
		/**
		 * @var string
		 */
		protected $relatedKey;
		
		
		public function __construct (Table $table, Table $relatedTable)
		{
			$this->table        = $table;
			$this->relatedTable = $relatedTable;
		}
		
		
		public function getTable ()
		{
			return $this->table->getTable();
		}
		
		
		public function getKey ()
		{
			return $this->key;
		}
		
		
		public function getRelatedTable ()
		{
			return $this->relatedTable->getTable();
		}
		
		
		public function getRelatedKey ()
		{
			return $this->relatedKey;
		}
	}
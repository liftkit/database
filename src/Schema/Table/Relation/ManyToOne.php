<?php


	namespace LiftKit\Database\Schema\Table\Relation;
	
	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Table\Table;
	
	
	class ManyToOne extends Relation
	{
	
	
		public function __construct (Table $table, Table $relatedTable, $key = null, $relatedKey = null)
		{
			parent::__construct($table, $relatedTable);

			if (is_null($relatedKey)) {
				$relatedKey = $this->relatedTable->primaryKey();
			}
			
			if (is_null($key)) {
				$key = $relatedKey;
			}
			
			$this->relatedKey = $relatedKey;
			$this->key        = $key;
		}
	}
<?php


	namespace LiftKit\Database\Schema\Table\Relation;

	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Table\Table;


	class OneToMany extends Relation
	{


		public function __construct (Table $table, Table $relatedTable, $key = null, $relatedKey = null)
		{
			parent::__construct($table, $relatedTable);

			if (is_null($key)) {
				$key = $this->table->getPrimaryKey();
			}

			if (is_null($relatedKey)) {
				$relatedKey = $key;
			}

			$this->relatedKey = $relatedKey;
			$this->key        = $key;
		}
	}
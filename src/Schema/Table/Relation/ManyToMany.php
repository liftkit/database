<?php


	namespace LiftKit\Database\Schema\Table\Relation;

	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Table\Table;


	class ManyToMany extends Relation
	{
		/**
		 * @var Table
		 */
		protected $relationalTable;


		public function __construct (Table $table, Table $relatedTable, Table $relationalTable, $key = null, $relatedKey = null)
		{
			parent::__construct($table, $relatedTable);

			if (is_null($key)) {
				$key = $this->table->getPrimaryKey();
			}

			if (is_null($relatedKey)) {
				$relatedKey = $this->relatedTable->getPrimaryKey();
			}

			$this->relationalTable = $relationalTable;
			$this->relatedKey      = $relatedKey;
			$this->key             = $key;
		}


		public function getRelationalTable ()
		{
			return $this->relationalTable->getTable();
		}
	}
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
		 * @var string
		 */
		protected $alias;


		/**
		 * @param string $table
		 * @param string $type
		 * @param string $relation
		 * @param string $condition
		 */
		public function __construct (Connection $database, $type, $table, $relation, $condition, $alias = null)
		{
			$this->database  = $database;
			$this->table     = $table;
			$this->type      = $type;
			$this->relation  = $relation;
			$this->condition = $condition;
			$this->alias     = $alias;
		}


		public function getTable ()
		{
			return $this->table;
		}


		public function toString ()
		{
			$string = $this->type . ' ' . $this->database->quoteIdentifier($this->table) . ' ';

			if ($this->alias) {
				$string .= 'AS ' . $this->database->quoteIdentifier($this->alias) . ' ';
			}

			 $string .= $this->relation . ' (' . $this->condition . ')';

			return $string;
		}


		public function __toString ()
		{
			return $this->toString();
		}
	}
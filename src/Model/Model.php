<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Models;


	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Query\Query;
	use LiftKit\Database\Query\Condition\Condition;
	use LiftKit\Database\Table\Table;


	/**
	 * Class Model
	 *
	 * @package LiftKit\Models
	 */
	abstract class Model
	{

		/**
		 * @var Connection
		 */
		protected $database;


		/**
		 * @param Connection $database
		 */
		public function __construct (Connection $database)
		{
			$this->database = $database;
		}


		/**
		 * @return Query
		 */
		protected function createQuery ()
		{
			return $this->database->createQuery();
		}


		/**
		 * @return Query
		 */
		protected function toQuery ($query)
		{
			return $this->database->toQuery($query);
		}


		/**
		 * @return Condition
		 */
		protected function createCondition ()
		{
			return $this->database->createCondition();
		}


		/**
		 * @return Table
		 */
		protected function createTable ($table)
		{
			return $this->database->createTable($table);
		}


		/**
		 * @param $identifier
		 *
		 * @return string
		 */
		protected function quoteIdentifier ($identifier)
		{
			return $this->database->quoteIdentifier($identifier);
		}
	}


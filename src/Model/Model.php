<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Models;


	use LiftKit\Database\Connection\Connection;
	use LiftKit\Database\Schema\Schema;
	use LiftKit\Database\Query\Query;
	use LiftKit\Database\Query\Condition\Condition;


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
		 * @var Schema
		 */
		protected $schema;


		/**
		 * @param Connection $database
		 */
		public function __construct (Connection $database, Schema $schema = null)
		{
			$this->database = $database;
			$this->schema = $schema;
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
		 * @param $identifier
		 *
		 * @return string
		 */
		protected function quoteIdentifier ($identifier)
		{
			return $this->database->quoteIdentifier($identifier);
		}
	}


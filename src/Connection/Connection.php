<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Connection;

	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Condition\Condition as DatabaseQueryCondition;
	use LiftKit\Database\Table\Table as DatabaseTable;
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Exception\Database as DatabaseException;
	use LiftKit\Database\Cache\Cache as DatabaseCache;

	use PDO;
	use PDOException;
	use PDOStatement;


	abstract class Connection
	{
		protected $database;
		protected $primaryKeys = array();
		protected $lastQuery;
		protected $loader;
		protected $cachedQueries = array();
		protected $placeholder = '?';

		/**
		 * @var DatabaseCache
		 */
		protected $cache;


		/**
		 * __construct function.
		 *
		 * @access public
		 *
		 * @param string $host
		 * @param string $user
		 * @param string $pass
		 * @param string $name
		 *
		 * @return void
		 */

		public function __construct (Container $loader, DatabaseCache $cache, $host, $user, $password, $schema)
		{
			$this->loader = $loader;
			$this->cache = $cache;

			try {
				$connectionString = $this->buildConnectionString($host, $user, $password, $schema);
				$this->database = new PDO($connectionString, $user, $password);
			} catch (PDOException $e) {
				throw new DatabaseException('Database connection error: ' . $e->getMessage());
			}
		}


		/**
		 * query function.
		 *
		 * @access public
		 *
		 * @param string $sql
		 * @param array  $data (default: array())
		 *
		 * @return DatabaseResult
		 */

		public function query ($sql, $data = array(), $entity = null)
		{
			if ($sql instanceof DatabaseQuery) {
				$sql = $sql->getRaw();
			}

			$statement = $this->database->prepare($sql);
			$result = $statement->execute($data);

			$this->lastQuery = $statement->queryString;
			$this->cachedQueries[$sql]++;

			if (! $result) {
				throw new DatabaseException($this->lastQuery . ': ' . $this->database->errorCode() . ' "' . $this->database->errorInfo() . '"');
			}else {
				return $this->createResult($statement, $entity);
			}
		}


		/**
		 * @param mixed $query
		 *
		 * @return DatabaseQuery
		 * @throws DatabaseException
		 */
		public function toQuery ($query)
		{
			if (is_object($query)) {
				$query = clone $query;
			}

			if ($query instanceof DatabaseQuery) {
				return $query;

			} else if ($query instanceof DatabaseQueryCondition) {
				return $this->createQuery()->where($query);

			} else if (is_null($query)) {
				return $this->createQuery();

			} else {
				throw new DatabaseException('Invalid query/condition type.');
			}
		}


		/**
		 * @return DatabaseQuery
		 */
		public function createQuery ()
		{
			return new DatabaseQuery($this);
		}


		/**
		 * @return DatabaseQueryCondition
		 */
		public function createCondition ()
		{
			return new DatabaseQueryCondition($this);
		}


		/**
		 * @param string $table
		 *
		 * @return DatabaseTable
		 */
		public function createTable ($table)
		{
			return new DatabaseTable($this, $table);
		}


		/**
		 * @param PDOStatement $result
		 * @param null|string  $entity
		 *
		 * @return DatabaseResult
		 */
		public function createResult (PDOStatement $result, $entity = null)
		{
			if ($result->columnCount()) {
				return new DatabaseResult($result, $this->loader, $entity);
			} else {
				return true;
			}
		}


		/**
		 * @param string $identifier
		 *
		 * @return string
		 */
		abstract public function quoteIdentifier ($identifier);


		/**
		 * quote function.
		 *
		 * @access public
		 *
		 * @param string $string
		 *
		 * @return string
		 */
		public function quote ($string)
		{
			return $this->database->quote($string);
		}


		/**
		 * lastId function.
		 *
		 * @access public
		 * @return int
		 */
		public function insertId ()
		{
			return $this->database->lastInsertId();
		}


		/**
		 * primaryKey function.
		 *
		 * @access public
		 *
		 * @param string $tableName
		 *
		 * @return string
		 */
		abstract public function primaryKey ($tableName);


		/**
		 * lastQuery function.
		 *
		 * @access public
		 * @return string
		 */
		public function lastQuery ()
		{
			return $this->lastQuery;
		}


		/**
		 * getFields function.
		 *
		 * @access public
		 *
		 * @param string $table
		 *
		 * @return array
		 */
		public function getFields ($table)
		{
			$sql = "SHOW COLUMNS FROM `".$table."`";

			$fields = $this->query($sql);
			$returnFields = array();

			foreach ($fields as $field) {
				$returnFields[] = $field['Field'];
			}

			return $returnFields;
		}


		/**
		 * @return bool
		 */
		public function startTransaction ()
		{
			return $this->database->beginTransaction();
		}


		/**
		 * @return bool
		 */
		public function rollback ()
		{
			return $this->database->rollBack();
		}


		/**
		 * @return bool
		 */
		public function commit ()
		{
			return $this->database->commit();
		}


		/**
		 * @return DatabaseCache
		 */
		public function getCache ()
		{
			return $this->cache;
		}


		/**
		 * @param string $host
		 * @param string $user
		 * @param string $password
		 * @param string $schema
		 *
		 * @return mixed
		 */
		abstract protected function buildConnectionString ($host, $user, $password, $schema);
	}



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
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Exception\Database as DatabaseException;
	use LiftKit\Database\Cache\Cache as DatabaseCache;
	use LiftKit\Database\Query\Raw\Raw;

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

		/**
		 * @var DatabaseCache
		 */
		protected $cache;


		/**
		 * @param string $identifier
		 *
		 * @return string
		 */
		abstract public function quoteIdentifier ($identifier);


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
		 * @param string $host
		 * @param string $user
		 * @param string $password
		 * @param string $schema
		 *
		 * @return mixed
		 */
		abstract protected function buildConnectionString ($host, $user, $password, $schema);


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
		 * @todo implement cache
		 *
		 * @param string $query
		 * @param array  $data (default: array())
		 * @param string $entity (default: null)
		 *
		 * @return DatabaseResult
		 */

		public function query ($query, $data = array(), $entity = null)
		{
			if ($this->cache->isCached($query)) {
				return $this->cache->getCachedResult($query);
			} else {
				$statement = $this->database->prepare((string) $query);
				$result = $statement->execute($data);

				$this->lastQuery = $statement->queryString;

				if (! $result) {
					throw new DatabaseException($this->database->errorCode() . ': ' . $this->lastQuery);
				}

				$databaseResult = $this->createResult($statement, $entity);

				if ($query instanceof DatabaseQuery && $databaseResult instanceof DatabaseResult) {
					$this->cache->cacheQuery($query, $databaseResult);
				}

				if ($query instanceof DatabaseQuery) {
					$this->cache->refreshCache($query);
				}

				return $this->insertId() ?: $databaseResult;
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
		 * @return Raw
		 */
		public function createRaw ($sql)
		{
			return new Raw($sql);
		}


		/**
		 * @return DatabaseCache
		 */
		public function getCache ()
		{
			return $this->cache;
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
		 * quote function.
		 *
		 * @access public
		 *
		 * @param mixed $string
		 *
		 * @return string
		 */
		public function quote ($string)
		{
			if (is_null($string)) {
				return 'NULL';
			} else {
				return $this->database->quote($string);
			}
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
			$sql = "SHOW COLUMNS FROM " . $this->quoteIdentifier($table);

			return $this->query($sql);
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
		 * @param PDOStatement $result
		 * @param null|string  $entity
		 *
		 * @return DatabaseResult
		 */
		protected function createResult (PDOStatement $result, $entity = null)
		{
			if ($result->columnCount()) {
				return new DatabaseResult($result, $this->loader, $entity);
			} else {
				return true;
			}
		}
	}



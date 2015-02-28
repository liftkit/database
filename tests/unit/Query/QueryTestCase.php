<?php

	namespace LiftKit\Tests\Unit\Database\Query;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Query\Condition\Condition;
	use LiftKit\Database\Query\Query;


	abstract class QueryTestCase extends DefaultTestCase
	{
		/**
		 * @var Container
		 */
		protected $container;


		/**
		 * @var Cache
		 */
		protected $cache;


		/**
		 * @var Connection
		 */
		protected $connection;


		/**
		 * @var Condition
		 */
		protected $condition;


		/**
		 * @var Query
		 */
		protected $query;



		public function afterConnection ()
		{
			$this->cache = new Cache;
			$this->container = new Container;

			$this->connection = new Connection(
				$this->container,
				$this->cache,
				self::$pdo
			);

			$this->query = $this->connection->createQuery();
			$this->condition = $this->connection->createCondition();
		}


		protected function normalizeSql ($sql)
		{
			$sql = (string) $sql;
			$sql = preg_replace('#\s+#', ' ', $sql);
			$sql = trim($sql);
			$sql = preg_replace('#\(\s+#', '(', $sql);
			$sql = preg_replace('#\s+\)#', ')', $sql);

			return $sql;
		}

	}
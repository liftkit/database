<?php

	namespace LiftKit\Tests\Unit\Database\Query;

	use LiftKit\Tests\Unit\Database\SimpleTestCase;
	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Query\Condition\Condition;


	abstract class QueryTestCase extends SimpleTestCase
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



		public function afterConnection ()
		{
			$this->cache = new Cache;
			$this->container = new Container;

			$this->connection = new Connection(
				$this->container,
				$this->cache,
				$this->host,
				$this->user,
				$this->password,
				$this->schema
			);

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
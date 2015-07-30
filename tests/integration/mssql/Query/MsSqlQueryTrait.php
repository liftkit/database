<?php

	namespace LiftKit\Tests\Integration\MsSql\Database\Query;

	use LiftKit\Database\Connection\MsSql as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;


	trait MsSqlQueryTrait
	{



		public function afterConnection ()
		{
			$this->cache = new Cache;
			$this->container = new Container;

			if (! $this->connection) {
				$this->connection = new Connection(
					$this->container,
					$this->cache,
					self::$pdo
				);
			}

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
			$sql = preg_replace('#"(.+?)"#', '$1', $sql);

			return $sql;
		}

	}
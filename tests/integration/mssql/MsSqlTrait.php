<?php

	namespace LiftKit\Tests\Integration\MsSql\Database;

	use LiftKit\Database\Connection\MsSql as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;

	use LiftKit\Tests\Helpers\Database\Operation\MsSql\Truncate as TruncateOperation;
	use LiftKit\Tests\Helpers\Database\Operation\MsSql\Insert as InsertOperation;

	use LiftKit\Tests\Helpers\Database\Connection\MsSql\Connection as MsSqlConnection;

	use PDO;


	trait MsSqlTrait
	{


		public function getSetUpOperation()
		{
			$cascadeTruncates = true; // If you want cascading truncates, false otherwise. If unsure choose false.

			return new \PHPUnit_Extensions_Database_Operation_Composite(array(
				new TruncateOperation($cascadeTruncates),
				new InsertOperation()
			));
		}



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
		}


		public function getConnection ()
		{
			$this->dsn = $GLOBALS['DB_DSN_MSSQL'];
			$this->host = $GLOBALS['DB_HOST_MSSQL'];
			$this->user = $GLOBALS['DB_USER_MSSQL'];
			$this->password = $GLOBALS['DB_PASSWORD_MSSQL'];
			$this->schema = $GLOBALS['DB_SCHEMA_MSSQL'];

			if ($this->conn === null) {
				if (self::$pdo == null) {
					self::$pdo = new PDO($this->dsn, $this->user, $this->password);
				}

				$this->conn = new MsSqlConnection(self::$pdo, $this->schema);
			}

			$this->afterConnection();

			return $this->conn;
		}
	}
<?php


	namespace LiftKit\Tests\Database;

	use PHPUnit_Extensions_Database_TestCase;
	use PDO;


	abstract class TestCase extends PHPUnit_Extensions_Database_TestCase
	{
		// only instantiate pdo once for test clean-up/fixture load
		static protected $pdo = null;

		// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
		protected $conn = null;


		protected $dsn;
		protected $host;
		protected $user;
		protected $password;
		protected $schema;


		public function getConnection ()
		{
			$this->dsn = $GLOBALS['DB_DSN'];
			$this->host = $GLOBALS['DB_HOST'];
			$this->user = $GLOBALS['DB_USER'];
			$this->password = $GLOBALS['DB_PASSWORD'];
			$this->schema = $GLOBALS['DB_SCHEMA'];

			if ($this->conn === null) {
				if (self::$pdo == null) {
					self::$pdo = new PDO($this->dsn, $this->user, $this->password);
				}

				$this->conn = $this->createDefaultDBConnection(self::$pdo, $this->schema);
			}

			$this->afterConnection();

			return $this->conn;
		}


		public function getDataSet ()
		{
			return $this->createMySQLXMLDataSet(__DIR__ . '/../datasets/simple.xml');
		}


		abstract public function afterConnection ();
	}
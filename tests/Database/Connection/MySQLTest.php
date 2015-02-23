<?php


	namespace LiftKit\Tests\Database\Connection;

	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;

	use LiftKit\Tests\Database\TestCase;


	class MySQLTest extends TestCase
	{
		/**
		 * @var Connection
		 */
		protected $connection;

		/**
		 * @var Cache
		 */
		protected $cache;


		/**
		 * @var Container
		 */
		protected $container;


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
		}


		public function testConnectsToDatabase ()
		{
			$this->assertTrue($this->connection instanceof Connection);
		}
	}
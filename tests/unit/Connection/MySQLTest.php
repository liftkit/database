<?php


	namespace LiftKit\Tests\Unit\Database\Connection;

	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Query\Identifier\Identifier;


	class MySQLTest extends ConnectionTest
	{

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


		public function testQuoteIdentifier ()
		{
			$this->assertTrue($this->connection->quoteIdentifier('test') instanceof Identifier);
			$this->assertEquals($this->connection->quoteIdentifier('test'), '`test`');
		}


		public function testPrimaryKey ()
		{
			$this->assertEquals($this->connection->primaryKey('parents'), 'parent_id');
		}
	}
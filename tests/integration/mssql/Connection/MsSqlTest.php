<?php


	namespace LiftKit\Tests\Integration\MsSql\Database\Connection;

	use LiftKit\Tests\Integration\MsSql\Database\MsSqlTrait;
	use LiftKit\Tests\Unit\Database\Connection\ConnectionTest;
	use LiftKit\Database\Query\Identifier\Identifier;


	class MsSqlTest extends ConnectionTest
	{
		use MsSqlTrait;


		public function testQuoteIdentifier ()
		{
			$this->assertTrue($this->connection->quoteIdentifier('test') instanceof Identifier);
			$this->assertEquals($this->connection->quoteIdentifier('test'), '"test"');
		}


		public function testPrimaryKey ()
		{
			$this->assertEquals($this->connection->primaryKey('parents'), 'parent_id');
		}


		public function testQuote ()
		{
			$this->assertEquals("'test''s'", $this->connection->quote("test's"));
		}


		public function testInsertId ()
		{
			$this->connection->query("INSERT INTO parents(parent_name) VALUES('george')");

			$this->assertTrue(
				is_numeric($this->connection->insertId())
			);
		}
	}
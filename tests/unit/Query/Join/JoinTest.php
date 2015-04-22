<?php


	namespace LiftKit\Test\Unit\Database\Query\Join;

	use LiftKit\Database\Query\Join\Join;
	use LiftKit\Tests\Unit\Database\Query\QueryTestCase;


	class JoinTest extends QueryTestCase
	{



		public function testJoin ()
		{
			$join = new Join(
				$this->connection,
				'LEFT JOIN',
				'parents',
				'ON',
				'parents.parent_id = children.parent_id'
			);

			$this->assertEquals(
				(string) $join,
				'LEFT JOIN ' . $this->connection->quoteIdentifier('parents') . ' ON (parents.parent_id = children.parent_id)'
			);

			$this->assertEquals(
				$join->getTable(),
				'parents'
			);
		}



		public function testJoinWithAlias ()
		{
			$join = new Join(
				$this->connection,
				'LEFT JOIN',
				'parents',
				'ON',
				'parents.parent_id = children.parent_id',
				'alias'
			);

			$this->assertEquals(
				(string) $join,
				'LEFT JOIN ' . $this->connection->quoteIdentifier('parents') . ' AS `alias` ON (parents.parent_id = children.parent_id)'
			);

			$this->assertEquals(
				$join->getTable(),
				'parents'
			);
		}
	}
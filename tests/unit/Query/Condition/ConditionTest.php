<?php

	namespace LiftKit\Tests\Unit\Database\Condition;

	use LiftKit\Tests\Unit\Database\Query\QueryTestCase;


	class ConditionTest extends QueryTestCase
	{


		public function testEmptyCondition ()
		{
			$this->assertEquals($this->condition, '');
		}


		public function testEqual ()
		{
			$this->condition->equal('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2'"
			);
		}


		public function testNotEqual ()
		{
			$this->condition->notEqual('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` <> '2'"
			);
		}


		public function testOrEqual ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orEqual('value', 'test');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `value` = 'test'"
			);
		}


		public function testOrNotEqual ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orNotEqual('value', 'test');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `value` <> 'test'"
			);
		}


		public function testLessThan ()
		{
			$this->condition->lessThan('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` < '2'"
			);
		}


		public function testOrLessThan ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orLessThan('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` < '2'"
			);
		}


		public function testLessThanOrEqual ()
		{
			$this->condition->lessThanOrEqual('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` <= '2'"
			);
		}


		public function testGreaterThan ()
		{
			$this->condition->greaterThan('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` > '2'"
			);
		}


		public function testOrGreaterThan ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orGreaterThan('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` > '2'"
			);
		}


		public function testGreaterThanOrEqual ()
		{
			$this->condition->greaterThanOrEqual('id', 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` >= '2'"
			);
		}


		public function testWithRaw ()
		{
			$this->condition->equal($this->connection->createRaw('id'), 2);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"id = '2'"
			);
		}


		public function testWithIdentifier ()
		{
			$this->condition->equal('id1', $this->connection->quoteIdentifier('id2'));

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id1` = `id2`"
			);
		}


		public function testAnd ()
		{
			$this->condition->equal('id', 2);
			$this->condition->equal('value', 'test');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' AND `value` = 'test'"
			);
		}


		public function testIn ()
		{
			$this->condition->in('id', array(1, 2, 3, 4, 5, 6));

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` IN ('1', '2', '3', '4', '5', '6')"
			);
		}


		public function testNotIn ()
		{
			$this->condition->notIn('id', array(1, 2, 3, 4, 5, 6));

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` NOT IN ('1', '2', '3', '4', '5', '6')"
			);
		}


		public function testOrIn ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orIn('id', array(1, 2, 3, 4, 5, 6));

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` IN ('1', '2', '3', '4', '5', '6')"
			);
		}


		public function testOrNotIn ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orNotIn('id', array(1, 2, 3, 4, 5, 6));

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` NOT IN ('1', '2', '3', '4', '5', '6')"
			);
		}


		public function testIsNull ()
		{
			$this->condition->is('id', null);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` IS NULL"
			);
		}


		public function testIsTrue ()
		{
			$this->condition->is('id', true);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` IS TRUE"
			);
		}


		public function testIsFalse ()
		{
			$this->condition->is('id', false);

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` IS FALSE"
			);
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testIsInvalid ()
		{
			$this->condition->is('id', 'null');
		}


		public function testLike ()
		{
			$this->condition->like('id', '%1%');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` LIKE '%1%'"
			);
		}


		public function testNotLike ()
		{
			$this->condition->notLike('id', '%1%');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` NOT LIKE '%1%'"
			);
		}


		public function testOrLike ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orLike('id', '%1%');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` LIKE '%1%'"
			);
		}


		public function testOrNotLike ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orNotLike('id', '%1%');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` NOT LIKE '%1%'"
			);
		}


		public function testRegexp ()
		{
			$this->condition->regexp('id', '.*');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` REGEXP '.*'"
			);
		}


		public function testNotRegexp ()
		{
			$this->condition->notRegexp('id', '.*');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` NOT REGEXP '.*'"
			);
		}


		public function testOrRegexp ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orRegexp('id', '.*');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` REGEXP '.*'"
			);
		}


		public function testOrNotRegexp ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orNotRegexp('id', '.*');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR `id` NOT REGEXP '.*'"
			);
		}


		public function testCondition ()
		{
			$outerCondition = $this->connection->createCondition()->equal('id1', 2);
			$innerCondition = $this->connection->createCondition()->equal('id2', 3)->orEqual('id2', 4);

			$outerCondition->condition($innerCondition);

			$this->assertEquals(
				$this->normalizeSql($outerCondition),
				"`id1` = '2' AND (`id2` = '3' OR `id2` = '4')"
			);
		}


		public function testNotCondition ()
		{
			$outerCondition = $this->connection->createCondition()->equal('id1', 2);
			$innerCondition = $this->connection->createCondition()->equal('id2', 3)->orEqual('id2', 4);

			$outerCondition->notCondition($innerCondition);

			$this->assertEquals(
				$this->normalizeSql($outerCondition),
				"`id1` = '2' AND NOT (`id2` = '3' OR `id2` = '4')"
			);
		}


		public function testOrCondition ()
		{
			$outerCondition = $this->connection->createCondition()->equal('id1', 2);
			$innerCondition = $this->connection->createCondition()->equal('id2', 3)->orEqual('id2', 4);

			$outerCondition->orCondition($innerCondition);

			$this->assertEquals(
				$this->normalizeSql($outerCondition),
				"`id1` = '2' OR (`id2` = '3' OR `id2` = '4')"
			);
		}


		public function testOrNotCondition ()
		{
			$outerCondition = $this->connection->createCondition()->equal('id1', 2);
			$innerCondition = $this->connection->createCondition()->equal('id2', 3)->orEqual('id2', 4);

			$outerCondition->orNotCondition($innerCondition);

			$this->assertEquals(
				$this->normalizeSql($outerCondition),
				"`id1` = '2' OR NOT (`id2` = '3' OR `id2` = '4')"
			);
		}


		public function testRaw ()
		{
			$this->condition->raw('id = 1');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"(id = 1)"
			);
		}


		public function testNotRaw ()
		{
			$this->condition->equal('id', 2);
			$this->condition->notRaw('id = 1');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' AND NOT (id = 1)"
			);
		}


		public function testOrRaw ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orRaw('id = 1');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR (id = 1)"
			);
		}


		public function testOrNotRaw ()
		{
			$this->condition->equal('id', 2);
			$this->condition->orNotRaw('id = 1');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				"`id` = '2' OR NOT (id = 1)"
			);
		}


		public function testSearch ()
		{
			$this->condition->search(array('field1', 'field2', 'field3'), 'three different terms');

			$this->assertEquals(
				$this->normalizeSql($this->condition),
				$this->normalizeSql(
					"(
						(
							`field1` REGEXP '[[:<:]]three'
							OR `field2` REGEXP '[[:<:]]three'
							OR `field3` REGEXP '[[:<:]]three'
						) AND (
							`field1` REGEXP '[[:<:]]different'
							OR `field2` REGEXP '[[:<:]]different'
							OR `field3` REGEXP '[[:<:]]different'
						) AND (
							`field1` REGEXP '[[:<:]]terms'
							OR `field2` REGEXP '[[:<:]]terms'
							OR `field3` REGEXP '[[:<:]]terms'
						)
					)"
				)
			);
		}



	}
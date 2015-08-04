<?php


	namespace LiftKit\Tests\Integration\MsSql\Database\Query;

	use LiftKit\Tests\Integration\MsSql\Database\MsSqlTrait;

	use LiftKit\Tests\Unit\Database\Query\QueryTest;


	class MsSqlTest extends QueryTest
	{
		use MsSqlTrait, MsSqlQueryTrait {
			MsSqlQueryTrait::afterConnection insteadof MsSqlTrait;
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testInsertIgnore ()
		{
			$this->query->insertIgnore()
				->into('children')
				->set(
					array(
						'child_id' => 6,
						'child_name' => 'child6',
					)
				);
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testInsertUpdate ()
		{
			$this->query->insertUpdate()
				->into('children')
				->set(
					array(
						'child_id' => 6,
						'child_name' => 'child6',
					)
				);
		}


		public function testDelete ()
		{
			$this->query->delete()
				->from('children')
				->where(
					$this->condition->equal('child_id', 2)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					DELETE
					FROM children
					WHERE (child_id = '2')
				")
			);

			$this->query->execute();
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testLeftJoinUsing ()
		{
			$this->query->leftJoinUsing('table', 'field');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testLeftJoinUsingAlias ()
		{
			$this->query->leftJoinUsing('table', 'field', 'test');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testRightJoinUsing ()
		{
			$this->query->rightJoinUsing('table', 'field');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testRightJoinUsingAlias ()
		{
			$this->query->leftJoinUsing('table', 'field', 'test');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testInnerJoinUsing ()
		{
			$this->query->innerJoinUsing('table', 'field');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testInnerJoinUsingAlias ()
		{
			$this->query->leftJoinUsing('table', 'field', 'test');
		}


		public function testLimit ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->limit(1);

			$this->assertEquals(
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN (
						SELECT children.child_id AS LK_ROW_ID, ROW_NUMBER() OVER (ORDER BY children.child_id ASC) AS LK_ROW_NUMBER
						FROM children
					) AS LK_ROWS ON (LK_ROWS.LK_ROW_ID = children.child_id)
					WHERE ((LK_ROWS.LK_ROW_NUMBER >= '1')
						AND (LK_ROWS.LK_ROW_NUMBER <= '1'))
				"),
				$this->normalizeSql($this->query)
			);

			$this->query->execute();
		}


		public function testStartLimit ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->start(1)
				->limit(10);

			$this->assertEquals(
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN (
						SELECT children.child_id AS LK_ROW_ID, ROW_NUMBER() OVER (ORDER BY children.child_id ASC) AS LK_ROW_NUMBER
						FROM children
					) AS LK_ROWS ON (LK_ROWS.LK_ROW_ID = children.child_id)
					WHERE ((LK_ROWS.LK_ROW_NUMBER >= '2')
						AND (LK_ROWS.LK_ROW_NUMBER <= '11'))
				"),
				$this->normalizeSql($this->query)
			);

			$this->query->execute();
		}


		public function testComposeWith ()
		{
			$query = $this->query->select('child_id')
				->from('children')
				->whereEqual('child_name', 'child1')
				->havingEqual('children.parent_id', 1)
				->orderBy('child_id');

			$innerQuery = $this->connection->createQuery()
				->addField('children.parent_id')
				->leftJoinEqual('parents', 'parents.parent_id', 'children.parent_id')
				->whereEqual('child_id', 2)
				->havingEqual('child_id', 2)
				->groupBy('children.parent_id')
				->groupBy('child_id');

			$query->composeWith($innerQuery);

			$this->assertEquals(
				$this->normalizeSql($query),
				$this->normalizeSql("
					SELECT child_id, children.parent_id
					FROM children
					LEFT JOIN parents ON (parents.parent_id = children.parent_id)
					WHERE (child_name = 'child1') AND ((child_id = '2'))
					GROUP BY children.parent_id, child_id
					HAVING (children.parent_id = '1') AND ((child_id = '2'))
					ORDER BY child_id ASC
				")
			);

			$this->query->execute();
		}
	}
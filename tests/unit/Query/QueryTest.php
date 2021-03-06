<?php


	namespace LiftKit\Tests\Unit\Database\Query;

	use LiftKit\Database\Query\Query;


	class QueryTest extends QueryTestCase
	{


		public function testSelect ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
				")
			);

			$this->query->execute();
		}


		public function testAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children AS alias
				")
			);

			$this->query->execute();
		}


		public function testWhereCondition ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->condition->equal('child_id', 2)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = '2')
				")
			);

			$this->query->execute();
		}


		public function testWhereRaw ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->connection->createRaw('child_id = 2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = 2)
				")
			);

			$this->query->execute();
		}


		public function testWhereAnd ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->where(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = '2') AND (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testWhereNot ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->notWhere(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = '2') AND NOT (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testWhereOr ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->orWhere(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = '2') OR (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testWhereOrNot ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->where(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->orNotWhere(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					WHERE (child_id = '2') OR NOT (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testHaving ()
		{
			$this->query->select()
				->fields(array('child_id'))
				->from('children')
				->groupBy('child_id')
				->having(
					$this->connection->createCondition()->equal('child_id', 2)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id
					FROM children
					GROUP BY child_id
					HAVING (child_id = '2')
				")
			);

			$this->query->execute();
		}


		public function testHavingAnd ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->groupBy('child_id')
				->groupBy('child_name')
				->having(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->having(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					GROUP BY child_id, child_name
					HAVING (child_id = '2') AND (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testHavingNot ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->groupBy('child_id')
				->groupBy('child_name')
				->having(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->notHaving(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					GROUP BY child_id, child_name
					HAVING (child_id = '2') AND NOT (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testHavingOr ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->groupBy('child_id')
				->groupBy('child_name')
				->having(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->orHaving(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					GROUP BY child_id, child_name
					HAVING (child_id = '2') OR (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testHavingOrNot ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->groupBy('child_id')
				->groupBy('child_name')
				->having(
					$this->connection->createCondition()->equal('child_id', 2)
				)
				->orNotHaving(
					$this->connection->createCondition()->equal('child_name', 'child2')
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					GROUP BY child_id, child_name
					HAVING (child_id = '2') OR NOT (child_name = 'child2')
				")
			);

			$this->query->execute();
		}


		public function testLeftJoin ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('parents.parent_id')
					)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testLeftJoinAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('alias.parent_id')
					),
					'alias'
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testLeftJoinEqual ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoinEqual('parents', 'children.parent_id', 'parents.parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testLeftJoinEqualAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoinEqual('parents', 'children.parent_id', 'alias.parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testLeftJoinUsing ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoinUsing('parents', 'parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testLeftJoinUsingAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->leftJoinUsing('parents', 'parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LEFT JOIN parents AS alias USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoin ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('parents.parent_id')
					)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoinAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('alias.parent_id')
					),
					'alias'
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoinEqual ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoinEqual('parents', 'children.parent_id', 'parents.parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoinEqualAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoinEqual('parents', 'children.parent_id', 'alias.parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoinUsing ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoinUsing('parents', 'parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testRightJoinUsingAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoinUsing('parents', 'parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					RIGHT JOIN parents AS alias USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoin ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('parents.parent_id')
					)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoinAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoin(
					'parents',
					$this->condition->equal(
						'children.parent_id',
						$this->connection->quoteIdentifier('alias.parent_id')
					),
					'alias'
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoinEqual ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoinEqual('parents', 'children.parent_id', 'parents.parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents ON (children.parent_id = parents.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoinEqualAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoinEqual('parents', 'children.parent_id', 'alias.parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents AS alias ON (children.parent_id = alias.parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoinUsing ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoinUsing('parents', 'parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testInnerJoinUsingAlias ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->innerJoinUsing('parents', 'parent_id', 'alias');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					INNER JOIN parents AS alias USING (parent_id)
				")
			);

			$this->query->execute();
		}


		public function testGroupBy ()
		{
			$this->query->select()
				->fields(array('parent_id'))
				->from('children')
				->groupBy('parent_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT parent_id
					FROM children
					GROUP BY parent_id
				")
			);

			$this->query->execute();
		}


		public function testGroupByMultiple ()
		{
			$this->query->select()
				->fields(array('child_id', 'parent_id'))
				->from('children')
				->groupBy('parent_id')
				->groupBy('child_id');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, parent_id
					FROM children
					GROUP BY parent_id, child_id
				")
			);

			$this->query->execute();
		}


		public function testOrderBy ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->orderBy('parent_id', Query::QUERY_ORDER_ASC);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					ORDER BY parent_id ASC
				")
			);

			$this->query->execute();
		}


		public function testOrderByMultiple ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->orderBy('parent_id', Query::QUERY_ORDER_ASC)
				->orderBy('child_name', Query::QUERY_ORDER_DESC);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					ORDER BY parent_id ASC, child_name DESC
				")
			);

			$this->query->execute();
		}


		public function testLimit ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->limit(1);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LIMIT 0, 1
				")
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
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name
					FROM children
					LIMIT 1, 10
				")
			);

			$this->query->execute();
		}


		public function testAddField ()
		{
			$this->query->select()
				->addField('child_id')
				->addField('child_name', 'name')
				->from('children');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name AS name
					FROM children
				")
			);

			$this->query->execute();
		}


		public function testPrependField ()
		{
			$this->query->select()
				->addField('child_id')
				->prependField('child_name', 'name')
				->from('children');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_name AS name, child_id
					FROM children
				")
			);

			$this->query->execute();
		}


		public function testFieldsWithAlias ()
		{
			$this->query->select()
				->fields(
					array(
						'child_id',
						array('child_name', 'name')
					)
				)
				->from('children');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id, child_name AS name
					FROM children
				")
			);

			$this->query->execute();
		}


		public function testPrependFields ()
		{
			$this->query->select()
				->addField('child_id')
				->prependFields(array('child_name'))
				->from('children');

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_name, child_id
					FROM children
				")
			);

			$this->query->execute();
		}


		public function testInsert ()
		{
			$this->query->insert()
				->into('children')
				->set(
					array(
						'child_name' => 'child6',
					)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					INSERT INTO children (child_name)
					VALUES ('child6')
				")
			);

			$this->query->execute();
		}


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

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					INSERT IGNORE INTO children
						SET child_id = '6', child_name = 'child6'
				")
			);

			$this->query->execute();
		}


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

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					INSERT INTO children
						SET child_id = '6', child_name = 'child6'
					ON DUPLICATE KEY UPDATE child_id = '6', child_name = 'child6'
				")
			);

			$this->query->execute();
		}


		public function testUpdate ()
		{
			$this->query->update()
				->table('children')
				->set(
					array(
						'child_name' => 'child6',
					)
				)
				->where(
					$this->condition->equal('child_id', 2)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					UPDATE children
						SET child_name = 'child6'
					WHERE (child_id = '2')
				")
			);

			$this->query->execute();
		}


		public function testDelete ()
		{
			$this->query->delete('children.*')
				->from('children')
				->where(
					$this->condition->equal('child_id', 2)
				);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					DELETE children.*
					FROM children
					WHERE (child_id = '2')
				")
			);

			$this->query->execute();
		}


		public function testWhereMappedCondition ()
		{
			$this->query->select()
				->from('children')
				->whereEqual('child_id', 1);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT *
					FROM children
					WHERE (child_id = '1')
				")
			);

			$this->query->execute();
		}


		public function testHavingMappedCondition ()
		{
			$this->query->select('child_id')
				->from('children')
				->groupBy('child_id')
				->havingEqual('child_id', 1);

			$this->assertEquals(
				$this->normalizeSql($this->query),
				$this->normalizeSql("
					SELECT child_id
					FROM children
					GROUP BY child_id
					HAVING (child_id = '1')
				")
			);

			$this->query->execute();
		}


		public function testGetTable ()
		{
			$this->query->from('children');
			$this->assertEquals($this->query->getTable(), 'children');

			$this->query->into('parents');
			$this->assertEquals($this->query->getTable(), 'parents');

			$this->query->table('friends');
			$this->assertEquals($this->query->getTable(), 'friends');
		}


		public function testGetSetCache ()
		{
			$this->query->setCache(true);
			$this->assertEquals($this->query->isCached(), true);

			$this->query->setCache(false);
			$this->assertEquals($this->query->isCached(), false);
		}


		public function testGetInfo ()
		{
			$this->query->select()
				->fields(array('child_id', 'child_name'))
				->from('children')
				->rightJoinEqual('parents', 'parent.parent_id', 'children.parent_id');

			$this->assertEquals($this->query->getType(), Query::QUERY_TYPE_SELECT);
			$this->assertEquals($this->query->getTable(), 'children');
			$this->assertEquals(count($this->query->getJoins()), 1);
		}


		public function testComposeWith ()
		{
			$query = $this->query->select('child_id')
				->from('children')
				->whereEqual('child_name', 'child1')
				->havingEqual('parent_id', 1)
				->orderBy('child_id');

			$innerQuery = $this->connection->createQuery()
				->addField('child_name')
				->addField('children.parent_id')
				->leftJoinEqual('parents', 'parents.parent_id', 'children.parent_id')
				->whereEqual('child_id', 2)
				->havingEqual('child_id', 2)
				->groupBy('parent_id')
				->groupBy('child_id')
				->orderBy('parents.parent_id')
				->limit(1);

			$query->composeWith($innerQuery);

			$this->assertEquals(
				$this->normalizeSql($query),
				$this->normalizeSql("
					SELECT child_id, child_name, children.parent_id
					FROM children
					LEFT JOIN parents ON (parents.parent_id = children.parent_id)
					WHERE (child_name = 'child1') AND ((child_id = '2'))
					GROUP BY parent_id, child_id
					HAVING (parent_id = '1') AND ((child_id = '2'))
					ORDER BY child_id ASC, parents.parent_id ASC
					LIMIT 0, 1
				")
			);

			$this->query->execute();
		}
	}
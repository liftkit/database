<?php


	namespace LiftKit\Tests\Unit\Database\Schema\Table;

	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Schema\Table\Table;
	use LiftKit\Database\Query\Query;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use PDO;


	class TableTest extends DefaultTestCase
	{
		/**
		 * @var Container
		 */
		protected $container;


		/**
		 * @var Cache
		 */
		protected $cache;


		/**
		 * @var Connection
		 */
		protected $connection;


		/**
		 * @var Table
		 */
		protected $parentsTable;


		/**
		 * @var Table
		 */
		protected $childrenTable;


		/**
		 * @var Table
		 */
		protected $friendsTable;



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

			$this->parentsTable = new Table($this->connection, 'parents');
			$this->parentsTable->oneToMany('children')
				->manyToMany('friends', 'parent_friends');

			$this->childrenTable = new Table($this->connection, 'children');
			$this->childrenTable->manyToOne('parents');

			$this->friendsTable = new Table($this->connection, 'friends');
			$this->friendsTable->manyToMany('parents', 'parent_friends', 'parent_id', 'friend_id');
		}


		public function testGetRows ()
		{
			$this->assertResultEqualToQuery(
				$this->childrenTable->getRows(),
				"
					SELECT parents.*, children.*
					FROM children
					LEFT JOIN parents USING(parent_id)
				"
			);
		}


		public function testGetRowsWithQuery ()
		{
			$query = $this->connection->createQuery()
				->orderBy('child_id', Query::QUERY_ORDER_DESC);

			$this->assertResultEqualToQuery(
				$this->childrenTable->getRows($query),
				"
					SELECT parents.*, children.*
					FROM children
					LEFT JOIN parents USING(parent_id)
					ORDER BY child_id DESC
				"
			);
		}


		public function testGetRow ()
		{
			$row = $this->childrenTable->getRow(1);

			$sql = "SELECT parents.*, children.*
					FROM children
					LEFT JOIN parents ON children.parent_id = parents.parent_id
					WHERE child_id = 1";
			$result = self::$pdo->query($sql);

			$this->assertEquals(
				$row->toArray(),
				$result->fetch(PDO::FETCH_ASSOC)
			);
		}


		public function testGetRowByValue ()
		{
			$row = $this->childrenTable->getRowByValue('child_name', 'child1');

			$sql = "SELECT parents.*, children.*
					FROM children
					LEFT JOIN parents ON children.parent_id = parents.parent_id
					WHERE child_name = 'child1'
					LIMIT 1";
			$result = self::$pdo->query($sql);

			$this->assertEquals(
				$row->toArray(),
				$result->fetch(PDO::FETCH_ASSOC)
			);
		}


		public function testInsertRow ()
		{
			$sql = "SELECT * FROM children";
			$beforeCount = $this->createTableFromQuery($sql)->getRowCount();

			$insertData = array(
				'child_id' => '100',
				'child_name' => 'child100',
			);

			$this->childrenTable->insertRow($insertData);

			$afterCount = $this->createTableFromQuery($sql)->getRowCount();

			$this->assertEquals($afterCount - $beforeCount, 1);

			$child = $this->childrenTable->getRow(100);

			$this->assertCommonFieldsMatch($child->toArray(), $insertData);
		}


		public function testUpdateRow ()
		{
			$updateData = array(
				'child_id' => '1',
				'child_name' => 'new_child_name',
			);

			$this->childrenTable->updateRow($updateData);
			$child = $this->childrenTable->getRow('1');

			$this->assertCommonFieldsMatch($child->toArray(), $updateData);
		}


		public function testInsertUpdateRow ()
		{
			$updateData = array(
				'child_id' => '1',
				'child_name' => 'new_child_name',
			);

			$this->childrenTable->insertUpdateRow($updateData);
			$child = $this->childrenTable->getRow('1');

			$this->assertCommonFieldsMatch($child->toArray(), $updateData);
		}


		public function testDeleteRow ()
		{
			$child = $this->childrenTable->getRow(1);
			$this->assertNotNull($child);

			$beforeCount = $this->childrenTable->getRows()->count();
			$this->childrenTable->deleteRow(1);
			$afterCount = $this->childrenTable->getRows()->count();

			$this->assertEquals($beforeCount - $afterCount, 1);

			$child = $this->childrenTable->getRow(1);
			$this->assertNull($child);
		}


		public function testGetChildrenOneToMany ()
		{
			$this->assertResultEqualToQuery(
				$this->parentsTable->getChildren('children', 1),
				"
					SELECT children.*
					FROM children
					WHERE parent_id = 1
				"
			);
		}


		public function testGetChildrenManyToMany ()
		{
			$this->assertResultEqualToQuery(
				$this->parentsTable->getChildren('friends', 1),
				"
					SELECT *
					FROM parent_friends
					LEFT JOIN friends USING(friend_id)
					WHERE parent_friends.parent_id = 1
				"
			);
		}


		public function testGetChildOneToMany ()
		{
			$child = $this->childrenTable->getRow(1);
			$parentChild = $this->parentsTable->getChild('children', 1, 1);

			$this->assertCommonFieldsMatch($child->toArray(), $parentChild->toArray());
		}


		public function testGetChildrenInvalid ()
		{
			$this->setExpectedException('\LiftKit\Database\Schema\Table\Exception\Relation');

			$this->childrenTable->getChildren('parents', 1);
		}


		public function testInsertChildOneToMany ()
		{
			$childData = array(
				'child_name' => 'new_child_name',
			);

			$childId = $this->parentsTable->insertChild('children', 1, $childData);
			$childData['parent_id'] = 1;

			$child = $this->childrenTable->getRow($childId);

			$this->assertCommonFieldsMatch($child->toArray(), $childData);
		}


		public function testInsertChildManyToMany ()
		{
			$childData = array(
				'child_name' => 'new_child_name',
			);

			$childId = $this->parentsTable->insertChild('children', 1, $childData);
			$childData['parent_id'] = 1;

			$child = $this->parentsTable->getChild('children', 1, $childId);

			$this->assertCommonFieldsMatch($child->toArray(), $childData);
		}


		public function testAssignChildrenOneToMany ()
		{
			$originalChildIds = $this->parentsTable->getChildren('children', 2)->fetchColumn('child_id');

			$assignedChildIds = array(4, 5);
			$this->parentsTable->assignChildren('children', 2, $assignedChildIds);

			$newChildIds = $this->parentsTable->getChildren('children', 2)->fetchColumn('child_id');

			$this->assertEquals($assignedChildIds, $newChildIds);

			$unsetIds = array_diff($originalChildIds, $assignedChildIds);

			$nullIds = $this->childrenTable->getRows(
				$this->connection->createQuery()
					->whereIn('child_id', $unsetIds)
					->whereIs('children.parent_id', null)
			)->fetchColumn('child_id');

			$this->assertEquals($unsetIds, $nullIds);
		}


		public function testAssignChildrenOneToManyNonSubtractive ()
		{
			$originalChildIds = $this->parentsTable->getChildren('children', 2)->fetchColumn('child_id');

			$assignedChildIds = array(4, 5);
			$this->parentsTable->assignChildren('children', 2, $assignedChildIds, false);

			$newChildIds = $this->parentsTable->getChildren('children', 2)->fetchColumn('child_id');

			$combinedIds = array_unique(array_merge($originalChildIds, $assignedChildIds));
			sort($combinedIds);
			sort($newChildIds);

			$this->assertEquals($combinedIds, $newChildIds);
		}


		public function testAssignChildrenManyToMany ()
		{
			$this->parentsTable->getChildren('friends', 2)->fetchColumn('friend_id');

			$assignedChildIds = array(4, 5);
			$this->parentsTable->assignChildren('friends', 2, $assignedChildIds);

			$newChildIds = $this->parentsTable->getChildren('friends', 2)->fetchColumn('friend_id');

			$this->assertEquals($assignedChildIds, $newChildIds);
		}


		public function testAssignChildrenManyToManyNonSubtractive ()
		{
			$originalChildIds = $this->parentsTable->getChildren('friends', 2)->fetchColumn('friend_id');

			$assignedChildIds = array(4, 5);
			$this->parentsTable->assignChildren('friends', 2, $assignedChildIds, false);

			$newChildIds = $this->parentsTable->getChildren('friends', 2)->fetchColumn('friend_id');

			$combinedIds = array_unique(array_merge($originalChildIds, $assignedChildIds));
			sort($combinedIds);
			sort($newChildIds);

			$this->assertEquals($combinedIds, $newChildIds);
		}


		public function testAssignChildOneToMany ()
		{
			$nonChild = $this->parentsTable->getChildren('children', 1)->fetchRow();
			$this->parentsTable->assignChild('children', 2, $nonChild['child_id']);

			$child = $this->childrenTable->getRow($nonChild['child_id']);

			$this->assertEquals($child['parent_id'], 2);
		}


		public function testAssignChildManyToMany ()
		{
			$nonChild = $this->parentsTable->getChildren('friends', 1)->fetchRow();
			$this->parentsTable->assignChild('friends', 2, $nonChild['friend_id']);

			$childIds = $this->parentsTable->getChildren('friends', 2)->fetchColumn('friend_id');

			$this->assertTrue(in_array($nonChild['friend_id'], $childIds));
		}


		public function testUnassignChildOneToMany ()
		{
			$originalChildIds = $this->parentsTable->getChildren('children', 1)->fetchColumn('child_id');
			$this->parentsTable->unassignChild('children', 1, $originalChildIds[0]);
			$childIds = $this->parentsTable->getChildren('children', 1)->fetchColumn('child_id');

			$this->assertFalse(in_array($originalChildIds[0], $childIds));
		}


		public function testUnassignChildManyToMany ()
		{
			$originalChildIds = $this->parentsTable->getChildren('friends', 1)->fetchColumn('friend_id');
			$this->parentsTable->unassignChild('friends', 1, $originalChildIds[0]);
			$childIds = $this->parentsTable->getChildren('friends', 1)->fetchColumn('friend_id');

			$this->assertFalse(in_array($originalChildIds[0], $childIds));
		}


		public function testSetChildrenOneToMany ()
		{
			$children = array(
				array(
					'child_id' => '2',
					'child_name' => 'bobby',
				),
				array(
					'child_name' => 'new_child',
				)
			);

			$this->parentsTable->setChildren('children', 1, $children);

			$newChildren = $this->parentsTable->getChildren('children', 1, $this->connection->createQuery()->orderBy('child_id'))->fetchAll();

			foreach ($newChildren as $index => $child) {
				$this->assertCommonFieldsMatch($child->toArray(), $children[$index]);
			}
		}


		public function testSetChildrenManyToMany ()
		{
			$children = array(
				array(
					'friend_id' => '2',
					'friend_name' => 'bobby',
				),
				array(
					'friend_name' => 'new_child',
				)
			);

			$this->parentsTable->setChildren('friends', 1, $children, true, false);

			$newChildren = $this->parentsTable->getChildren('friends', 1, $this->connection->createQuery()->orderBy('friend_id'))->fetchAll();

			foreach ($newChildren as $index => $child) {
				$this->assertCommonFieldsMatch($child->toArray(), $children[$index]);
			}
		}


		public function testSetChildrenManyToManyRelational ()
		{
			$children = array(
				array(
					'friend_id' => '2',
					'parent_friend_relation' => 'looker',
				),
				array(
					'parent_friend_relation' => 'feeler',
				)
			);

			$this->parentsTable->setChildren('friends', 1, $children, true, true);

			$newChildren = $this->parentsTable->getChildren('friends', 1, $this->connection->createQuery()->orderBy('friend_id'))->fetchAll();

			foreach ($newChildren as $index => $child) {
				$this->assertCommonFieldsMatch($child->toArray(), $children[$index]);
			}
		}


		public function testGetParents ()
		{
			$this->assertResultEqualToResult(
				$this->parentsTable->getChildren('friends', 1),
				$this->parentsTable->getParents('friends', 1)
			);
		}


		public function testGetParent ()
		{
			$child = $this->childrenTable->getRow(1);

			$parent = $this->parentsTable->getRow($child['parent_id']);
			$childParent = $this->childrenTable->getParent('parents', 1);

			$this->assertCommonFieldsMatch($parent->toArray(), $childParent->toArray());
		}
	}
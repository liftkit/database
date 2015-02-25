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

			$this->assertEquals(array_intersect_key($child->toArray(), $insertData), $insertData);
		}


		public function testUpdateRow ()
		{
			$updateData = array(
				'child_id' => '1',
				'child_name' => 'new_child_name',
			);

			$this->childrenTable->updateRow($updateData);
			$child = $this->childrenTable->getRow('1');

			$this->assertEquals(array_intersect_key($child->toArray(), $updateData), $updateData);
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
	}
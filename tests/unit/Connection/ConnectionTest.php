<?php


	namespace LiftKit\Tests\Unit\Database\Connection;

	use LiftKit\Database\Connection\Connection as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;

	use LiftKit\Database\Query\Condition\Condition;
	use LiftKit\Database\Query\Query;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use LiftKit\Tests\Helpers\Database\DataSet\ArrayDataSet;
	use LiftKit\Database\Result\Result;

	use PHPUnit_Extensions_Database_DataSet_DataSetFilter;


	abstract class ConnectionTest extends DefaultTestCase
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


		public function testConnectsToDatabase ()
		{
			$this->assertTrue($this->connection instanceof Connection);
		}


		public function testQuery ()
		{
			$tables = array('parents');
			$dataSet = $this->getConnection()->createDataSet();

			$filterDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);
			$filterDataSet->addIncludeTables($tables);

			$sql = "SELECT *
					FROM parents";

			$result = $this->connection->query($sql);

			$queryDataSet = new ArrayDataSet(
				array(
					'parents' => $result->flatten()
				)
			);

			$this->assertTablesEqual($queryDataSet->getTable('parents'), $filterDataSet->getTable('parents'));
		}


		public function testQueryWithPlaceholders ()
		{
			$sql = "SELECT *
					FROM children
					WHERE parent_id = 1";

			$queryTable = $this->getConnection()->createQueryTable('parent_children', $sql);

			$result = $this->connection->query("
				SELECT *
				FROM children
				WHERE parent_id = ?
			", array(1));

			$queryDataSet = new ArrayDataSet(
				array(
					'parent_children' => $result->flatten()
				)
			);

			$this->assertTablesEqual($queryDataSet->getTable('parent_children'), $queryTable);
		}


		public function testCreateQuery ()
		{
			$this->assertTrue($this->connection->createQuery() instanceof Query);
		}


		public function testCreateCondition ()
		{
			$this->assertTrue($this->connection->createCondition() instanceof Condition);
		}


		public function testGetCache ()
		{
			$this->assertTrue($this->connection->getCache() instanceof Cache);
		}


		public function testToQuery ()
		{
			$query = $this->connection->createQuery();
			$condition = $this->connection->createCondition();

			$this->assertTrue($this->connection->toQuery($query) instanceof Query);
			$this->assertTrue($this->connection->toQuery($condition) instanceof Query);
			$this->assertTrue($this->connection->toQuery(null) instanceof Query);
		}


		public function testQuote ()
		{
			$this->assertEquals("'test\\'s'", $this->connection->quote("test's"));
		}


		public function testQuoteNull ()
		{
			$this->assertEquals('NULL', $this->connection->quote(null));
		}


		public function testInsertId ()
		{
			$sql = "INSERT INTO parents
					SET parent_name = 'parent4'";

			$this->connection->query($sql);

			$this->assertEquals($this->connection->insertId(), 4);
		}


		public function testTransactionRollback ()
		{
			$this->connection->startTransaction();

			$this->connection->query("INSERT INTO parents SET parent_name = 'parent4'");

			$result = $this->connection->query("SELECT * FROM parents");
			$this->assertEquals($result->count(), 4);

			$this->connection->rollback();

			$result = $this->connection->query("SELECT * FROM parents");
			$this->assertEquals($result->count(), 3);
		}


		public function testTransactionCommit ()
		{
			$this->connection->startTransaction();

			$this->connection->query("INSERT INTO parents SET parent_name = 'parent4'");

			$this->connection->commit();

			$result = $this->connection->query("SELECT * FROM parents");
			$this->assertEquals($result->count(), 4);
		}


		public function testCache ()
		{
			$query = $this->connection->createQuery()
				->select('*')
				->from('children');

			$this->assertNotSame(
				$this->connection->query($query),
				$this->connection->query($query)
			);

			$query->setCache(true);

			$this->assertSame(
				$this->connection->query($query),
				$this->connection->query($query)
			);
		}


		public function testRefreshCache ()
		{
			$query = $this->connection->createQuery()
				->select('*')
				->from('children')
				->setCache(true);

			$beforeCache = $this->connection->query($query);

			$this->assertSame(
				$beforeCache,
				$this->connection->query($query)
			);

			$updateQuery = $this->connection->createQuery()
				->update()
				->table('children')
				->set(array('child_id' => 123456))
				->whereEqual('child_id', 12345);

			$this->connection->query($updateQuery);

			$this->assertNotSame(
				$beforeCache,
				$this->connection->query($query)
			);
		}


		public function testGetLastQueryString ()
		{
			$sql = "SELECT * FROM children";

			$this->connection->query($sql);

			$this->assertEquals($sql, $this->connection->getLastQueryString());
		}


		/**
		 * @expectedException \LiftKit\Database\Exception\Database
		 */
		public function testFailure ()
		{
			$sql = "SELECT xyz FROM asd";
			$this->connection->query($sql);
		}


		public function testResultSetReturnedInsteadOfInsertId ()
		{
			$id = $this->connection->query("INSERT INTO parents SET parent_name = 'parent4'");

			$this->assertEquals($id, 4);

			$this->connection->query("INSERT INTO parents SET parent_name = 'parent4'");

			$result = $this->connection->query("SELECT * FROM parents WHERE parent_id = 4");

			$this->assertTrue($result instanceof Result);
		}
	}
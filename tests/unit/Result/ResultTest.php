<?php


	namespace LiftKit\Tests\Unit\Database\Result;

	use LiftKit\Database\Result\Result;
	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use LiftKit\Tests\Stub\Database\Entity\Entity as StubEntity;
	use PDO;


	class ResultTest extends DefaultTestCase
	{


		public function testIsResult ()
		{
			$result = $this->connection->query('SELECT * FROM children');

			$this->assertTrue($result instanceof Result);
		}


		public function testResultMatches ()
		{
			$sql = 'SELECT * FROM children';
			$result = $this->connection->query($sql);

			$this->assertResultEqualToQuery(
				$result,
				$sql
			);
		}


		public function testFetchAll ()
		{
			$sql = 'SELECT * FROM children';

			$pdoResult = self::$pdo->query($sql);
			$result = $this->connection->query($sql);

			foreach ($result->fetchAll() as $row) {
				$rowArray = $pdoResult->fetch(PDO::FETCH_ASSOC);

				$this->assertEquals(
					$rowArray,
					$row->toArray()
				);
			}
		}


		public function testFetchRow ()
		{
			$sql = 'SELECT * FROM children';

			$pdoResult = self::$pdo->query($sql);
			$result = $this->connection->query($sql);

			$this->assertEquals(
				$pdoResult->fetch(PDO::FETCH_ASSOC),
				$result->fetchRow()->toArray()
			);
		}


		public function testFetchColumn ()
		{
			$sql = 'SELECT child_id FROM children';

			$pdoResult = self::$pdo->query($sql);
			$result = $this->connection->query($sql);

			foreach ($result->fetchColumn('child_id') as $index => $childId) {
				$pdoRow = $pdoResult->fetch();

				$this->assertEquals(
					$childId,
					$pdoRow['child_id']
				);
			}
		}


		public function testFetchField ()
		{
			$sql = 'SELECT child_id
					FROM children
					ORDER BY child_id
					LIMIT 1';
			$childId = $this->connection->query($sql)->fetchField();
			$testChildId = self::$pdo->query($sql)->fetchColumn();

			$this->assertEquals($childId, $testChildId);
		}


		public function testTransform ()
		{
			$sql = 'SELECT child_id
					FROM children';
			$columnsResult = $this->connection->query($sql)->fetchColumn('child_id');

			$transformedResult = $this->connection->query($sql)->transform(
				function ($row)
				{
					return $row['child_id'];
				}
			);

			$this->assertEquals($columnsResult, $transformedResult);
		}


		public function testFlatten ()
		{
			$sql = 'SELECT *
					FROM children';
			$flattened = $this->connection->query($sql)->flatten();

			$transformed = $this->connection->query($sql)->transform(
				function ($row)
				{
					return $row->toArray();
				}
			);

			$this->assertEquals($flattened, $transformed);
		}


		public function testCount ()
		{
			$sql = 'SELECT *
					FROM children';

			$resultCount = $this->connection->query($sql)->count();
			$pdoCount = self::$pdo->query($sql)->rowCount();

			$this->assertEquals($resultCount, $pdoCount);
		}


		public function testEntity ()
		{
			$this->container->setRule(
				'StubEntity',
				function ($container, $data)
				{
					return new StubEntity($data);
				}
			);

			$sql = 'SELECT *
					FROM children';
			$entity = $this->connection->query($sql, array(), 'StubEntity')->fetchRow();

			$this->assertTrue($entity instanceof StubEntity);
		}


		public function testGetQueryString ()
		{
			$sql = "SELECT * FROM children";

			$result = $this->connection->query($sql);

			$this->assertEquals($sql, $result->getQueryString());
		}
	}
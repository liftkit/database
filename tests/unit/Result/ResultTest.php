<?php


	namespace LiftKit\Tests\Unit\Database\Result;

	use LiftKit\Database\Result\Result;
	use LiftKit\Tests\Unit\Database\DefaultTestCase;
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
	}
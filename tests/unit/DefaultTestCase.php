<?php


	namespace LiftKit\Tests\Unit\Database;

	use LiftKit\Database\Connection\MySql as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Result\Result;

	use PHPUnit_Extensions_Database_DataSet_QueryDataSet;
	use LiftKit\Tests\Helpers\Database\DataSet\ArrayDataSet;
	use LiftKit\Tests\Helpers\Database\Operation\Truncate as TruncateOperation;
	use LiftKit\Tests\Helpers\Database\Operation\Insert as InsertOperation;
	use PDO;



	abstract class DefaultTestCase extends TestCase
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



		public function afterConnection ()
		{
			$this->cache = new Cache;
			$this->container = new Container;

			if (! $this->connection) {
				$this->connection = new Connection(
					$this->container,
					$this->cache,
					self::$pdo
				);
			}
		}


		public function getSetUpOperation()
		{
			$cascadeTruncates = true; // If you want cascading truncates, false otherwise. If unsure choose false.

			return new \PHPUnit_Extensions_Database_Operation_Composite(array(
				new TruncateOperation($cascadeTruncates),
				new InsertOperation()
			));
		}


		public function getDataSet ()
		{
			return $this->getDefaultDataSet();
		}


		protected function getDefaultDataSet ()
		{
			return $this->createMySQLXMLDataSet(__DIR__ . '/../datasets/default/default.xml');
		}


		protected function assertRowEqualToQuery ($row, $query)
		{
			$this->assertCommonFieldsMatch(
				self::$pdo->query($query)->fetch(PDO::FETCH_ASSOC),
				$row
			);
		}


		protected function assertResultEqualToQuery (Result $result, $query)
		{
			$this->assertTablesEqual(
				$this->createTableFromQuery($query),
				$this->createTableFromResult($result)
			);
		}


		protected function assertResultEqualToResult (Result $result1, Result $result2)
		{
			$this->assertTablesEqual(
				$this->createTableFromResult($result1),
				$this->createTableFromResult($result2)
			);
		}


		protected function assertCommonFieldsMatch ($row, $compare)
		{
			$this->assertEquals(array_intersect_key($row, $compare), array_intersect_key($compare, $row));
		}


		protected function createTableFromResult (Result $result)
		{
			$dataSet = new ArrayDataSet(
				array(
					'result_table' => $result->flatten()
				)
			);

			return $dataSet->getTable('result_table');
		}


		protected function createTableFromQuery ($query)
		{
			$queryDataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
			$queryDataSet->addTable('result_table', $query);

			return $queryDataSet->getTable('result_table');
		}
	}
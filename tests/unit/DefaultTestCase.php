<?php


	namespace LiftKit\Tests\Unit\Database;

	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Result\Result;

	use PHPUnit_Extensions_Database_DataSet_QueryDataSet;
	use LiftKit\Tests\Helpers\Database\DataSet\ArrayDataSet;



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

			$this->connection = new Connection(
				$this->container,
				$this->cache,
				$this->host,
				$this->user,
				$this->password,
				$this->schema
			);
		}


		public function getDataSet ()
		{
			return $this->getDefaultDataSet();
		}


		protected function getDefaultDataSet ()
		{
			return $this->createMySQLXMLDataSet(__DIR__ . '/../datasets/default/default.xml');
		}


		protected function assertResultEqualToQuery (Result $result, $query)
		{
			$this->assertTablesEqual(
				$this->createTableFromResult($result),
				$this->createTableFromQuery($query)
			);
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
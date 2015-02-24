<?php


	namespace LiftKit\Tests\Unit\Database\Schema\Table;

	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Schema\Table\Table;
	use LiftKit\Database\Result\Result;

	use LiftKit\Tests\Helpers\Database\DataSet\ArrayDataSet;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use PHPUnit_Extensions_Database_DataSet_QueryDataSet;


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


		protected function assertResultEqualToQuery (Result $result, $query)
		{
			$resultDataSet = new ArrayDataSet(
				array(
					'result_table' => $result->flatten()
				)
			);

			$queryDataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
			$queryDataSet->addTable('result_table', $query);

			$this->assertTablesEqual($resultDataSet->getTable('result_table'), $queryDataSet->getTable('result_table'));
		}
	}
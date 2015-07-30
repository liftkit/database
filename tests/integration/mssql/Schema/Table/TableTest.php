<?php


	namespace LiftKit\Tests\Integration\MsSql\Database\Schema\Table;

	use LiftKit\Database\Connection\MsSql as Connection;
	use LiftKit\Tests\Integration\MsSql\Database\MsSqlTrait;
	use LiftKit\Tests\Unit\Database\Schema\Table\TableTest as BaseTableTest;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Schema\Schema;


	/**
	 * Class TableTest
	 *
	 * @package LiftKit\Tests\Integration\MsSql\Database\Schema\Table
	 * @group current
	 */
	class TableTest extends BaseTableTest
	{
		use MsSqlTrait;


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

			$this->schema = new Schema($this->connection);

			$this->parentsTable = $this->schema->defineTable('parents')
				->oneToMany('children')
				->manyToMany('friends', 'parent_friends');

			$this->childrenTable = $this->schema->defineTable('children')
				->manyToOne('parents');

			$this->friendsTable = $this->schema->defineTable('friends')
				->manyToMany('parents', 'parent_friends', 'parent_id', 'friend_id');
		}


		/**
		 * @expectedException \LiftKit\Database\Query\Exception\Query
		 */
		public function testInsertUpdateRow ()
		{
			$updateData = array(
				'child_id' => '1',
				'child_name' => 'new_child_name',
			);

			$this->childrenTable->insertUpdateRow($updateData);
		}
	}
<?php

	
	namespace LiftKit\Tests\Unit\Database\Schema\Table\Relation;
	
	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	
	use LiftKit\Database\Connection\MySQL as Connection;
	use LiftKit\Database\Cache\Cache;
	use LiftKit\DependencyInjection\Container\Container;
	use LiftKit\Database\Schema\Table\Table;
	
	
	abstract class RelationTestCase extends DefaultTestCase
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



		/**
		 * @var Table
		 */
		protected $parentFriendsTable;



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

			$this->parentsTable       = new Table($this->connection, 'parents');
			$this->childrenTable      = new Table($this->connection, 'children');
			$this->friendsTable       = new Table($this->connection, 'friends');
			$this->parentFriendsTable = new Table($this->connection, 'parent_friends');
		}
	}
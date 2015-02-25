<?php


	namespace LiftKit\Tests\Unit\Database\Cache;

	use LiftKit\Database\Cache\Cache;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;


	class CacheTest extends DefaultTestCase
	{
		/**
		 * @var Cache
		 */
		protected $cache;


		public function afterConnection ()
		{
			parent::afterConnection();

			$this->cache = new Cache;
		}


		public function testCache ()
		{
			$query = $this->connection->createQuery()
				->select('*')
				->from('children')
				->setCache(true);
			$result = $query->execute();

			$this->cache->cacheQuery($query, $result);

			$this->assertTrue($this->cache->isCached($query));
			$this->assertSame($result, $this->cache->getCachedResult($query));
		}


		public function testRefreshCacheForTables ()
		{
			$childrenQuery = $this->connection->createQuery()
				->select('*')
				->from('children')
				->setCache(true);
			$childrenResult = $childrenQuery->execute();

			$this->cache->cacheQuery($childrenQuery, $childrenResult);
			$this->assertSame($childrenResult, $this->cache->getCachedResult($childrenQuery));

			$parentsQuery = $this->connection->createQuery()
				->select('*')
				->from('parents')
				->setCache(true);
			$parentsResult = $parentsQuery->execute();

			$this->cache->cacheQuery($parentsQuery, $parentsResult);
			$this->assertSame($parentsResult, $this->cache->getCachedResult($parentsQuery));

			$this->cache->refreshCacheForTables(array('parents', 'children'));

			$this->assertTrue(is_null($this->cache->getCachedResult($childrenQuery)));
			$this->assertTrue(is_null($this->cache->getCachedResult($parentsQuery)));
		}


		public function testRefreshCacheOnUpdate ()
		{
			$childrenQuery = $this->connection->createQuery()
				->select('*')
				->from('children')
				->setCache(true);
			$childrenResult = $childrenQuery->execute();

			$this->cache->cacheQuery($childrenQuery, $childrenResult);

			$parentsQuery = $this->connection->createQuery()
				->select('*')
				->from('parents')
				->setCache(true);
			$parentsResult = $parentsQuery->execute();

			$this->cache->cacheQuery($parentsQuery, $parentsResult);

			$refreshQuery = $this->connection->createQuery()
				->update()
				->table('children')
				->set(array('child_id' => 123456))
				->whereEqual('child_id', 12345);

			$this->cache->refreshCache($refreshQuery);

			$this->assertTrue(is_null($this->cache->getCachedResult($childrenQuery)));
			$this->assertSame($parentsResult, $this->cache->getCachedResult($parentsQuery));
		}
	}
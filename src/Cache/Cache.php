<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */


	namespace LiftKit\Database\Cache;

	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Result\Result as DatabaseResult;


	class Cache
	{
		protected $cachedQueries = array();


		public function cacheQuery (DatabaseQuery $query, DatabaseResult $result)
		{
			if ($query->getType() == DatabaseQuery::QUERY_TYPE_SELECT && $query->isCached()) {
				$this->cachedQueries[$query->getRaw()] = array(
					'result' => $result,
					'dependentTables' => $this->getDependentTables($query),
				);
			}
		}


		public function refreshCacheForTables ($dependentTables)
		{
			foreach ($this->cachedQueries as $index => $cachedQuery) {
				$commonTables = array_intersect($cachedQuery['dependentTables'], $dependentTables);

				if (count($commonTables)) {
					unset($this->cachedQueries[$index]);
				}
			}
		}


		public function refreshCache (DatabaseQuery $query)
		{
			if ($query->getType() != DatabaseQuery::QUERY_TYPE_SELECT) {
				$dependentTables = $this->getDependentTables($query);
				$this->refreshCacheForTables($dependentTables);
			}
		}


		public function getCachedResult (DatabaseQuery $query)
		{
			if (isset($this->cachedQueries[$query->getRaw()])) {
				$result = $this->cachedQueries[$query->getRaw()]['result'];
			} else {
				$result = null;
			}

			if ($query->getType() == DatabaseQuery::QUERY_TYPE_SELECT && $query->isCached() && $result) {
				$result->rewind();

				return $result;
			} else {
				return null;
			}
		}


		public function isCached ($query)
		{
			if ($query instanceof DatabaseQuery && isset($this->cachedQueries[$query->getRaw()])) {
				return $this->cachedQueries[$query->getRaw()]['result']
					&& $query->getType() == DatabaseQuery::QUERY_TYPE_SELECT
					&& $query->isCached();
			} else {
				return false;
			}
		}


		protected function getDependentTables (DatabaseQuery $query)
		{
			$dependentTables[$query->getTable()] = $query->getTable();

			foreach ($query->getJoins() as $joinData) {
				if (is_string($joinData->getTable())) {
					$dependentTables[$joinData->getTable()] = $joinData->getTable();
				}
			}

			return array_values($dependentTables);
		}
	}

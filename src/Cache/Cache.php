<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */


	namespace LiftKit\Database\Cache;

	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Interfaces\DatabaseResult;


	class Cache
	{
		protected $cachedQueries = array();


		public function cacheQuery (DatabaseQuery $query, DatabaseResult $result)
		{
			if ($query->getType() == DatabaseQuery::QUERY_TYPE_SELECT && $query->isCached())
			{
				$this->cachedQueries[$query->getRaw()] = array(
					'result' => $result,
					'dependentTables' => $this->getDependentTables($query),
				);
			}
		}


		public function refreshCache (DatabaseQuery $query)
		{
			if ($query->getType() != DatabaseQuery::QUERY_TYPE_SELECT)
			{
				$dependentTables = $this->getDependentTables($query);

				foreach ($this->cachedQueries as $index => $cachedQuery) {
					$commonTables = array_intersect($cachedQuery['dependentTables'], $dependentTables);

					if (count($commonTables)) {
						unset($this->cachedQueries[$index]);
					}
				}
			}
		}


		public function getCachedResult (DatabaseQuery $query)
		{
			$result = $this->cachedQueries[$query->getRaw()]['result'];

			if ($query->getType() == DatabaseQuery::QUERY_TYPE_SELECT && $query->isCached() && $result) {
				$result->rewind();

				return $result;
			} else {
				return null;
			}
		}


		protected function getDependentTables (DatabaseQuery $query)
		{
			$dependentTables[$query->getTable()] = $query->getTable();

			foreach ($query->getJoins() as $joinData) {
				$dependentTables[$joinData['table']] = $joinData['table'];
			}

			return array_values($dependentTables);
		}
	}

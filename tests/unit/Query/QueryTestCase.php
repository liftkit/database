<?php

	namespace LiftKit\Tests\Unit\Database\Query;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	use LiftKit\Database\Query\Condition\Condition;
	use LiftKit\Database\Query\Query;


	abstract class QueryTestCase extends DefaultTestCase
	{

		/**
		 * @var Condition
		 */
		protected $condition;


		/**
		 * @var Query
		 */
		protected $query;



		public function afterConnection ()
		{
			parent::afterConnection();

			$this->query = $this->connection->createQuery();
			$this->condition = $this->connection->createCondition();
		}


		protected function normalizeSql ($sql)
		{
			$sql = (string) $sql;
			$sql = preg_replace('#\s+#', ' ', $sql);
			$sql = trim($sql);
			$sql = preg_replace('#\(\s+#', '(', $sql);
			$sql = preg_replace('#\s+\)#', ')', $sql);
			$sql = preg_replace('#`([A-Za-z0-9_\-]+?)`#', '$1', $sql);

			return $sql;
		}

	}
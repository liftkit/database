<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Database\Query;

	use LiftKit\Database\Query\Exception\UnsupportedFeature;


	class MsSql extends Query
	{


		protected function generateInsertIgnoreQuery ()
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support INSERT IGNORE queries');
		}


		protected function generateInsertUpdateQuery ()
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support INSERT UPDATE queries');
		}


		public function insertIgnore ()
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support INSERT IGNORE queries');
		}


		public function insertUpdate ()
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support INSERT UPDATE queries');
		}


		public function leftJoinUsing ($table, $field, $alias = null)
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support USING conditions');
		}


		public function rightJoinUsing ($table, $field, $alias = null)
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support USING conditions');
		}


		public function innerJoinUsing ($table, $field, $alias = null)
		{
			throw new UnsupportedFeature('The MSSQL driver doesn\'t support USING conditions');
		}


		protected function generateSelectQuery ()
		{
			if ($this->limit) {
				$query = $this->database->createQuery()
					->composeWith($this);

				$query->composeWith($this->createLimitQuery());
			} else {
				$query = $this;
			}

			$queryLines[] = "SELECT " . $query->processFields();

			if ($this->alias) {
				$queryLines[] = "FROM " . $query->filterIdentifier($this->table) . " AS " . $query->filterIdentifier($this->alias);
			} else {
				$queryLines[] = "FROM " . $query->filterIdentifier($this->table);
			}

			$queryLines[] = $query->processJoins();
			$queryLines[] = $query->processWhere();
			$queryLines[] = $query->processGroupBy();
			$queryLines[] = $query->processHaving();
			$queryLines[] = $query->processOrderBy();

			$queryLines = array_filter($queryLines);

			return implode(PHP_EOL, $queryLines);
		}


		protected function processLimit ()
		{
			return '';
		}


		protected function createLimitQuery ()
		{
			if (empty($this->orderBys)) {
				$this->orderBy($this->getTable() . '.' . $this->database->primaryKey($this->getTable()));
			}

			$tableName = $this->alias ?: $this->table;

			$subQueryLines[] = "SELECT "
				. $this->database->quoteIdentifier($tableName . '.' . $this->database->primaryKey($this->table))
				. " AS LK_ROW_ID"
				. ", ROW_NUMBER() OVER (" . $this->processOrderBy() . ") AS LK_ROW_NUMBER";

			$subQueryLines[] = "FROM " . $this->database->quoteIdentifier($this->table) . " "
				. ($this->alias  ? "AS " . $this->database->quoteIdentifier($this->alias) : "");

			$subQueryLines[] = $this->processJoins();
			$subQueryLines[] = $this->processWhere();
			$subQueryLines[] = $this->processGroupBy();
			$subQueryLines[] = $this->processHaving();

			return $this->database->createQuery()
				->leftJoinEqual(
					$this->database->createRaw("(" . implode(PHP_EOL, $subQueryLines) . ")"),
					'LK_ROWS.LK_ROW_ID',
					$this->table . '.' . $this->database->primaryKey($this->table),
					'LK_ROWS'
				)
				->from($this->table)
				->whereGreaterThanOrEqual('LK_ROWS.LK_ROW_NUMBER', $this->start + 1)
				->whereLessThanOrEqual('LK_ROWS.LK_ROW_NUMBER', $this->start + $this->limit);
		}
	}



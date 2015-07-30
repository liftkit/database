<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Database\Query;

	use LiftKit\Database\Query\Exception\Query as QueryBuilderException;


	class MsSql extends Query
	{


		protected function generateInsertIgnoreQuery ()
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support INSERT IGNORE queries');
		}


		protected function generateInsertUpdateQuery ()
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support INSERT UPDATE queries');
		}


		public function insertIgnore ()
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support INSERT IGNORE queries');
		}


		public function insertUpdate ()
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support INSERT UPDATE queries');
		}


		public function leftJoinUsing ($table, $field, $alias = null)
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support USING conditions');
		}


		public function rightJoinUsing ($table, $field, $alias = null)
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support USING conditions');
		}


		public function innerJoinUsing ($table, $field, $alias = null)
		{
			throw new QueryBuilderException('The MSSQL driver doesn\'t support USING conditions');
		}


		protected function generateSelectQuery ()
		{
			if ($this->limit) {
				if (empty($this->orderBys)) {
					$this->orderBy($this->database->primaryKey($this->getTable()));
				}

				$innerQueryLines[] = "SELECT TOP 100 PERCENT " . $this->processFields() . ", ROW_NUMBER() OVER(" . $this->processOrderBy() . ") AS LK_ROW_NUMBER";

				if ($this->alias) {
					$innerQueryLines[] = "FROM " . $this->filterIdentifier($this->table) . " AS " . $this->filterIdentifier($this->alias);
				} else {
					$innerQueryLines[] = "FROM " . $this->filterIdentifier($this->table);
				}

				$innerQueryLines[] = $this->processJoins();
				$innerQueryLines[] = $this->processWhere();
				$innerQueryLines[] = $this->processGroupBy();
				$innerQueryLines[] = $this->processHaving();
				$innerQueryLines[] = $this->processOrderBy();

				$innerQueryLines = array_filter($innerQueryLines);

				$queryLines[] = "SELECT " . $this->processFields();

				if ($this->alias) {
					$queryLines[] = "FROM (" . implode(PHP_EOL, $innerQueryLines) . ") AS " . $this->filterIdentifier($this->alias);
				} else {
					$queryLines[] = "FROM (" . implode(PHP_EOL, $innerQueryLines) . ") AS " . $this->filterIdentifier($this->table);
				}

				$queryLines[] = "WHERE LK_ROW_NUMBER BETWEEN " . ($this->start + 1) . " AND " . ($this->start + $this->limit);

				$queryLines = array_filter($queryLines);

				return implode(PHP_EOL, $queryLines);
			} else {
				$queryLines[] = "SELECT " . $this->processFields();

				if ($this->alias) {
					$queryLines[] = "FROM " . $this->filterIdentifier($this->table) . " AS " . $this->filterIdentifier($this->alias);
				} else {
					$queryLines[] = "FROM " . $this->filterIdentifier($this->table);
				}

				$queryLines[] = $this->processJoins();
				$queryLines[] = $this->processWhere();
				$queryLines[] = $this->processGroupBy();
				$queryLines[] = $this->processHaving();
				$queryLines[] = $this->processOrderBy();

				$queryLines = array_filter($queryLines);

				return implode(PHP_EOL, $queryLines);
			}
		}
	}



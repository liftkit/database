<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Database\Query;

	use LiftKit\Database\Connection\Connection as Database;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Exception\Query as QueryBuilderException;
	use LiftKit\Database\Query\Condition\Condition as DatabaseQueryCondition;
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Query\Raw\Raw;


	/**
	 * Class Query
	 *
	 * @package LiftKit\Database\Query
	 */
	class Query
	{
		const QUERY_TYPE_SELECT = 'SELECT';
		const QUERY_TYPE_INSERT = 'INSERT';
		const QUERY_TYPE_INSERT_IGNORE = 'INSERT IGNORE';
		const QUERY_TYPE_INSERT_UPDATE = 'INSERT UPDATE';
		const QUERY_TYPE_UPDATE = 'UPDATE';
		const QUERY_TYPE_DELETE = 'DELETE';

		protected $database;

		protected $type;
		protected $table;
		protected $fields = array();
		protected $data = array();
		protected $joins = array();
		protected $unions = array();
		protected $whereCondition;
		protected $havingCondition;
		protected $groupBys = array();
		protected $orderBys = array();
		protected $start = 0;
		protected $limit = null;
		protected $isCached = false;

		protected $entityHydrationRule = null;
		protected $prependFields = false;

		protected $whereConditionMethodMap = array(
			'whereEqual'              => 'equal',
			'orWhereEqual'            => 'orEqual',
			'whereNotEqual'           => 'notEqual',
			'orWhereNotEqual'         => 'orNotEqual',

			'whereLessThan'           => 'lessThan',
			'orWhereLessThan'         => 'orLessThan',
			'whereLessThanOrEqual'    => 'lessThanOrEqual',

			'whereGreaterThan'        => 'greaterThan',
			'orWhereGreaterThan'      => 'orGreaterThan',
			'whereGreaterThanOrEqual' => 'greaterThanOrEqual',

			'whereIn'                 => 'in',
			'orWhereIn'               => 'orIn',
			'whereNotIn'              => 'notIn',
			'orWhereNotIn'            => 'orNotIn',

			'whereIs'                 => 'is',
			'orWhereIs'               => 'orIs',
			'whereNotIs'              => 'notIs',
			'orWhereNotIs'            => 'orNotIs',

			'whereLike'               => 'like',
			'orWhereLike'             => 'orLike',
			'whereNotLike'            => 'notLike',
			'orWhereNotLike'          => 'orNotLike',

			'whereRegexp'             => 'regexp',
			'orWhereRegexp'           => 'orRegexp',
			'whereNotRegexp'          => 'notRegexp',
			'orWhereNotRegexp'        => 'orNotRegexp',

			'whereCondition'          => 'condition',
			'orWhereCondition'        => 'orCondition',
			'whereNotCondition'       => 'notCondition',
			'orWhereNotCondition'     => 'orNotCondition',

			'whereRaw'                => 'raw',
			'orWhereRaw'              => 'orRaw',
			'notWhereRaw'             => 'notRaw',
			'orWhereNotRaw'           => 'orNotRaw',

			'whereSearch'             => 'search',
		);

		protected $havingConditionMethodMap = array(
			'havingEqual'              => 'equal',
			'orHavingEqual'            => 'orEqual',
			'havingNotEqual'           => 'notEqual',
			'orHavingNotEqual'         => 'orNotEqual',

			'havingLessThan'           => 'lessThan',
			'orHavingLessThan'         => 'orLessThan',
			'havingLessThanOrEqual'    => 'lessThanOrEqual',

			'havingGreaterThan'        => 'greaterThan',
			'orHavingGreaterThan'      => 'orGreaterThan',
			'havingGreaterThanOrEqual' => 'greaterThanOrEqual',

			'havingIn'                 => 'in',
			'orHavingIn'               => 'orIn',
			'havingNotIn'              => 'notIn',
			'orHavingNotIn'            => 'orNotIn',

			'havingIs'                 => 'is',
			'orHavingIs'               => 'orIs',
			'havingNotIs'              => 'notIs',
			'orHavingNotIs'            => 'orNotIs',

			'havingLike'               => 'like',
			'orHavingLike'             => 'orLike',
			'havingNotLike'            => 'notLike',
			'orHavingNotLike'          => 'orNotLike',

			'havingRegexp'             => 'regexp',
			'orHavingRegexp'           => 'orRegexp',
			'havingNotRegexp'          => 'notRegexp',
			'orHavingNotRegexp'        => 'orNotRegexp',

			'havingCondition'          => 'condition',
			'orHavingCondition'        => 'orCondition',
			'havingNotCondition'       => 'notCondition',
			'orHavingNotCondition'     => 'orNotCondition',

			'havingRaw'                => 'raw',
			'orHavingRaw'              => 'orRaw',
			'notHavingRaw'             => 'notRaw',
			'orHavingNotRaw'           => 'orNotRaw',
		);


		public function __construct (Database $database)
		{
			$this->database        = $database;
			$this->whereCondition  = $this->database->createCondition();
			$this->havingCondition = $this->database->createCondition();

		}


		public function __call ($method, $arguments)
		{
			if (isset($this->whereConditionMethodMap[$method])) {
				call_user_func_array(
					array(
						$this->whereCondition,
						$this->whereConditionMethodMap[$method]
					),
					$arguments
				);

				return $this;
			} else if (isset($this->havingConditionMethodMap[$method])) {
				call_user_func_array(
					array(
						$this->havingCondition,
						$this->havingConditionMethodMap[$method]
					),
					$arguments
				);

				return $this;
			} else {
				throw new QueryBuilderException('Method ' . $method . ' not found.');
			}
		}


		public function setCache ($bool)
		{
			$this->isCached = $bool;

			return $this;
		}


		public function isCached ()
		{
			return (bool) $this->isCached;
		}


		public function setPrependFields ($bool)
		{
			$this->prependFields = (boolean) $bool;

			return $this;
		}


		public function setEntity ($dependencyInjectionRule)
		{
			$this->entityHydrationRule = $dependencyInjectionRule;

			return $this;
		}


		public function composeWith ($query)
		{
			$query = $this->database->toQuery($query);

			$this->type = $query->type ? $query->type : $this->type;

			if ($query->prependFields) {
				$this->fields = array_merge($query->fields, $this->fields);
				$this->data   = array_merge($query->data, $this->data);
			} else {
				$this->fields = array_merge($this->fields, $query->fields);
				$this->data   = array_merge($this->data, $query->data);
			}

			$this->joins  = array_merge($this->joins, $query->joins);
			$this->unions = array_merge($this->unions, $query->unions);

			if (!$query->whereCondition->isEmpty()) {
				$this->whereCondition($query->whereCondition);
			}

			if (!$query->havingCondition->isEmpty()) {
				$this->havingCondition($query->havingCondition);
			}

			$this->groupBys = array_merge($this->groupBys, $query->groupBys);
			$this->orderBys = array_merge($this->orderBys, $query->orderBys);

			$this->start = $query->start ? $query->start : $this->start;
			$this->limit = $query->limit ? $query->limit : $this->limit;

			$this->entityHydrationRule = $query->entityHydrationRule ?: $this->entityHydrationRule;

			return $this;
		}


		public function __toString ()
		{
			return $this->getRaw();
		}


		public function getRaw ()
		{
			$queryLines = array();

			if ($this->type == self::QUERY_TYPE_SELECT) {
				$queryLines[] = "SELECT " . $this->processFields();
				$queryLines[] = "FROM " . $this->database->quoteIdentifier($this->table);
				$queryLines[] = $this->processJoins();
				$queryLines[] = $this->processWhere();
				$queryLines[] = $this->processGroupBy();
				$queryLines[] = $this->processHaving();
				$queryLines[] = $this->processOrderBy();
				$queryLines[] = $this->processLimit();

			} else if ($this->type == self::QUERY_TYPE_INSERT) {
				$queryLines[] = "INSERT INTO " . $this->database->quoteIdentifier($this->table);
				$queryLines[] = "SET " . $this->processData();

			} else if ($this->type == self::QUERY_TYPE_INSERT_IGNORE) {
				$queryLines[] = "INSERT IGNORE INTO ".$this->database->quoteIdentifier($this->table);
				$queryLines[] = "SET " . $this->processData();

			} else if ($this->type == self::QUERY_TYPE_INSERT_UPDATE) {
				$queryLines[] = "INSERT INTO " . $this->database->quoteIdentifier($this->table);
				$queryLines[] = "SET " . $this->processData();
				$queryLines[] = "ON DUPLICATE KEY UPDATE " . $this->processData();

			} else if ($this->type == self::QUERY_TYPE_UPDATE) {
				$queryLines[] = "UPDATE " . $this->database->quoteIdentifier($this->table);
				$queryLines[] = "SET " . $this->processData();
				$queryLines[] = $this->processJoins();
				$queryLines[] = $this->processWhere();
				$queryLines[] = $this->processGroupBy();
				$queryLines[] = $this->processHaving();
				$queryLines[] = $this->processOrderBy();
				$queryLines[] = $this->processLimit();

			} else if ($this->type == self::QUERY_TYPE_DELETE) {
				$queryLines[] = "DELETE " . $this->processFields();
				$queryLines[] = "FROM " . $this->database->quoteIdentifier($this->table);
				$queryLines[] = $this->processJoins();
				$queryLines[] = $this->processWhere();
				$queryLines[] = $this->processGroupBy();
				$queryLines[] = $this->processHaving();
				$queryLines[] = $this->processOrderBy();
				$queryLines[] = $this->processLimit();

			} else {
				throw new QueryBuilderException('Invalid query type '.var_export($this->type, true));
			}

			$queryLines = array_filter($queryLines);

			return implode($queryLines, PHP_EOL);
		}


		public function getJoins ()
		{
			return $this->joins;
		}


		public function getTable ()
		{
			return $this->table;
		}


		public function getType ()
		{
			return $this->type;
		}


		public function execute ()
		{
			$cache = $this->database->getCache();
			$cache->refreshCache($this);

			if ($result = $cache->getCachedResult($this)) {
				return $result;
			} else {
				$result = $this->database->query($this->getRaw(), array(), $this->entityHydrationRule);

				if ($result instanceof DatabaseResult) {
					$cache->cacheQuery($this, $result);
				}

				return $result;
			}
		}


		public function set ($data)
		{
			$this->data = array_merge($this->data, (array)$data);

			return $this;
		}


		public function fields ($fields, $prepend = false)
		{
			if (!is_array($fields)) {
				throw new QueryBuilderException('The fields parameter must be an array.');
			}

			foreach ($fields as $field) {
				if (is_array($field)) {
					$alias = $field[1];
					$field = $field[0];
				}

				if ($field instanceof self) {
					$field = '(' . $field->getRaw() . ')';
				}

				if (isset($alias)) {
					$field .= ' AS ' . $this->filterIdentifier($alias);
				}

				if ($prepend) {
					array_unshift($this->fields, $field);
				} else {
					$this->fields[] = $field;
				}
			}

			return $this;
		}


		public function addField ($field, $alias = null)
		{
			if ($field instanceof self) {
				$field = '(' . $field->getRaw() . ')';
			}

			if (!is_null($alias)) {
				$field .= ' AS ' . $this->filterIdentifier($alias);
			}

			$this->fields(array($field));

			return $this;
		}


		public function select ()
		{
			$this->type = self::QUERY_TYPE_SELECT;
			$this->fields(func_get_args());

			return $this;
		}


		public function insert ()
		{
			$this->type = self::QUERY_TYPE_INSERT;

			return $this;
		}


		public function insertIgnore ()
		{
			$this->type = self::QUERY_TYPE_INSERT_IGNORE;

			return $this;
		}


		public function insertUpdate ()
		{
			$this->type = self::QUERY_TYPE_INSERT_UPDATE;

			return $this;
		}


		public function update ()
		{
			$this->type = self::QUERY_TYPE_UPDATE;

			return $this;
		}


		public function delete ()
		{
			$this->type = self::QUERY_TYPE_DELETE;
			$this->fields(func_get_args());

			return $this;
		}


		public function table ($table)
		{
			$this->table = $table;

			return $this;
		}


		public function from ($table)
		{
			$this->table($table);

			return $this;
		}


		public function into ($table)
		{
			$this->table($table);

			return $this;
		}


		public function leftJoin ($table, $condition)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'LEFT JOIN',
				'relation'  => 'ON',
				'condition' => $condition,
			);

			return $this;
		}


		public function leftJoinUsing ($table, $field)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'LEFT JOIN',
				'relation'  => 'USING',
				'condition' => $this->filterIdentifier($field),
			);

			return $this;
		}


		public function leftJoinEqual ($table, $left, $right)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'LEFT JOIN',
				'relation'  => 'ON',
				'condition' => $this->filterIdentifier($left) . '=' . $this->filterIdentifier($right),
			);

			return $this;
		}


		public function rightJoin ($table, $condition)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'RIGHT JOIN',
				'relation'  => 'ON',
				'condition' => $condition,
			);

			return $this;
		}


		public function rightJoinUsing ($table, $field)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'RIGHT JOIN',
				'relation'  => 'USING',
				'condition' => $this->filterIdentifier($field),
			);

			return $this;
		}


		public function rightJoinEqual ($table, $left, $right)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'RIGHT JOIN',
				'relation'  => 'ON',
				'condition' => $this->filterIdentifier($left) . '=' . $this->filterIdentifier($right),
			);

			return $this;
		}


		public function innerJoin ($table, $condition)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'INNER JOIN',
				'relation'  => 'ON',
				'condition' => $condition,
			);

			return $this;
		}


		public function innerJoinUsing ($table, $field)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'INNER JOIN',
				'relation'  => 'USING',
				'condition' => $this->filterIdentifier($field),
			);

			return $this;
		}


		public function innerJoinEqual ($table, $left, $right)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'INNER JOIN',
				'relation'  => 'ON',
				'condition' => $this->filterIdentifier($left) . '=' . $this->filterIdentifier($right),
			);

			return $this;
		}


		public function outerJoin ($table, $condition)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'OUTER JOIN',
				'relation'  => 'ON',
				'condition' => $condition,
			);

			return $this;
		}


		public function outerJoinUsing ($table, $field)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'OUTER JOIN',
				'relation'  => 'USING',
				'condition' => $this->filterIdentifier($field),
			);

			return $this;
		}


		public function outerJoinEqual ($table, $left, $right)
		{
			$this->joins[] = array(
				'table'     => $table,
				'type'      => 'OUTER JOIN',
				'relation'  => 'ON',
				'condition' => $this->filterIdentifier($left) . '=' . $this->filterIdentifier($right),
			);

			return $this;
		}


		public function where ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->whereCondition->condition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->whereCondition->raw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function orWhere ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->whereCondition->orCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->whereCondition->orRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function notWhere ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->whereCondition->notCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->whereCondition->notRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function orNotWhere ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->whereCondition->orNotCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->whereCondition->orNotRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function having ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->havingCondition->condition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->havingCondition->raw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function orHaving ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->havingCondition->orCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->havingCondition->orRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function notHaving ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->havingCondition->notCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->havingCondition->notRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function orNotHaving ($condition)
		{
			if ($condition instanceof DatabaseQueryCondition) {
				$this->havingCondition->orNotCondition($condition);
			} else if ($condition instanceof Raw || is_string($condition)) {
				$this->havingCondition->orNotRaw($condition);
			} else {
				throw new QueryBuilderException(gettype($condition).' is not a valid condition type.');
			}

			return $this;
		}


		public function groupBy ($field)
		{
			$this->groupBys[] = $this->filterIdentifier($field);

			return $this;
		}


		public function orderBy ($field, $direction = 'ASC')
		{
			$this->orderBys[] = array(
				'field'     => $this->filterIdentifier($field),
				'direction' => $direction,
			);

			return $this;
		}


		public function start ($start)
		{
			$this->start = intval($start);

			return $this;
		}


		public function limit ($limit)
		{
			$this->limit = intval($limit);

			return $this;
		}


		protected function processFields ()
		{
			if (empty($this->fields)) {
				return '';
			} else {
				$this->fields = array_map($this->fields, array($this, 'filterIdentifier'));
				return implode(', ', $this->fields);
			}
		}


		protected function processData ()
		{
			if (empty($this->data)) {
				return '';
			} else {
				$fields = array();

				foreach ($this->data as $key => $value) {
					$fields[] = $key . " = " . $this->database->quote($value);
				}

				return implode(', ', $fields);
			}
		}


		protected function processJoins ()
		{
			if (empty($this->joins)) {
				return '';
			} else {
				$joins = array();

				foreach ($this->joins as $join) {
					$joins[] = $join['type'] . " "  . $this->filterIdentifier($join['table']) . " " . $join['relation'] . " (" . $join['condition'] . ")";
				}

				return implode("\n", $joins);
			}
		}


		protected function processWhere ()
		{
			if (!$this->whereCondition->isEmpty()) {
				return "WHERE " . $this->whereCondition->getRaw();
			} else {
				return '';
			}
		}


		protected function processHaving ()
		{
			if (!$this->havingCondition->isEmpty()) {
				return "HAVING " . $this->havingCondition->getRaw();
			} else {
				return '';
			}
		}


		protected function processGroupBy ()
		{
			if (empty($this->groupBys)) {
				return '';
			} else {
				return "GROUP BY " . implode(', ', $this->groupBys);
			}
		}


		protected function processOrderBy ()
		{
			if (empty($this->orderBys)) {
				return '';
			} else {
				$sql       = "ORDER BY ";
				$orderBys = array();

				foreach ($this->orderBys as $orderBy) {
					$orderBys[] = $orderBy['field'] . ' ' . $orderBy['direction'];
				}

				return $sql.implode(',', $orderBys);
			}
		}


		protected function processLimit ()
		{
			if (strval(intval($this->limit)) === strval($this->limit)) {
				return "LIMIT ".intval($this->start).", ".intval($this->limit);
			} else {
				return '';
			}
		}


		protected function filterIdentifier ($value)
		{
			if ($value instanceof DatabaseQuery) {
				return '(' . $value->getRaw() . ')';

			} else if (strval(intval($value)) === strval($value) || $value instanceof Raw) {
				return $value;

			} else {
				return $this->database->quoteIdentifier($value);
			}
		}
	}



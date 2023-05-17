<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 *
	 */


	namespace LiftKit\Database\Query\Condition;

	use LiftKit\Database\Connection\Connection as Database;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Raw\Raw;
	use LiftKit\Database\Query\Identifier\Identifier;
	use LiftKit\Database\Query\Exception\Query as DatabaseQueryBuilderException;
	use LiftKit\Database\Exception\Database as DatabaseException;


	/**
	 * Class Condition
	 *
	 * @package LiftKit\Database\Query\Condition
	 */
	class Condition
	{
		protected $conditions = array();
		protected $database;


		public function __construct (Database $database)
		{
			$this->database = $database;
		}


		public function isEmpty ()
		{
			return (boolean)!count($this->conditions);
		}


		public function __toString ()
		{
			return $this->getRaw();
		}


		public function getRaw ()
		{
			if (! $this->isEmpty()) {
				$query = '';

				foreach ($this->conditions as $condition) {
					if ($query == '') {
						$query .= $condition['condition'];
					} else {
						$query .= ' ' . $condition['relation'] . ' ' . $condition['condition'];
					}
				}

				return $query;
			} else {
				return '';
			}
		}


		public function equal ($left, $right)
		{
			if ($this->isIsValue($right)) {
				return $this->is($left, $right);
			} else {
				$this->conditions[] = array(
					'relation'  => 'AND',
					'condition' => $this->filterLeftValue($left) . " = " . $this->filterValue($right),
				);
			}

			return $this;
		}


		public function orEqual ($left, $right)
		{
			if ($this->isIsValue($right)) {
				return $this->orIs($left, $right);
			} else {
				$this->conditions[] = array(
					'relation'  => 'OR',
					'condition' => $this->filterLeftValue($left) . " = " . $this->filterValue($right),
				);

				return $this;
			}
		}


		public function notEqual ($left, $right)
		{
			if ($this->isIsValue($right)) {
				return $this->notIs($left, $right);
			} else {
				$this->conditions[] = array(
					'relation'  => 'AND',
					'condition' => $this->filterLeftValue($left) . " <> " . $this->filterValue($right),
				);

				return $this;
			}
		}


		public function orNotEqual ($left, $right)
		{
			if ($this->isIsValue($right)) {
				return $this->orNotIs($left, $right);
			} else {
				$this->conditions[] = array(
					'relation'  => 'OR ',
					'condition' => $this->filterLeftValue($left) . " <> " . $this->filterValue($right),
				);

				return $this;
			}
		}


		public function lessThan ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($left) . " < " . $this->filterValue($right),
			);

			return $this;
		}


		public function orLessThan ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($left) . " < " . $this->filterValue($right),
			);

			return $this;
		}


		public function lessThanOrEqual ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($left) . " <= " . $this->filterValue($right),
			);

			return $this;
		}


		public function orLessThanOrEqual ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($left) . " <= " . $this->filterValue($right),
			);

			return $this;
		}


		public function greaterThan ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($left) . " > " . $this->filterValue($right),
			);

			return $this;
		}


		public function orGreaterThan ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($left) . " > " . $this->filterValue($right),
			);

			return $this;
		}


		public function greaterThanOrEqual ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($left) . " >= " . $this->filterValue($right),
			);

			return $this;
		}


		public function orGreaterThanOrEqual ($left, $right)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($left) . " >= " . $this->filterValue($right),
			);

			return $this;
		}


		public function in ($value, $values)
		{
			if (empty($values)) {
				$this->conditions[] = array(
					'relation'  => 'AND',
					'condition' => "1 = 0",
				);
			} else {
				if (is_array($values)) {
					$values = array_map(
						array(
							$this,
							'filterValue'
						),
						$values
					);

					$this->conditions[] = array(
						'relation'  => 'AND',
						'condition' => $this->filterLeftValue($value) . " IN (" . implode(', ', $values) . ")",
					);

				} else if ($values instanceof DatabaseQuery) {
					$this->conditions[] = array(
						'relation'  => 'AND',
						'condition' => $this->filterLeftValue($value) . " IN (" . $values->getRaw() . ")",
					);

				} else {
					throw new DatabaseQueryBuilderException('Invalid in condition value ' . var_export($values));
				}
			}

			return $this;
		}


		public function orIn ($value, $values)
		{
			if (empty($values)) {
				$this->conditions[] = array(
					'relation'  => 'OR',
					'condition' => "1 = 0",
				);

			} else {
				if (is_array($values)) {
					$values = array_map(
						array(
							$this,
							'filterValue'
						),
						$values
					);

					$this->conditions[] = array(
						'relation'  => 'OR',
						'condition' => $this->filterLeftValue($value) . " IN (" . implode(', ', $values) . ")",
					);

				} else if ($values instanceof DatabaseQuery) {
					$this->conditions[] = array(
						'relation'  => 'OR',
						'condition' => $this->filterLeftValue($value) . " IN (" . $values->getRaw() . ")",
					);

				} else {
					throw new DatabaseQueryBuilderException('Invalid in condition value ' . var_export($values));
				}
			}

			return $this;
		}


		public function notIn ($value, $values)
		{
			if (empty($values)) {
				$this->conditions[] = array(
					'relation'  => 'AND',
					'condition' => '1 = 1',
				);

			} else {
				if (is_array($values)) {
					$values = array_map(
						array(
							$this,
							'filterValue'
						),
						$values
					);

					$this->conditions[] = array(
						'relation'  => 'AND',
						'condition' => $this->filterLeftValue($value) . " NOT IN (" . implode(', ', $values) . ")",
					);

				} else if ($values instanceof DatabaseQuery) {
					$this->conditions[] = array(
						'relation'  => 'AND',
						'condition' => $this->filterLeftValue($value) . " NOT IN (" . $values->getRaw() . ")",
					);

				} else {
					throw new DatabaseQueryBuilderException('Invalid in condition value ' . var_export($values));
				}
			}

			return $this;
		}


		public function orNotIn ($value, $values)
		{
			if (empty($values)) {
				$this->conditions[] = array(
					'relation'  => 'OR',
					'condition' => "1 = 1",
				);
			} else {
				if (is_array($values)) {
					$values = array_map(
						array(
							$this,
							'filterValue'
						),
						$values
					);

					$this->conditions[] = array(
						'relation'  => 'OR',
						'condition' => $this->filterLeftValue($value) . " NOT IN (" . implode(', ', $values) . ")",
					);
				} else if ($values instanceof DatabaseQuery) {
					$this->conditions[] = array(
						'relation'  => 'OR',
						'condition' => $this->filterLeftValue($value) . " NOT IN (" . $values->getRaw() . ")",
					);
				} else {
					throw new DatabaseQueryBuilderException('Invalid in condition value ' . var_export($values));
				}
			}

			return $this;
		}


		public function is ($value, $boolean)
		{
			$boolean = $this->filterIsValue($boolean);

			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . ' IS ' . $boolean,
			);

			return $this;
		}


		public function orIs ($value, $boolean)
		{
			$boolean = $this->filterIsValue($boolean);

			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . ' IS ' . $boolean,
			);

			return $this;
		}


		public function notIs ($value, $boolean)
		{
			$boolean = $this->filterIsValue($boolean);

			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . ' IS NOT ' . $boolean,
			);

			return $this;
		}


		public function orNotIs ($value, $boolean)
		{
			$boolean = $this->filterIsValue($boolean);

			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . ' IS NOT ' . $boolean,
			);

			return $this;
		}


		public function like ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . " LIKE " . $this->filterString($pattern),
			);

			return $this;
		}


		public function orLike ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . " LIKE " . $this->filterString($pattern),
			);

			return $this;
		}


		public function notLike ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . " NOT LIKE " . $this->filterString($pattern),
			);

			return $this;
		}


		public function orNotLike ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . " NOT LIKE " . $this->filterString($pattern),
			);

			return $this;
		}


		public function regexp ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . " REGEXP " . $this->filterString($pattern),
			);

			return $this;
		}


		public function orRegexp ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . " REGEXP " . $this->filterString($pattern),
			);

			return $this;
		}


		public function notRegexp ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => $this->filterLeftValue($value) . " NOT REGEXP " . $this->filterString($pattern),
			);

			return $this;
		}


		public function orNotRegexp ($value, $pattern)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => $this->filterLeftValue($value) . " NOT REGEXP " . $this->filterString($pattern),
			);

			return $this;
		}


		public function condition (Condition $condition)
		{
			$this->raw($condition->getRaw());

			return $this;
		}


		public function orCondition (Condition $condition)
		{
			$this->orRaw($condition->getRaw());

			return $this;
		}


		public function notCondition (Condition $condition)
		{
			$this->notRaw($condition->getRaw());

			return $this;
		}


		public function orNotCondition (Condition $condition)
		{
			$this->orNotRaw($condition->getRaw());

			return $this;
		}


		public function raw ($condition)
		{
			$this->conditions[] = array(
				'relation'  => 'AND',
				'condition' => '(' . $condition . ')',
			);

			return $this;
		}


		public function orRaw ($condition)
		{
			$this->conditions[] = array(
				'relation'  => 'OR',
				'condition' => '(' . $condition . ')',
			);

			return $this;
		}


		public function notRaw ($condition)
		{
			$this->conditions[] = array(
				'relation'  => 'AND NOT',
				'condition' => '(' . $condition . ')',
			);

			return $this;
		}


		public function orNotRaw ($condition)
		{
			$this->conditions[] = array(
				'relation'  => 'OR NOT',
				'condition' => '(' . $condition . ')',
			);

			return $this;
		}


		public function search ($fields, $termString)
		{
			$boundary = $this->getWordBoundary();
			$condition = new self($this->database);

			$terms = preg_split('#(\s+)#', $termString);
			$terms = array_filter($terms);

			if (!empty($fields) && !empty($terms)) {
				foreach ($terms as $term) {
					$innerCondition = new self($this->database);

					foreach ($fields as $field) {
						$innerCondition->orRegexp(
							$field,
							$boundary . preg_quote($term)
						);
					}

					$condition->condition($innerCondition);
				}

				$this->condition($condition);
			} else {
				$this->raw('1 = 1');
			}

			return $this;
		}


		protected function filterString ($value)
		{
			return $this->database->quote($value);
		}


		protected function filterValue ($value)
		{
			if ($value instanceof DatabaseQuery) {
				return '(' . $value->getRaw() . ')';

			} else if ($value instanceof Identifier) {
					return (string) $value;

			} else if ($value instanceof Raw) {
					return (string) $value;

			} else {
				return $this->database->quote($value);
			}
		}


		protected function filterLeftValue ($value)
		{
			if ($value instanceof DatabaseQuery) {
				return '(' . $value->getRaw() . ')';

			} else if ($value instanceof Raw) {
				return (string) $value;

			} else {
				return $this->database->quoteIdentifier($value);
			}
		}


		protected function filterIsValue ($boolean)
		{
			if ($boolean === true) {
				$boolean = 'TRUE';

			} else if ($boolean === false) {
				$boolean = 'FALSE';

			} else if ($boolean === null) {
				$boolean = 'NULL';

			} else {
				throw new DatabaseQueryBuilderException('Invalid IS token ' . var_export($boolean, true));
			}

			return $boolean;
		}


		protected function isIsValue ($value)
		{
			if ($value === true || $value === false || $value === null) {
				return true;
			} else {
				return false;
			}
		}


		private function getWordBoundary ()
		{
			static $useAlternateWordBoundary;

			if (! isset($useAlternateWordBoundary)) {
				$sql = "
					SELECT 'word1' 
					REGEXP '[[:<:]]word1'
				";

				try {
					$useAlternateWordBoundary = (bool) $this->database->query($sql)->fetchField();
				} catch (DatabaseException $e) {
					$useAlternateWordBoundary = false;
				}
			}

			return $useAlternateWordBoundary ? '[[:<:]]' : '\\b';
		}
	}



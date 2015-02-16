<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Table;

	use LiftKit\Database\Connection\Connection as Database;
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Condition as DatabaseQueryCondition;
	use LiftKit\Database\Entity\Entity;

	use LiftKit\Database\Exception\Database as DatabaseException;
	use LiftKit\Database\Exception\QueryBuilder as QueryBuilderException;


	/**
	 * Class Table
	 *
	 * @package LiftKit\Database\Table
	 */
	class Table
	{
		const ONE_TO_MANY  = 1;
		const MANY_TO_ONE  = 2;
		const MANY_TO_MANY = 3;
		const ONE_TO_ONE   = 4;

		/**
		 * @var Database
		 */
		protected $database;

		/**
		 * @var string|null
		 */
		protected $table;

		/**
		 * @var array[]
		 */
		protected $relations = array();

		/**
		 * @var null|string
		 */
		protected $entityRule = null;


		/**
		 * @param Database    $database
		 * @param string|null $table
		 */
		public function __construct (Database $database, $table = null)
		{
			$this->database = $database;
			$this->setTable($table);
		}


		/**
		 * @return string|null
		 */
		public function getTable ()
		{
			return $this->table;
		}


		/**
		 * @param string $table
		 *
		 * @return self
		 */
		public function setTable ($table)
		{
			$this->table = $table;

			return $this;
		}


		/**
		 * @param string $entityRule
		 *
		 * @return $this
		 */
		public function setEntity ($entityRule)
		{
			$this->entityRule = $entityRule;

			return $this;
		}


		/**
		 * @param string $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws DatabaseException
		 */
		public function oneToOne ($table, $foreignKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($foreignKey)) {
				$foreignKey = $this->database->primaryKey($table);
			}

			if (is_null($key)) {
				$key = $foreignKey;
			}

			if (is_null($relationIdentifier)) {
				$relationIdentifier = $table;
			}

			if (isset($this->relations[$relationIdentifier])) {
				throw new DatabaseException('A relation with the identifier `'.$relationIdentifier.'` already exists.');
			}

			$this->relations[$relationIdentifier] = array(
				'type'        => self::ONE_TO_ONE,
				'table'       => $table,
				'key'         => $key,
				'foreign_key' => $foreignKey,
			);

			return $this;
		}


		/**
		 * @param string     $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws DatabaseException
		 */
		public function oneToMany ($table, $foreignKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($foreignKey)) {
				$foreignKey = $this->getPrimaryKey();
			}

			if (is_null($key)) {
				$key = $foreignKey;
			}

			if (is_null($relationIdentifier)) {
				$relationIdentifier = $table;
			}

			if (isset($this->relations[$relationIdentifier])) {
				throw new DatabaseException('A relation with the identifier `'.$relationIdentifier.'` already exists.');
			}

			$this->relations[$relationIdentifier] = array(
				'type'        => self::ONE_TO_MANY,
				'table'       => $table,
				'key'         => $key,
				'foreign_key' => $foreignKey,
			);

			return $this;
		}


		/**
		 * @return string
		 */
		public function getPrimaryKey ()
		{
			return $this->database->primaryKey($this->table);
		}


		/**
		 * @param string     $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws DatabaseException
		 */
		public function manyToOne ($table, $foreignKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($foreignKey)) {
				$foreignKey = $this->database->primaryKey($table);
			}

			if (is_null($key)) {
				$key = $this->database->primaryKey($table);
			}

			if (is_null($relationIdentifier)) {
				$relationIdentifier = $table;
			}

			if (isset($this->relations[$relationIdentifier])) {
				throw new DatabaseException('A relation with the identifier `'.$relationIdentifier.'` already exists.');
			}

			$this->relations[$relationIdentifier] = array(
				'type'        => self::MANY_TO_ONE,
				'table'       => $table,
				'key'         => $key,
				'foreign_key' => $foreignKey,
			);

			return $this;
		}


		/**
		 * @param string     $table
		 * @param string     $relationalTable
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws DatabaseException
		 */
		public function manyToMany ($table, $relationalTable, $foreignKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($foreignKey)) {
				$foreignKey = $this->database->primaryKey($table);
			}

			if (is_null($key)) {
				$key = $this->database->primaryKey($this->table);
			}

			if (is_null($relationIdentifier)) {
				$relationIdentifier = $table;
			}

			if (isset($this->relations[$relationIdentifier])) {
				throw new DatabaseException('A relation with the identifier `'.$relationIdentifier.'` already exists.');
			}

			$this->relations[$relationIdentifier] = array(
				'type'             => self::MANY_TO_MANY,
				'relational_table' => $relationalTable,
				'table'            => $table,
				'key'              => $key,
				'foreign_key'      => $foreignKey,
			);

			return $this;
		}


		/**
		 * @param array|object|Entity $row
		 * @param bool                $filterColumns
		 *
		 * @return int
		 */
		public function insertRow ($row, $filterColumns = true)
		{
			if ($filterColumns) {
				$row = $this->filterColumns($row);
			}

			$this->database->createQuery()
				->insert()
				->into($this->table)
				->set($row)
				->execute();

			return $this->database->insertId();
		}


		/**
		 * @param array|object|Entity $row
		 * @param bool $filterColumns
		 *
		 * @return int
		 */
		public function insertUpdateRow ($row, $filterColumns = true)
		{
			if ($filterColumns) {
				$row = $this->filterColumns($row);
			}

			$this->database->createQuery()
				->insertUpdate()
				->into($this->table)
				->set($row)
				->execute();

			return $this->database->insertId();
		}


		/**
		 * @param string     $relationIdentifier
		 * @param int     $id
		 * @param array|object|Entity $row
		 * @param null|string $foreignKey
		 *
		 * @return int
		 * @throws DatabaseException
		 */
		public function insertChild ($relationIdentifier, $id, $row, $foreignKey = null)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY || $relation['type'] == self::MANY_TO_MANY || $relation['type'] == self::ONE_TO_ONE) {
				if (is_null($foreignKey)) {
					$foreignKey = $this->getPrimaryKey();
				}

				$row[$foreignKey] = $id;

				$this->database->createQuery()
					->insert()
					->into($relation['table'])
					->set($row)
					->execute();

				return $this->database->insertId();
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param $relation_identifier
		 *
		 * @return array
		 */
		public function getRelation ($relation_identifier)
		{
			return $this->relations[$relation_identifier];
		}


		/**
		 * @param array|object|Entity $row
		 * @param bool $filterColumns
		 *
		 * @throws QueryBuilderException
		 */
		public function updateRow ($row, $filterColumns = true)
		{
			if ($filterColumns) {
				$row = $this->filterColumns($row);
			}

			$this->database->createQuery()
				->update()
				->table($this->table)
				->set($row)
				->where(
					$this->database->createCondition()
						->equal($this->getPrimaryKey(), $row[$this->getPrimaryKey()])
				)
				->execute();
		}


		/**
		 * @param int $id
		 *
		 * @throws QueryBuilderException
		 */
		public function deleteRow ($id)
		{
			$this->database->createQuery()
				->delete()
				->from($this->table)
				->where(
					$this->database->createCondition()
						->equal($this->getPrimaryKey(), $id)
				)
				->execute();
		}


		/**
		 * @param null|DatabaseQuery $inputQuery
		 *
		 * @return DatabaseResult|null
		 * @throws QueryBuilderException
		 */
		public function getRows ($inputQuery = null)
		{
			$query = $this->database->createQuery();

			foreach ($this->getSingleParentRelations() as $relation) {
				$key =
					strstr($relation['key'], '.')
						? $this->database->backtickQuote($relation['key'])
						: $this->database->backtickQuote($this->table.'.'.$relation['key']);

				$foreign_key =
					strstr($relation['foreign_key'], '.')
						? $this->database->backtickQuote($relation['foreign_key'])
						: $this->database->backtickQuote($relation['table'].'.'.$relation['foreign_key']);

				$query->leftJoin(
					$relation['table'],
					$query->createCondition()
						->equal($key, $foreign_key)
				)
					->fields(array($this->database->backtickQuote($relation['table']).'.*'));
			}

			$query->select()
				->from($this->table)
				->fields(array($this->database->backtickQuote($this->table).'.*'));

			if (!is_null($inputQuery)) {
				$query->composeWith($inputQuery);
			}

			$query->setEntity($this->entityRule);

			return $query->execute();
		}


		/**
		 * @param string     $relationIdentifier
		 * @param int     $id
		 * @param null $inputQuery
		 *
		 * @return DatabaseResult|null
		 * @throws DatabaseException
		 * @throws QueryBuilderException
		 */
		public function getChildren ($relationIdentifier, $id, $inputQuery = null)
		{
			$relation = $this->getRelation($relationIdentifier);
			$query    = $this->database->createQuery();

			if ($relation['type'] == self::ONE_TO_MANY) {
				$query->select()
					->fields(array('*'))
					->from($relation['table'])
					->where(
						$query->createCondition()
							->equal($relation['foreign_key'], $id)
					);
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				$query->select()
					->fields(array('*'))
					->from($relation['relational_table'])
					->leftJoinUsing(
						$relation['table'],
						$relation['foreign_key']
					)
					->where(
						$query->createCondition()
							->equal($relation['relational_table'].'.'.$relation['key'], $id)
					);
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}

			if (!is_null($query)) {
				$query->composeWith($inputQuery);
			}

			return $query->execute();
		}


		/**
		 * @param string     $relationIdentifier
		 * @param int     $id
		 * @param null|DatabaseQuery $inputQuery
		 *
		 * @return \LiftKit\Database\Entity\Entity|mixed|null
		 * @throws DatabaseException
		 * @throws QueryBuilderException
		 */
		public function getParent ($relationIdentifier, $id, $inputQuery = null)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] != self::MANY_TO_ONE && $relation['type'] == self::ONE_TO_ONE) {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}

			$query = $this->database->createQuery();

			if ($relation['type'] == self::MANY_TO_ONE) {
				$row = $this->getRow($id);

				$query->select()
					->from($relation['table'])
					->where(
						$query->createCondition()
							->equal($relation['foreign_key'], $row[$relation['key']])
					);
			} else if ($relation['type'] == self::ONE_TO_ONE) {
				$row = $this->getRow($id);

				$query->select()
					->from($relation['table'])
					->where(
						$query->createCondition()
							->equal($relation['foreign_key'], $row[$relation['key']])
					);
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}

			$query->fields(array('*'));

			if (!is_null($inputQuery)) {
				$query->composeWith($inputQuery);
			}

			return $query->execute()
				->fetchRow();
		}


		/**
		 * @param int $id
		 *
		 * @return Entity|mixed|null
		 * @throws QueryBuilderException
		 */
		public function getRow ($id)
		{
			$query = $this->database->createQuery();

			foreach ($this->getSingleParentRelations() as $relation) {
				$key =
					strstr($relation['key'], '.')
						? $this->database->backtickQuote($relation['key'])
						: $this->database->backtickQuote($this->table.'.'.$relation['key']);

				$foreign_key =
					strstr($relation['foreign_key'], '.')
						? $this->database->backtickQuote($relation['foreign_key'])
						: $this->database->backtickQuote($relation['table'].'.'.$relation['foreign_key']);

				$query->leftJoin(
					$relation['table'],
					$query->createCondition()
						->equal($key, $foreign_key)
				)
					->fields(array($this->database->backtickQuote($relation['table']).'.*'));
			}

			$result = $query
				->select()
				->from($this->table)
				->fields(array($this->database->backtickQuote($this->table).'.*'))
				->where(
					$query->createCondition()
						->equal($this->table.'.'.$this->getPrimaryKey(), $id)
				)
				->limit(1)
				->setEntity($this->entityRule)
				->execute();

			return $result->fetchRow();
		}


		/**
		 * @param $columnName
		 * @param $value
		 *
		 * @return \LiftKit\Database\Entity\Entity|mixed|null
		 */
		public function getRowByValue($columnName, $value)
		{
			$query = $this->database->createQuery()
				->whereEqual($columnName, $value)
				->limit(1);

			return $this->getRows($query)->fetchRow();
		}


		/**
		 * @param      $relationIdentifier
		 * @param      $id
		 * @param null $input_query
		 *
		 * @return DatabaseResult
		 * @throws Database
		 */

		public function getParents ($relationIdentifier, $id, $input_query = null)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			$query = $this->database->createQuery();

			if ($relation['type'] == self::MANY_TO_MANY) {
				$query->select()
					->fields(array('*'))
					->from($relation['relational_table'])
					->leftJoinUsing(
						$relation['table'],
						$relation['foreign_key']
					)
					->where(
						$query->createCondition()
							->equal($relation['key'], $id)
					);
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}

			if (!is_null($input_query)) {
				$query->composeWith($input_query);
			}

			return $query->execute();
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param null   $query
		 *
		 * @throws DatabaseException
		 */
		public function getSiblings ($relationIdentifier, $id, $query = null)
		{
			throw new DatabaseException(__METHOD__.' has not yet been implemented.');
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param array  $childIds
		 * @param bool   $subtractive
		 *
		 * @throws DatabaseException
		 */
		public function assignChildren ($relationIdentifier, $id, $childIds, $subtractive = true)
		{
			$relation  = $this->getRelation($relationIdentifier);
			$childIds = (array)$childIds;

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY) {
				$this->database->createQuery()
					->update()
					->table($relation['table'])
					->set(
						array(
							$relation['key'] => $id,
						)
					)
					->whereIn($this->database->primaryKey($relation['table']), $childIds)
					->execute();

				if ($subtractive) {
					$this->database->createQuery()
						->update()
						->table($relation['table'])
						->set(
							array(
								$relation['key'] => null,
							)
						)
						->whereEqual($relation['key'], $id)
						->whereNotIn($this->database->primaryKey($relation['table']), $childIds)
						->execute();
				}
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation['relational_table'])
						->whereEqual($relation['key'], $id)
						->whereNotIn($relation['foreign_key'], $childIds)
						->execute();
				}

				$query = $this->database->createQuery();

				$assigned_ids =
					$query->select($relation['foreign_key'])
						->from($relation['relational_table'])
						->whereEqual($relation['key'], $id)
						->execute()
						->fetchColumn();

				$new_ids = array_diff($childIds, $assigned_ids);

				foreach ($new_ids as $new_id) {
					$this->database->createQuery()
						->insert()
						->into($relation['relational_table'])
						->set(
							array(
								$relation['key']         => $id,
								$relation['foreign_key'] => $new_id,
							)
						)
						->execute();
				}
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int $id
		 * @param int $childId
		 *
		 * @throws DatabaseException
		 */
		public function assignChild ($relationIdentifier, $id, $childId)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY) {
				$this->database->createQuery()
					->update()
					->table($relation['table'])
					->set(
						array(
							$relation['key'] => $id,
						)
					)
					->whereEqual($relation['foreign_key'], $childId)
					->execute();
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				$this->database->createQuery()
					->insertIgnore()
					->into($relation['relational_table'])
					->set(
						array(
							$relation['key']         => $id,
							$relation['foreign_key'] => $childId,
						)
					)
					->execute();
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int $id
		 * @param array $parentIds
		 *
		 * @throws DatabaseException
		 */
		public function assignParents ($relationIdentifier, $id, $parentIds)
		{
			$relation   = $this->getRelation($relationIdentifier);
			$parentIds = (array)$parentIds;

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::MANY_TO_MANY) {
				$query = $this->database->createQuery();

				$query->delete()
					->table($relation['relational_table'])
					->whereEqual($relation['key'], $id)
					->whereNotIn($relation['foreign_key'], $parentIds)
					->execute();

				$query = $this->database->createQuery();

				$assigned_ids =
					$query->select($relation['foreign_key'])
						->from($relation['relational_table'])
						->whereEqual($relation['key'], $id)
						->execute()
						->fetchColumn();

				$new_ids = array_diff($parentIds, $assigned_ids);

				foreach ($new_ids as $new_id) {
					$this->database->createQuery()
						->insert()
						->into($relation['relational_table'])
						->set(
							array(
								$relation['key']         => $id,
								$relation['foreign_key'] => $new_id,
							)
						)
						->execute();
				}
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param string  $relationIdentifier
		 * @param int     $id
		 * @param array[] $children
		 * @param bool    $subtractive
		 * @param bool    $isRelation if true, data in children is to be inserted/updated in the relational
		 *                            table, otherwise it will be used to update/insert into the child table:
		 *                            this field is ignored for one-to-many relationships
		 *
		 * @throws DatabaseException
		 */
		public function setChildren ($relationIdentifier, $id, $children, $subtractive = true, $isRelation = true)
		{
			$relation = $this->getRelation($relationIdentifier);
			$children = (array)$children;

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY) {
				$child_ids = array();
				$primaryKey = $this->database->primaryKey($relation['table']);
				$parentPrimaryKey = $this->getPrimaryKey();

				foreach ($children as $child) {
					if ($key = $child[$primaryKey]) {
						$child_ids[] = $key;
						$child[$parentPrimaryKey] = $id;

						$this->database->createQuery()
							->update()
							->table($relation['table'])
							->set($child)
							->whereEqual($primaryKey, $key)
							->execute();
					} else {
						$child[$parentPrimaryKey] = $id;

						$child_ids[] = $this->database->createQuery()
							->insertUpdate()
							->into($relation['table'])
							->set($child)
							->execute();
					}
				}

				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation['table'])
						->whereEqual($parentPrimaryKey, $id)
						->whereNotIn($primaryKey, $child_ids)
						->execute();
				}
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				$child_ids = array();

				if ($isRelation) {
					$table = $relation['relational_table'];
					$keyField = $this->database->primaryKey($table);
				} else {
					$table = $relation['table'];
					$keyField = $relation['foreign_key'];
				}

				foreach ($children as $child) {
					if ($isRelation) {
						$key = $child[$keyField];
						$child[$relation['key']] = $id;
					} else {
						$key = $child[$keyField];
					}

					if ($key) {
						$child_ids[] = $key;

						$this->database->createQuery()
							->update()
							->table($table)
							->set($child)
							->whereEqual($keyField, $key)
							->execute();
					} else {
						$child_ids[] = $this->database->createQuery()
							->insertUpdate()
							->into($table)
							->set($child)
							->execute();
					}
				}

				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation['relational_table'])
						->whereEqual($relation['key'], $id)
						->whereNotIn($relation['foreign_key'], $child_ids)
						->execute();
				}
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int $id
		 * @param int $parentId
		 *
		 * @throws DatabaseException
		 */
		public function assignParent ($relationIdentifier, $id, $parentId)
		{
			throw new DatabaseException(__METHOD__.' has not yet been implemented.');
		}


		/**
		 * @param string $relationIdentifier
		 * @param int $id
		 * @param int $childId
		 *
		 * @throws DatabaseException
		 */
		public function unassignChild ($relationIdentifier, $id, $childId)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new DatabaseException('Invalid relation `'.$relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY) {
				$this->database->createQuery()
					->update()
					->table($relation['table'])
					->set(
						array(
							$relation['key'] => null,
						)
					)
					->whereEqual($relation['key'], $id)
					->whereEqual($relation['foreign_key'], $childId)
					->execute();
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				$this->database->createQuery()
					->delete()
					->from($relation['relational_table'])
					->whereEqual($relation['key'], $id)
					->whereEqual($relation['foreign_key'], $childId)
					->execute();
			} else {
				throw new DatabaseException('Invalid relation type `'.$relation['type'].'`');
			}
		}


		/**
		 * @param $set
		 * @param $column
		 *
		 * @return array
		 */
		protected function extractColumn ($set, $column)
		{
			$return = array();

			foreach ($set as $row) {
				$return[] = $row[$column];
			}

			return $return;
		}


		/**
		 * @param $row
		 *
		 * @return array
		 */
		protected function filterColumns ($row)
		{
			$columns = $this->database->getFields($this->table);

			return array_intersect_key($row, array_flip($columns));
		}


		/**
		 * @return array[]
		 */
		protected function getSingleParentRelations ()
		{
			$tables = array();

			foreach ($this->relations as $relation) {
				if ($relation['type'] == self::MANY_TO_ONE || $relation['type'] == self::ONE_TO_ONE) {
					$tables[] = $relation;
				}
			}

			return $tables;
		}
	}



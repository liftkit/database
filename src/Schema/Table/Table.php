<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Schema\Table;

	use LiftKit\Database\Connection\Connection as Database;
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Condition as DatabaseQueryCondition;
	use LiftKit\Database\Entity\Entity;

	use LiftKit\Database\Schema\Table\Exception\Relation as RelationException;
	use LiftKit\Database\Query\Exception\Query as QueryBuilderException;


	/**
	 * Class Table
	 *
	 * @package LiftKit\Database\Table
	 */
	class Table
	{
		const ONE_TO_MANY = 1;
		const MANY_TO_ONE = 2;
		const MANY_TO_MANY = 3;
		const ONE_TO_ONE = 4;

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
		public function __construct (Database $database, $table)
		{
			$this->database = $database;
			$this->table    = $table;
		}


		/**
		 * @return string|null
		 */
		public function getTable ()
		{
			return $this->table;
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
		 * @return string
		 */
		public function getPrimaryKey ()
		{
			return $this->database->primaryKey($this->table);
		}


		/**
		 * @param string      $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
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
				throw new RelationException('A relation with the identifier `' . $relationIdentifier . '` already exists.');
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
		 * @param string      $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
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
				throw new RelationException('A relation with the identifier `' . $relationIdentifier . '` already exists.');
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
		 * @param string      $table
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
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
				throw new RelationException('A relation with the identifier `' . $relationIdentifier . '` already exists.');
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
		 * @param string      $table
		 * @param string      $relationalTable
		 * @param null|string $foreignKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
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
				throw new RelationException('A relation with the identifier `' . $relationIdentifier . '` already exists.');
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
		 * @param null|DatabaseQuery $inputQuery
		 *
		 * @return DatabaseResult|null
		 * @throws QueryBuilderException
		 */
		public function getRows ($inputQuery = null)
		{
			$query = $this->database->createQuery();

			foreach ($this->getSingleParentRelations() as $relation) {
				$key = strstr($relation['key'], '.')
					? $relation['key']
					: $this->table . '.' . $relation['key'];

				$foreignKey = strstr($relation['foreign_key'], '.')
					? $relation['foreign_key']
					: $relation['table'] . '.' . $relation['foreign_key'];

				$query->leftJoin(
					$relation['table'],
					$this->database->createCondition()->equal(
						$key,
						$this->database->quoteIdentifier($foreignKey)
					)
				)
					->fields(array($relation['table'] . '.*'));
			}

			$query->select()
				->from($this->table)
				->fields(array($this->table . '.*'));

			if (!is_null($inputQuery)) {
				$query->composeWith($inputQuery);
			}

			$query->setEntity($this->entityRule);

			return $query->execute();
		}


		/**
		 * @param int $id
		 *
		 * @return Entity|mixed|null
		 * @throws QueryBuilderException
		 */
		public function getRow ($id)
		{
			$query = $this->database->createQuery()
				->whereEqual($this->table . '.' . $this->getPrimaryKey(), $id)
				->limit(1);

			return $this->getRows($query)->fetchRow();
		}


		/**
		 * @param $columnName
		 * @param $value
		 *
		 * @return \LiftKit\Database\Entity\Entity|mixed|null
		 */
		public function getRowByValue ($columnName, $value)
		{
			$query = $this->database->createQuery()
				->whereEqual($columnName, $value)
				->limit(1);

			return $this->getRows($query)
				->fetchRow();
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
		 * @param bool                $filterColumns
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
		 * @param array|object|Entity $row
		 * @param bool                $filterColumns
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
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param null   $inputQuery
		 *
		 * @return DatabaseResult|null
		 * @throws RelationException
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
						$this->database->createCondition()
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
						$this->database->createCondition()
							->equal($relation['relational_table'] . '.' . $relation['key'], $id)
					);
			} else {
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}

			if (!is_null($query)) {
				$query->composeWith($inputQuery);
			}

			return $query->execute();
		}


		/**
		 * @param string $relationIdentifier
		 * @param int $id
		 * @param int $childId
		 *
		 * @return Entity|mixed|null
		 * @throws RelationException
		 */
		public function getChild ($relationIdentifier, $id, $childId)
		{
			$relation = $this->getRelation($relationIdentifier);

			$query = $this->database->createQuery()
				->whereEqual($this->database->primaryKey($relation['table']), $childId);

			return $this->getChildren($relationIdentifier, $id, $query)->fetchRow();
		}


		/**
		 * @param string              $relationIdentifier
		 * @param int                 $id
		 * @param array|object|Entity $row
		 * @param null|string         $foreignKey
		 *
		 * @return int
		 * @throws RelationException
		 */
		public function insertChild ($relationIdentifier, $id, $row, $foreignKey = null)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY || $relation['type'] == self::ONE_TO_ONE) {
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

			} else if ($relation['type'] == self::MANY_TO_MANY) {
				if (is_null($foreignKey)) {
					$foreignKey = $this->getPrimaryKey();
				}

				$childId = $this->database->createQuery()
					->insert()
					->into($relation['table'])
					->set($row)
					->execute();

				$relation = array(
					$foreignKey => $id,
					$this->database->getPrimaryKey($relation['table']) => $childId,
				);

				$this->database->createQuery()
					->insert()
					->into($relation['relational_table'])
					->set($relation)
					->execute();

				return $childId;

			} else {
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param array  $childIds
		 * @param bool   $subtractive
		 *
		 * @throws RelationException
		 */
		public function assignChildren ($relationIdentifier, $id, $childIds, $subtractive = true)
		{
			$relation = $this->getRelation($relationIdentifier);
			$childIds = (array)$childIds;

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
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

				$assignedIds =
					$query->select($relation['foreign_key'])
						->from($relation['relational_table'])
						->whereEqual($relation['key'], $id)
						->execute()
						->fetchColumn();

				$newIds = array_diff($childIds, $assignedIds);

				foreach ($newIds as $new_id) {
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
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param int    $childId
		 *
		 * @throws RelationException
		 */
		public function assignChild ($relationIdentifier, $id, $childId)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
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
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param int    $childId
		 *
		 * @throws RelationException
		 */
		public function unassignChild ($relationIdentifier, $id, $childId)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
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
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
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
		 * @throws RelationException
		 */
		public function setChildren ($relationIdentifier, $id, $children, $subtractive = true, $isRelation = true)
		{
			$relation = $this->getRelation($relationIdentifier);
			$children = (array)$children;

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation['type'] == self::ONE_TO_MANY) {
				$childIds        = array();
				$primaryKey       = $this->database->primaryKey($relation['table']);
				$parentPrimaryKey = $this->getPrimaryKey();

				foreach ($children as $child) {
					if ($key = $child[$primaryKey]) {
						$childIds[]               = $key;
						$child[$parentPrimaryKey] = $id;

						$this->database->createQuery()
							->insertUpdate()
							->table($relation['table'])
							->set($child)
							->whereEqual($primaryKey, $key)
							->execute();
					} else {
						$child[$parentPrimaryKey] = $id;

						$childIds[] = $this->database->createQuery()
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
						->whereNotIn($primaryKey, $childIds)
						->execute();
				}
			} else if ($relation['type'] == self::MANY_TO_MANY) {
				$childIds = array();

				if ($isRelation) {
					$table    = $relation['relational_table'];
					$keyField = $this->database->primaryKey($table);
				} else {
					$table    = $relation['table'];
					$keyField = $relation['foreign_key'];
				}

				foreach ($children as $child) {
					if ($isRelation) {
						$key                     = $child[$keyField];
						$child[$relation['key']] = $id;
					} else {
						$key = $child[$keyField];
					}

					if ($key) {
						$childIds[] = $key;

						$this->database->createQuery()
							->insertUpdate()
							->table($table)
							->set($child)
							->whereEqual($keyField, $key)
							->execute();
					} else {
						$childIds[] = $this->database->createQuery()
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
						->whereNotIn($relation['foreign_key'], $childIds)
						->execute();
				}
			} else {
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}
		}


		/**
		 * @param      $relationIdentifier
		 * @param      $id
		 * @param null $inputQuery
		 *
		 * @return DatabaseResult
		 * @throws Database
		 */

		public function getParents ($relationIdentifier, $id, $inputQuery = null)
		{
			return $this->getChildren($relationIdentifier, $id, $inputQuery);
		}


		/**
		 * @param string             $relationIdentifier
		 * @param int                $id
		 * @param null|DatabaseQuery $inputQuery
		 *
		 * @return \LiftKit\Database\Entity\Entity|mixed|null
		 * @throws RelationException
		 * @throws QueryBuilderException
		 */
		public function getParent ($relationIdentifier, $id, $inputQuery = null)
		{
			$relation = $this->getRelation($relationIdentifier);

			if (! $relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation['type'] != self::MANY_TO_ONE && $relation['type'] != self::ONE_TO_ONE) {
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}

			$row = $this->getRow($id);

			$query = $this->database->createQuery()
				->select()
				->fields(array('*'))
				->from($relation['table'])
				->where(
					$this->database->createCondition()
						->equal($relation['foreign_key'], $row[$relation['key']])
				);

			if (!is_null($inputQuery)) {
				$query->composeWith($inputQuery);
			}

			return $query->execute()->fetchRow();
		}


		/**
		 * @param string $relationIdentifier
		 * @param int    $id
		 * @param array  $parentIds
		 *
		 * @throws RelationException
		 */
		public function assignParents ($relationIdentifier, $id, $parentIds)
		{
			$this->assignChildren($relationIdentifier, $id, $parentIds);
		}


		/**
		 * @param $relationIdentifier
		 *
		 * @return array
		 */
		protected function getRelation ($relationIdentifier)
		{
			return $this->relations[$relationIdentifier];
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
			$columns = $this->database->getFields($this->table)->fetchColumn('Field');

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



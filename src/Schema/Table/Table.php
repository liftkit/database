<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Schema\Table;

	use LiftKit\Database\Schema\Schema;
	use LiftKit\Database\Connection\Connection as Database;
	use LiftKit\Database\Result\Result as DatabaseResult;
	use LiftKit\Database\Query\Query as DatabaseQuery;
	use LiftKit\Database\Query\Condition as DatabaseQueryCondition;
	use LiftKit\Database\Entity\Entity;

	use LiftKit\Database\Schema\Table\Relation\Relation;
	use LiftKit\Database\Schema\Table\Relation\OneToOne;
	use LiftKit\Database\Schema\Table\Relation\OneToMany;
	use LiftKit\Database\Schema\Table\Relation\ManyToOne;
	use LiftKit\Database\Schema\Table\Relation\ManyToMany;

	use LiftKit\Database\Schema\Table\Exception\Relation as RelationException;
	use LiftKit\Database\Query\Exception\Query as QueryBuilderException;

	use LiftKit\Database\Query\Exception\UnsupportedFeature;


	/**
	 * Class Table
	 *
	 * @package LiftKit\Database\Table
	 */
	class Table
	{
		/**
		 * @var Database
		 */
		protected $database;


		/**
		 * @var Schema
		 */
		protected $schema;

		/**
		 * @var string|null
		 */
		protected $table;

		/**
		 * @var Relation[]
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
		public function __construct (Database $database, Schema $schema, $table)
		{
			$this->database = $database;
			$this->schema   = $schema;
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
			return $this->primaryKey();
		}


		/**
		 * @return string
		 * @deprecated
		 */
		public function primaryKey ()
		{
			return $this->database->primaryKey($this->table);
		}


		/**
		 * @param string      $relatedTable
		 * @param null|string $relatedKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
		 */
		public function oneToOne ($relatedTable, $relatedKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($relationIdentifier)) {
				$relationIdentifier = $relatedTable;
			}

			$this->relations[$relationIdentifier] = new OneToOne(
				$this,
				$this->schema->getTable($relatedTable, true),
				$key,
				$relatedKey
			);

			return $this;
		}


		/**
		 * @param string      $relatedTable
		 * @param null|string $relatedKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
		 */
		public function oneToMany ($relatedTable, $relatedKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($relationIdentifier)) {
				$relationIdentifier = $relatedTable;
			}

			$this->relations[$relationIdentifier] = new OneToMany(
				$this,
				$this->schema->getTable($relatedTable, true),
				$key,
				$relatedKey
			);

			return $this;
		}


		/**
		 * @param string      $relatedTable
		 * @param null|string $relatedKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
		 */
		public function manyToOne ($relatedTable, $relatedKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($relationIdentifier)) {
				$relationIdentifier = $relatedTable;
			}

			$this->relations[$relationIdentifier] = new ManyToOne(
				$this,
				$this->schema->getTable($relatedTable, true),
				$key,
				$relatedKey
			);

			return $this;
		}


		/**
		 * @param string      $relatedTable
		 * @param string      $relationalTable
		 * @param null|string $relatedKey
		 * @param null|string $key
		 * @param null|string $relationIdentifier
		 *
		 * @return $this
		 * @throws RelationException
		 */
		public function manyToMany ($relatedTable, $relationalTable, $relatedKey = null, $key = null, $relationIdentifier = null)
		{
			if (is_null($relationIdentifier)) {
				$relationIdentifier = $relatedTable;
			}

			$this->relations[$relationIdentifier] = new ManyToMany(
				$this,
				$this->schema->getTable($relatedTable, true),
				$this->schema->getTable($relationalTable, true),
				$key,
				$relatedKey
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
				$key = strstr($relation->getRelatedKey(), '.')
					? $relation->getRelatedKey()
					: $this->table . '.' . $relation->getRelatedKey();

				$foreignKey = strstr($relation->getKey(), '.')
					? $relation->getKey()
					: $relation->getRelatedTable() . '.' . $relation->getKey();

				$query->leftJoin(
					$relation->getRelatedTable(),
					$this->database->createCondition()->equal(
						$key,
						$this->database->quoteIdentifier($foreignKey)
					)
				)
					->fields(array($relation->getRelatedTable() . '.*'));
			}

			$query->select()
				->from($this->table)
				->fields(array($this->table . '.*'));

			$query->setEntity($this->entityRule);

			if (! is_null($inputQuery)) {
				$query->composeWith($inputQuery);
			}

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

			return $this->database->createQuery()
				->insert()
				->into($this->table)
				->set($row)
				->execute();
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
				$row = $this->filterColumns($row, false);
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
			$id = $row[$this->getPrimaryKey()];

			if ($filterColumns) {
				$row = $this->filterColumns($row);
			}

			$this->database->createQuery()
				->update()
				->table($this->table)
				->set($row)
				->where(
					$this->database->createCondition()
						->equal($this->getPrimaryKey(), $id)
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

			if ($relation instanceof OneToMany) {
				$query->whereEqual($relation->getRelatedTable() . '.' . $relation->getKey(), $id);

			} else if ($relation instanceof ManyToMany) {
				$query->addField($relation->getRelationalTable() . '.*')
					->leftJoinEqual(
						$relation->getRelationalTable(),
						$relation->getRelationalTable() . '.' .$relation->getRelatedKey(),
						$relation->getRelatedTable() . '.' . $relation->getRelatedKey()
					)
					->whereEqual($relation->getRelationalTable() . '.' . $relation->getKey(), $id);

			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
			}

			$query->composeWith($inputQuery);

			return $relation->getRelatedTableObject()->getRows($query);
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

			$query = $this->database->createQuery()->whereEqual(
				$relation->getRelatedTable() . '.' . $this->database->primaryKey($relation->getRelatedTable()),
				$childId
			);

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

			if (! $relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation instanceof OneToMany || $relation instanceof OneToOne) {
				if (is_null($foreignKey)) {
					$foreignKey = $this->getPrimaryKey();
				}

				$row[$foreignKey] = $id;

				$this->database->createQuery()
					->insert()
					->into($relation->getRelatedTable())
					->set($row)
					->execute();

				return $this->database->insertId();

			} else if ($relation instanceof ManyToMany) {
				if (is_null($foreignKey)) {
					$foreignKey = $this->getPrimaryKey();
				}

				$childId = $this->database->createQuery()
					->insert()
					->into($relation->getRelatedTable())
					->set($row)
					->execute();

				$data = array(
					$foreignKey => $id,
					$this->database->primaryKey($relation->getRelatedTable()) => $childId,
				);

				$this->database->createQuery()
					->insert()
					->into($relation->getRelationalTable())
					->set($data)
					->execute();

				return $childId;

			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
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

			if (! $relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation instanceof OneToMany) {
				$this->database->createQuery()
					->update()
					->table($relation->getRelatedTable())
					->set(
						array(
							$relation->getRelatedKey() => $id,
						)
					)
					->whereIn($this->database->primaryKey($relation->getRelatedTable()), $childIds)
					->execute();

				if ($subtractive) {
					$this->database->createQuery()
						->update()
						->table($relation->getRelatedTable())
						->set(
							array(
								$relation->getRelatedKey() => null,
							)
						)
						->whereEqual($relation->getRelatedKey(), $id)
						->whereNotIn($this->database->primaryKey($relation->getRelatedTable()), $childIds)
						->execute();
				}
			} else if ($relation instanceof ManyToMany) {
				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation->getRelationalTable())
						->whereEqual($relation->getKey(), $id)
						->whereNotIn($relation->getRelatedKey(), $childIds)
						->execute();
				}

				$query = $this->database->createQuery();

				$assignedIds =
					$query->select($relation->getRelatedKey())
						->from($relation->getRelationalTable())
						->whereEqual($relation->getKey(), $id)
						->execute()
						->fetchColumn();

				$newIds = array_diff($childIds, $assignedIds);

				foreach ($newIds as $newId) {
					$this->database->createQuery()
						->insert()
						->into($relation->getRelationalTable())
						->set(
							array(
								$relation->getKey()        => $id,
								$relation->getRelatedKey() => $newId,
							)
						)
						->execute();
				}
			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
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

			if ($relation instanceof OneToMany) {
				$this->database->createQuery()
					->update()
					->table($relation->getRelatedTable())
					->set(
						array(
							$relation->getRelatedKey() => $id,
						)
					)
					->whereEqual($relation->getKey(), $childId)
					->execute();
			} else if ($relation instanceof ManyToMany) {
				if (! $this->getChild($relationIdentifier, $id, $childId)) {
					$this->database->createQuery()
						->insert()
						->into($relation->getRelationalTable())
						->set(
							array(
								$relation->getKey()         => $id,
								$relation->getRelatedKey()  => $childId,
							)
						)
						->execute();
				}
			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
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

			if ($relation instanceof OneToMany) {
				$this->database->createQuery()
					->update()
					->table($relation->getRelatedTable())
					->set(
						array(
							$relation->getRelatedKey() => null,
						)
					)
					->whereEqual($relation->getRelatedKey(), $id)
					->whereEqual($relation->getKey(), $childId)
					->execute();
			} else if ($relation instanceof ManyToMany) {
				$this->database->createQuery()
					->delete()
					->from($relation->getRelationalTable())
					->whereEqual($relation->getKey(), $id)
					->whereEqual($relation->getRelatedKey(), $childId)
					->execute();
			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
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
		public function setChildren ($relationIdentifier, $id, $children, $subtractive = true, $isRelation = true, $filterColumns = true)
		{
			$relation = $this->getRelation($relationIdentifier);
			$children = (array)$children;

			if (!$relation) {
				throw new RelationException('Invalid relation `' . $relationIdentifier);
			}

			if ($relation instanceof OneToMany) {
				$childIds        = array();
				$primaryKey       = $this->database->primaryKey($relation->getRelatedTable());
				$parentPrimaryKey = $this->getPrimaryKey();

				foreach ($children as $child) {
					$key = $child[$primaryKey];

					if ($filterColumns) {
						$child = $relation->getRelatedTableObject()->filterColumns($child);
					}

					if ($key) {
						$childIds[]               = $key;
						$child[$parentPrimaryKey] = $id;

						$this->database->createQuery()
							->update()
							->table($relation->getRelatedTable())
							->set($child)
							->whereEqual($primaryKey, $key)
							->execute();
					} else {
						$child[$parentPrimaryKey] = $id;

						$childIds[] = $this->database->createQuery()
							->insert()
							->into($relation->getRelatedTable())
							->set($child)
							->execute();
					}
				}

				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation->getRelatedTable())
						->whereEqual($parentPrimaryKey, $id)
						->whereNotIn($primaryKey, $childIds)
						->execute();
				}
			} else if ($relation instanceof ManyToMany) {
				$childIds = array();

				if ($isRelation) {
					$table    = $relation->getRelationalTable();
					$keyField = $relation->getRelationalTableObject()->getPrimaryKey();
				} else {
					$table    = $relation->getRelatedTable();
					$keyField = $relation->getRelatedKey();
				}

				foreach ($children as $child) {
					if ($filterColumns && $isRelation) {
						$child = $relation->getRelationalTableObject()->filterColumns($child, false);
					} else if ($filterColumns) {
						$child = $relation->getRelatedTableObject()->filterColumns($child);
					}

					if ($isRelation) {
						$key                        = $child[$keyField];
						$child[$relation->getKey()] = $id;
					} else {
						$key = $child[$keyField];
					}

					if ($key) {
						$childIds[] = $key;

						$this->database->createQuery()
							->update()
							->table($table)
							->set($child)
							->whereEqual($keyField, $key)
							->execute();
					} else {
						$childIds[] = $this->database->createQuery()
							->insert()
							->into($table)
							->set($child)
							->execute();
					}
				}

				if ($subtractive) {
					$this->database->createQuery()
						->delete()
						->from($relation->getRelationalTable())
						->whereEqual($relation->getKey(), $id)
						->whereNotIn($keyField, $childIds)
						->execute();
				}
			} else {
				throw new RelationException('Invalid relation type `' . gettype($relation) . '`');
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

			if ($relation instanceof ManyToOne && $relation instanceof OneToOne) {
				throw new RelationException('Invalid relation type `' . $relation['type'] . '`');
			}

			$row = $this->getRow($id);

			$query = $this->database->createQuery()
				->whereEqual($relation->getKey(), $row[$relation->getRelatedKey()])
				->composeWith($inputQuery);

			return $relation->getRelatedTableObject()->getRows($query)->fetchRow();
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
		 * @return Relation
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
		protected function filterColumns ($row, $stripPrimary = false)
		{
			$columns = $this->database->getFields($this->table);

			if ($stripPrimary) {
				$columns = array_filter($columns, function ($column) {
					if ($this->database->primaryKey($this->table) == $column) {
						return false;
					} else {
						return true;
					}
				});
			}

			return array_intersect_key($row, array_flip($columns));
		}


		/**
		 * @return Relation[]
		 */
		protected function getSingleParentRelations ()
		{
			$tables = array();

			foreach ($this->relations as $relation) {
				if ($relation instanceof ManyToOne || $relation instanceof OneToOne) {
					$tables[] = $relation;
				}
			}

			return $tables;
		}
	}



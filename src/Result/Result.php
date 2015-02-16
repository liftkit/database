<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Result;

	use LiftKit\Database\Entity\Entity as DatabaseRow;

	use LiftKit\DependencyInjection\Container\Container;

	use PDO;
	use PDOStatement;

	use Countable;
	use Iterator;


	/**
	 * Class Result
	 *
	 * @package LiftKit\Database\Result
	 */
	class Result implements Countable, Iterator
	{
		/**
		 * @var Container
		 */
		protected $container;

		/**
		 *
		 * @var array
		 */
		protected $current = false;

		/**
		 * @var PDOStatement
		 */
		protected $pdoStatement;

		/**
		 * @var string|null
		 */
		protected $entityName;

		/**
		 * @var int
		 */
		protected $cursor = -1;


		public function __construct (PDOStatement $pdoStatement, Container $container = null, $entityName = null)
		{
			$this->pdoStatement = $pdoStatement;
			$this->container = $container;
			$this->entityName = $entityName;
		}


		public function fetchAll ()
		{
			$data = array();

			while ($row = $this->pdoStatement->fetchAll()) {
				$data[] = $this->cast($row);
			}

			return $data;
		}


		public function fetchRow ()
		{
			$row = $this->pdoStatement->fetch();

			if ($row) {
				return $this->cast($row);
			} else {
				return null;
			}
		}


		public function fetchColumn ($column = null)
		{
			$data = $this->fetchAll();
			$list = array();

			foreach ($data as $row) {
				if ($column == null) {
					$values = array_values($row->toArray());
					$list[] = array_shift($values);
				} else {
					$list[] = $row[$column];
				}
			}

			return $list;
		}


		public function fetchField ($field = null)
		{
			$row = $this->pdoStatement->fetch();

			if (is_null($field)) {
				return $row[0];
			} else {
				return $row[$field];
			}
		}


		public function transform ($callback)
		{
			$array  = array();
			foreach ($this as $row)
			{
				$array[] = $callback($row);
			}

			return $array;
		}


		public function flatten ()
		{
			return $this->transform(
				function ($row)
				{
					return $row->toArray();
				}
			);
		}


		public function count()
		{
			return $this->pdoStatement->rowCount();
		}


		public function current()
		{
			return $this->current ? $this->cast($this->current) : false;
		}


		public function key()
		{
			return $this->cursor;
		}


		public function next()
		{
			if ($this->hasNext()) {
				$this->cursor++;
				$this->current = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);
				if (empty($this->current))
					$this->current = false;
				else
					return true;
			}else
				$this->current = false;
			return false;
		}


		public function rewind()
		{
			if ($this->cursor != -1) {
				$this->cursor = -1;
				$this->pdoStatement->execute();
			} else {
				$this->next();
			}
		}


		public function valid()
		{
			return ($this->current != false) || (($this->cursor == -1) && ($this->count() > 0));
		}


		public function getFirst()
		{
			$this->rewind();
			return $this->getNext();
		}


		public function hasNext()
		{
			return ($this->count() > 0) && (($this->cursor + 1) < $this->count());
		}


		public function getNext()
		{
			if ($this->next()) {
				return $this->cast($this->current);
			} else {
				$this->rewind();
			}
			return false;
		}


		public function getCurrent()
		{
			return $this->current();
		}


		protected function cast($data)
		{
			if (is_null($this->entityName)) {
				return new DatabaseRow($data);

			} else {
				return $this->container->getObject(
					$this->entityName,
					array($data)
				);
			}
		}
	}


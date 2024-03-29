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
	use JsonSerializable;


	/**
	 * Class Result
	 *
	 * @package LiftKit\Database\Result
	 */
	class Result implements Countable, Iterator, JsonSerializable
	{
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
		 * @var callable
		 */
		protected $castCallback;

		/**
		 * @var int
		 */
		protected $cursor = -1;

		/**
		 * @var int
		 */
		protected $count;


		public function __construct (PDOStatement $pdoStatement, callable $castCallback = null)
		{
			$this->pdoStatement = $pdoStatement;
			$this->castCallback = $castCallback;

			$this->count = $this->count();
		}


		public function fetchAll ()
		{
			$data = array();

			while ($row = $this->pdoStatement->fetch(PDO::FETCH_ASSOC)) {
				$data[] = $this->cast($row);
			}

			return $data;
		}


		public function fetchRow ()
		{
			$row = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

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
			$row = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				if (is_null($field)) {
					return array_shift($row);
				} else {
					return $row[$field];
				}
			} else {
				return null;
			}
		}


		public function transform ($callback)
		{
			$array  = array();
			foreach ($this->fetchAll() as $row)
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


		#[\ReturnTypeWillChange]
		public function count()
		{
			if (isset($this->count)) {
				return $this->count;
			} else {
				$count = $this->pdoStatement->rowCount();

				if ($count === false) {
					$count = 0;

					while ($row = $this->pdoStatement->fetch()) {
						$count++;
					}

					$this->cursor = -1;
					$this->pdoStatement->closeCursor();
					$this->pdoStatement->execute();
				}

				return $count;
			}
		}


		#[\ReturnTypeWillChange]
		public function current()
		{
			return $this->current ? $this->cast($this->current) : false;
		}


		#[\ReturnTypeWillChange]
		public function key()
		{
			return $this->cursor;
		}


		#[\ReturnTypeWillChange]
		public function next()
		{
			if ($this->hasNext()) {
				$this->cursor++;
				$this->current = $this->pdoStatement->fetch(PDO::FETCH_ASSOC);

				if (empty($this->current)) {
					$this->current = false;
				} else {
					return true;
				}
			} else {
				$this->current = false;
			}

			return false;
		}


		#[\ReturnTypeWillChange]
		public function rewind()
		{
			if ($this->cursor != -1) {
				$this->cursor = -1;
				$this->pdoStatement->execute();
			} else {
				$this->next();
			}
		}


		#[\ReturnTypeWillChange]
		public function valid()
		{
			return ($this->current != false) || (($this->cursor == -1) && ($this->count() > 0));
		}


		public function getQueryString ()
		{
			return $this->pdoStatement->queryString;
		}


		public function setCastCallback (callable $callback = null)
		{
			$this->castCallback = $callback;

			return $this;
		}


		#[\ReturnTypeWillChange]
		public function jsonSerialize ()
		{
			return $this->flatten();
		}


		protected function cast($data)
		{
			if (is_null($this->castCallback)) {
				return new DatabaseRow($data);

			} else {
				return call_user_func_array(
					$this->castCallback,
					array($data)
				);
			}
		}


		protected function hasNext()
		{
			return ($this->count() > 0) && (($this->cursor + 1) < $this->count());
		}
	}



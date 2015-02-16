<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Entity;

	use Countable;
	use Iterator;
	use ArrayAccess;


	/**
	 * Class Entity
	 *
	 * @package LiftKit\Database\Entity
	 */
	class Entity implements Countable, Iterator, ArrayAccess
	{
		protected $data;


		public function __construct ($data)
		{
			$this->data = (array)$data;
		}


		public function count ()
		{
			return count($this->data);
		}


		public function current ()
		{
			return current($this->data);
		}


		public function key ()
		{
			return key($this->data);
		}


		public function next ()
		{
			return next($this->data);
		}


		public function rewind ()
		{
			return reset($this->data);
		}


		public function valid ()
		{
			return key($this->data) !== null;
		}


		public function offsetExists ($offset)
		{
			return isset($this->data[$offset]);
		}


		public function offsetGet ($offset)
		{
			return $this->data[$offset];
		}


		public function offsetSet ($offset, $value)
		{
			$this->data[$offset] = $value;
		}


		public function offsetUnset ($offset)
		{
			unset($this->data[$offset]);
		}


		public function toArray ()
		{
			return $this->data;
		}


		/**
		 * @deprecated
		 * @return array
		 */
		public function asArray ()
		{
			return $this->toArray();
		}

		public function getField ($identifier)
		{
			return $this->data[$identifier];
		}
	}



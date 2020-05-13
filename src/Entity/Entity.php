<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Entity;

	use LiftKit\Collection\Collection;
	use JsonSerializable;


	/**
	 * Class Entity
	 *
	 * @package LiftKit\Database\Entity
	 */
	class Entity extends Collection implements JsonSerializable
	{


		public function getField ($identifier)
		{
			return $this->items[$identifier];
		}


		public function jsonSerialize ()
		{
			return $this->toArray();
		}
	}



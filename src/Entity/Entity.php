<?php

	/*
	 *
	 *	LiftKit MVC PHP Framework
	 *
	 */

	namespace LiftKit\Database\Entity;

	use LiftKit\Collection\Collection;


	/**
	 * Class Entity
	 *
	 * @package LiftKit\Database\Entity
	 */
	class Entity extends Collection
	{


		public function getField ($identifier)
		{
			return $this->items[$identifier];
		}
	}



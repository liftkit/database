<?php

	namespace LiftKit\Database\Query\Identifier;


	abstract class Identifier
	{
		/**
		 * @var string
		 */
		protected $identifierString;


		abstract public function quote ();


		public function __construct ($identifierString)
		{
			$this->identifierString = $identifierString;
		}


		public function __toString ()
		{
			return $this->quote();
		}
	}
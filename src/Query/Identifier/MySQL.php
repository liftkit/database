<?php


	namespace LiftKit\Database\Query\Identifier;


	class MySQL extends Identifier
	{


		public function quote ()
		{
			$identifier = $this->identifierString;

			if (!preg_match('#^([A-Za-z0-9\._])+$#', $identifier)) {
				return $identifier;
			}

			$split = explode('.', $identifier);

			foreach ($split as &$segment) {
				$segment = '`'.$segment.'`';
			}

			return implode('.', $split);
		}
	}
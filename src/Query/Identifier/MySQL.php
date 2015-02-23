<?php


	namespace LiftKit\Database\Query\Identifier;

	use LiftKit\Database\Query\Raw\Raw;
	use LiftKit\Database\Query\Query;


	class MySQL extends Identifier
	{


		public function quote ()
		{
			$identifier = $this->identifierString;

			if ($identifier instanceof Raw) {
				return (string) $identifier;
			} else if ($identifier instanceof Query) {
				return '(' . $identifier->getRaw() . ')';
			}

			$split = explode('.', (string) $identifier);

			foreach ($split as &$segment) {
				$segment = '`' . $segment . '`';
			}

			return implode('.', $split);
		}
	}
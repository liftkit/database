<?php


	namespace LiftKit\Database\Query\Identifier;

	use LiftKit\Database\Query\Raw\Raw;
	use LiftKit\Database\Query\Query;
	use LiftKit\Database\Query\Exception\Query as QueryException;


	class MsSql extends Identifier
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
				if ($segment != '*') {
					if (strstr($segment, '"')) {
						throw new QueryException('Invalid identifier ' . $segment);
					}

					$segment = '"' . $segment . '"';
				}
			}

			return implode('.', $split);
		}
	}
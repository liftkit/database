<?php


	namespace LiftKit\Database\Query\Union;


	class Union
	{
		const UNION_ALL = 1;
		const UNION_DISTINCT = 2;

		protected $query;
		protected $type;


		public function __construct ($query, $type = self::UNION_ALL)
		{
			$this->query = $query;
			$this->type = $type;
		}


		public function getQuery ()
		{
			return $this->query;
		}


		public function getType ()
		{
			return $this->type;
		}
	}
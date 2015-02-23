<?php


	namespace LiftKit\Database\Query\Raw;



	class Raw
	{
		protected $rawSql;


		public function __construct ($rawSql)
		{
			$this->rawSql = (string) $rawSql;
		}


		public function __toString ()
		{
			return $this->rawSql;
		}
	}
<?php


	namespace LiftKit\Tests\Unit\Database;


	abstract class DefaultTestCase extends TestCase
	{


		public function getDataSet ()
		{
			return $this->getDefaultDataSet();
		}


		protected function getDefaultDataSet ()
		{
			return $this->createMySQLXMLDataSet(__DIR__ . '/../datasets/default/default.xml');
		}
	}
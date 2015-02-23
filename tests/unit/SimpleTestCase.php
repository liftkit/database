<?php


	namespace LiftKit\Tests\Database;


	abstract class SimpleTestCase extends TestCase
	{


		public function getDataSet ()
		{
			return $this->getSimpleDataSet();
		}


		protected function getSimpleDataSet ()
		{
			return $this->createMySQLXMLDataSet(__DIR__ . '/../datasets/simple/simple.xml');
		}
	}
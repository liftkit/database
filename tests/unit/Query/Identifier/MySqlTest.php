<?php

	namespace LiftKit\Tests\Unit\Database\Query\Identifier;

	use LiftKit\Database\Query\Identifier\MySql as Identifier;
	use PHPUnit_Framework_TestCase;

	use LiftKit\Database\Query\Raw\Raw;


	class MySqlTest extends PHPUnit_Framework_TestCase
	{


		public function testIdentifier ()
		{
			$identifier = new Identifier('test');

			$this->assertEquals($identifier, '`test`');
		}


		public function testIdentifierWithQuote ()
		{
			$identifier = new Identifier('tes`t');

			$this->assertEquals($identifier, '`tes``t`');
		}


		public function testMultiSegment ()
		{
			$identifier = new Identifier('segment1.segment2.segment3');

			$this->assertEquals($identifier, '`segment1`.`segment2`.`segment3`');
		}


		public function testWithSpaces ()
		{
			$identifier = new Identifier('segment1.SEGMENT 2.segment3');

			$this->assertEquals($identifier, '`segment1`.`SEGMENT 2`.`segment3`');
		}


		public function testWithRaw ()
		{
			$raw = new Raw('test');
			$identifier = new Identifier($raw);

			$this->assertEquals($identifier, 'test');
		}


		public function testStart ()
		{
			$identifier = new Identifier('*');

			$this->assertEquals($identifier, '*');
		}
	}
<?php


	namespace LiftKit\Tests\Unit\Database\Query\Raw;

	use LiftKit\Database\Query\Raw\Raw;
	use PHPUnit_Framework_TestCase;


	class RawTest extends PHPUnit_Framework_TestCase
	{


		public function testRaw ()
		{
			$funkyString = 'uhjndlsd(sdfjjasdjjamp&$fmposakdp';

			$this->assertEquals(new Raw($funkyString), $funkyString);
		}
	}
<?php

	namespace LiftKit\Tests\Unit\Database\Model;
	
	use LiftKit\Tests\Stub\Database\Model\Model as StubModel;
	use LiftKit\Tests\Unit\Database\DefaultTestCase;
	
	
	class ModelTest extends DefaultTestCase
	{
		
		
		public function testCreateModel ()
		{
			$model = new StubModel($this->connection);
			
			$this->assertTrue($model instanceof StubModel);
		}
	}
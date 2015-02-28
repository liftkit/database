<?php


	namespace LiftKit\Tests\Unit\Database\Schema\Table\Relation;
	
	use LiftKit\Database\Schema\Table\Relation\ManyToOne;
	
	
	class ManyToOneTest extends RelationTestCase
	{
		
		
		public function testDefaults ()
		{
			$relation = new ManyToOne(
				$this->childrenTable,
				$this->parentsTable
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'children'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getKey(),
				'parent_id'
			);
			
			$this->assertEquals(
				$relation->getRelatedKey(),
				'parent_id'
			);
		}
		
		
		public function testKeyValues ()
		{
			$relation = new ManyToOne(
				$this->childrenTable,
				$this->parentsTable,
				'child_name',
				'parent_name'
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'children'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getKey(),
				'child_name'
			);
			
			$this->assertEquals(
				$relation->getRelatedKey(),
				'parent_name'
			);
		}
	}
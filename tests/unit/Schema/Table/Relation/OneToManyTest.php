<?php


	namespace LiftKit\Tests\Unit\Database\Schema\Table\Relation;
	
	use LiftKit\Database\Schema\Table\Relation\OneToMany;
	
	
	class OneToManyTest extends RelationTestCase
	{
		
		
		public function testDefaults ()
		{
			$relation = new OneToMany(
				$this->parentsTable,
				$this->childrenTable
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'children'
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
			$relation = new OneToMany(
				$this->parentsTable,
				$this->childrenTable,
				'parent_name',
				'child_name'
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'children'
			);
			
			$this->assertEquals(
				$relation->getKey(),
				'parent_name'
			);
			
			$this->assertEquals(
				$relation->getRelatedKey(),
				'child_name'
			);
		}
	}
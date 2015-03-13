<?php


	namespace LiftKit\Tests\Unit\Database\Schema\Table\Relation;
	
	use LiftKit\Database\Schema\Table\Relation\ManyToMany;
	
	
	class ManyToManyTest extends RelationTestCase
	{
		
		
		public function testDefaults ()
		{
			$relation = new ManyToMany(
				$this->parentsTable,
				$this->friendsTable,
				$this->parentFriendsTable
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'friends'
			);
			
			$this->assertEquals(
				$relation->getRelationalTable(),
				'parent_friends'
			);
			
			$this->assertEquals(
				$relation->getKey(),
				'parent_id'
			);
			
			$this->assertEquals(
				$relation->getRelatedKey(),
				'friend_id'
			);
		}
		
		
		public function testKeyValues ()
		{
			$relation = new ManyToMany(
				$this->parentsTable,
				$this->friendsTable,
				$this->parentFriendsTable,
				'parent_name',
				'friend_name'
			);
			
			$this->assertEquals(
				$relation->getTable(),
				'parents'
			);
			
			$this->assertEquals(
				$relation->getRelatedTable(),
				'friends'
			);
			
			$this->assertEquals(
				$relation->getRelationalTable(),
				'parent_friends'
			);
			
			$this->assertEquals(
				$relation->getKey(),
				'parent_name'
			);
			
			$this->assertEquals(
				$relation->getRelatedKey(),
				'friend_name'
			);
		}
	}
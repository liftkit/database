<?php

	namespace LiftKit\Tests\Unit\Database\Entity;


	use LiftKit\Database\Entity\Entity;

	use PHPUnit_Framework_TestCase;


	class EntityTest extends PHPUnit_Framework_TestCase
	{
		/**
		 * @var Entity
		 */
		protected $entity;


		public function setUp ()
		{
			$this->entity = new Entity(
				array(
					'parent_id' => 1,
					'parent_name' => 'parent1',
				)
			);
		}


		public function testGetField ()
		{
			$this->assertEquals($this->entity->getField('parent_id'), 1);
		}
	}
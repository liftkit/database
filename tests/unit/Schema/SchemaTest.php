<?php

	namespace LiftKit\Tests\Unit\Database\Schema;

	use LiftKit\Database\Schema\Schema;
	use LiftKit\Database\Schema\Table\Table;

	use LiftKit\Tests\Unit\Database\DefaultTestCase;


	class SchemaTest extends DefaultTestCase
	{


		public function testDefineGetTable ()
		{
			$schema = new Schema($this->connection);

			$childrenTable = $schema->defineTable('children');
			$parentsTable = $schema->defineTable('parents');

			$this->assertTrue($childrenTable instanceof Table);
			$this->assertTrue($parentsTable instanceof Table);

			$this->assertSame($childrenTable, $schema->getTable('children'));
			$this->assertSame($parentsTable, $schema->getTable('parents'));
		}


		public function testForce ()
		{
			$schema = new Schema($this->connection);

			$table = $schema->getTable('nonexistent', true);

			$this->assertSame(
				$table,
				$schema->getTable('nonexistent')
			);
		}


		/**
		 * @expectedException \LiftKit\Database\Schema\Exception\NonexistentTable
		 */
		public function testFailsWithNonexistentTable ()
		{
			$schema = new Schema($this->connection);

			$childrenTable = $schema->defineTable('children');
			$parentsTable = $schema->defineTable('parents');

			$schema->getTable('nonexistent');
		}
	}
# LiftKit database library

## Installation

Using composer require: `"liftkit/database": "~2.6"`

There's a lot of functionality that isn't directly documented here. A lot of it can be inferred from what is presented, and much of the rest can be inferred from method names and signatures. Don't be afraid to use the classes and tests themselves as supplemental documentation!

Let's jump right in to some examples.

## Connection

### Establish a connection

```php
use LiftKit\Database\Connection\MySql;
use LiftKit\DependencyInjection\Container\Container;
use LiftKit\Database\Cache\Cache;
use PDO;

$connection = new MySql(
  new Container,
	new Cache,
	new PDO('connectionString', 'username', 'password')
);
```

### Run a raw SQL query

```php
$results = $connection->query(
  "
    SELECT *
    FROM tbl
  "
);
```

### Using placeholders

```php
$connection->query(
  "
    SELECT *
    FROM tbl
    WHERE col1 = ?
      AND col2 = ?
  ",
  [
    'val1',
    'val2',
  ]
);
```

# Result objects

### Loop through results

```php
// NOTE: 
// Results are not loaded into memory. Instead they are
// wrapped by an object of the class
// \LiftKit\Database\Result\Result

$results = $connection->query(
	"
		SELECT *
		FROM tbl
	"
);

foreach ($results as $row) {
  echo 'column "name" = ' . $row['name'] . PHP_EOL;
  echo 'column "id" = ' . $row['id'] . PHP_EOL;
}
```

### Fetch a single column as an array

```php
foreach ($results->fetchColumn('id') as $id) {
	echo $id . PHP_EOL;
}
// '1'
// '2'
// ...
```

### Fetch all rows as an array of entities

```php
foreach ($results->fetchAll() as $row) {
	// Do something with $row['column']
}
```

### Fetch all rows as an array of associative arrays

```php
foreach ($results->flatten() as $row) {
	// Do something with $row['column']
}
```

## Query builder

### New query

```php
use LiftKit\Database\Query\Query;

/**
 * @var Query $query
 */
$query = $connection->createQuery();
```

### Simple select query

```php
// SELECT field1, field2
// FROM tbl
// WHERE field1 = 'val1'

$results = $query->select('field1', 'field2')
  ->from('tbl')
  ->whereEqual('field1', 'val1')
  ->execute();
```

### More complicated select query

Note that the method `$connection->quoteIdentifier` is called on the right parameters.
That's because the right parameter is expected to be a value. If it is instead
a SQL identifier, it must be quoted.

```php
use LiftKit\Database\Query\Condition\Condition;

// SELECT field1, field2
// FROM tbl
// LEFT JOIN other_tbl ON (
//  tbl.field1 = other_tbl.field1
//  OR tbl.field2 > other_tbl.field2
// )
// WHERE tbl.field1 = 'val1'
// OR other_tbl.field2 = 'val2'
// GROUP BY tbl.field3, tbl.field4
// HAVING tbl.field1 < 1
// ORDER BY tbl.field5 ASC, tbl.field6 DESC

$results = $query->select('field1', 'field2')
  ->from('tbl')
  ->leftJoin(
    'other_tbl',
    $connection->createCondition()
    	->equal(
     		'tbl.field1',
     		$connection->quoteIdentifier('other_tbl.field1')
    	)
    	->orGreaterThan(
     		'tbl.field2',
     		$connection->quoteIdentifier('other_tbl.field2')
    	)
  )
  ->whereEqual('tbl1.field1', 'val1')
  ->orWhereEqual('other_tbl.field2', 'val2')
  ->groupBy('tbl.field3')
  ->groupBy('tbl.field4')
  ->havingLessThan('tbl.field1', 1)
  ->orderBy('tbl.field5', Query::ORDER_ASC)
  ->orderBy('tbl.field6', Query::ORDER_DESC)
  ->execute();
```

### Update query

Note that update queries can utilize conditions the same as select statements.

```php
// UPDATE tbl
// SET field2 = 'val2', field3 = 'val3'
// WHERE tbl.id = 2

$query->update()
  ->table('tbl')
  ->set(
    [
      'field2' => 'val2',
      'field3' => 'val3',
    ]
  )
  ->whereEqual('tbl.id', 2)
  ->execute();
```

### Insert query

Insert queries return their insert ID.

```php
// INSERT INTO tbl
// SET field2 = 'val2', field3 = 'val3'

$id = $query->insert()
  ->into('tbl')
  ->set(
    [
      'field2' => 'val2',
      'field3' => 'val3',
    ]
  )
  ->execute();
```

### Delete query

Note that delete queries can use conditions the same as select queries.

```php
// DELETE tbl.*
// FROM tbl
// WHERE id = 1

$query->delete()
  ->from('tbl')
  ->whereEqual('id', 1)
  ->execute();
```


## Subqueries

Subqueries can be substituted pretty much anywhere a value or identifier can be. 

Note: This is also an example of how to use raw SQL instead of escaped values in your queries
using the method `createRaw`.

```php
// SELECT *
// FROM tbl1
// WHERE
// ( SELECT COUNT(*)
//   FROM tbl2
//   WHERE tbl1.id = tbl2.tbl2_id
// ) = 1

$results = $query->select('*')
  ->from('tbl1')
  ->whereEqual(
    $connection->createQuery()
      ->select($connection->createRaw('COUNT(*)'))
      ->from('tbl2')
      ->whereEqual(
      	'tbl1.id', 
      	$connection->quoteIdentifier('tb2.tbl1_id')
      ),
    1
  )
  ->execute();
```

### Composing parts of queries

This comes in hand for extracting away parts of queries you use often, while
retaining the ability to combine them with other queries.

Let's say you have a function that returns all of the rows from `tbl`.

```php
function getAllTblRows ()
{
  $query = $connection->createQuery();
  
  return $query->select('*')
    ->from('tbl')
    ->execute();
}

// SELECT *
// FROM tbl

$results = getActiveTblRows();
```

Now you need another query which select only records which are active from `tbl`. Notice the additions to `getAllTblRows`.

```php
function getAllTblRows (Query $inputQuery = null)
{
  $query = $connection->createQuery();
  
  return $query->select('*')
    ->from('tbl')
    ->composeWith($inputQuery)
    ->execute();
}

function getActiveTblRows ()
{
  $query = $connection->createQuery();
  
  $query->whereEqual('active', 1);
  
  return getAllTblRows($query);
}

// SELECT *
// FROM tbl
// WHERE active = 1

$results = getActiveTblRows();
```

## Table objects

Table objects are meant to reduce the boilerplate you need to place in
your query builder queries.

### Fetching multiple rows

```php
use LiftKit\Database\Schema\Schema;
use LiftKit\Database\Schema\Table\Table;

// We'll get back to schemas in a moment

$table = new Table(
	$connection,
	new Schema($connection),
	'tbl'
);

// SELECT *
// FROM tbl

$results = $tbl->getRows();
```

### Fetching multiple rows with composed query

```php
// SELECT *
// FROM tbl
// WHERE active = 1

$results = $table->getRows(
	$connection->createQuery()
		->whereEqual('active', 1)
);
```

### Fetching a single row

```php
// SELECT *
// FROM tbl
// WHERE id = 1

$row = $table->getRow(1);

// 'val1'
echo $row['field1'];

// 'val2'
echo $row['field2'];
```

### Inserting a new row

````php
// INSERT INTO tbl
// SET field1 = 'val1', field2 = 'val2'

$id = $table->insertRow(
	[
		'field1' => 'val1',
		'field2' => 'val2',
	]
);
```

### Updating a row

```php

// UPDATE tbl
// SET field1 = 'val1', field2 = 'val2'
// WHERE id = 1

$table->updateRow(
	[
		'id'     => 1,
		'field1' => 'val1',
		'field2' => 'val2',
	]
);
```

### Deleting a row

```php
// DELETE FROM tbl
// WHERE id = 1

$table->deleteRow(1);
```

More info on table objects, relations, and entities coming soon!

# LiftKit database library

Let's jump right in to some examples.

## Connection

### Establish a connection

```php
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

### Loop through results

```php
// NOTE: 
// Results are not loaded into memory. Instead they are
// wrapped by an object of the class
// \LiftKit\Database\Result\Result

foreach ($results as $result) {
  echo 'column "name" = ' . $result['name'] . PHP_EOL;
  echo 'column "id" = ' . $result['id'] . PHP_EOL;
}
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

## Query builder

### New query

```php
use LiftKit\Database\Query\Query;

$query = new Query($connection);
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

$joinCondition = new Condition($connection);

$results = $query->select('field1', 'field2')
  ->from('tbl')
  ->leftJoin(
    'other_tbl',
    $joinCondition->equal(
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

More info on table objects, relations, and entities coming soon!

# DELETE statements

## Create the query

```php
use Sirius\Sql\Delete;
$select = new Delete($connection);
```

or use the factory
```php
use Sirius\Sql\QueryFactory;

$factory = new QueryFactory($connection);
$select = $factory->newDelete();
```
## FROM

Use the `from()` method to specify FROM expression.

```php
$delete->from('posts');
```

## Conditions

The _Delete_ `WHERE` methods work just like their equivalent _Select_ methods:

- `where()` and `andWhere()` AND a WHERE condition
- `orWhere()` ORs a WHERE condition
- `whereStartSet()` starts a grouped condition
- `endSet()` closes previously opened grouped condition
- `whereSprintf()` and `andWhereSprintf()` AND a WHERE condition with sprintf()
- `orWhereSprintf()` ORs a WHERE condition with sprintf()

## Sorting

Some databases (notably MySQL) recognize an `ORDER BY` clause. You can add one
to the _Delete_ with the `orderBy()` method; pass each expression as a variadic
argument.

```php
$delete
    ->orderBy('priority', 'publish_date');
// SQL: DELETE ... ORDER BY priority, publish_date
```

## LIMIT and OFFSET

Some databases (notably MySQL and SQLite) recognize a `LIMIT` clause; others
(notably SQLite) recognize an additional `OFFSET`. You can add these to the
_Delete_ with the `limit()` and `offset()` methods:

```php
$delete
    ->limit(10)
    ->offset(40);
// SQL: LIMIT 10 OFFSET 40
```

## RETURNING

Some databases (notably PostgreSQL) recognize a `RETURNING` clause. You can add
one to the _Delete_ using the `returning()` method, specifying columns as
variadic arguments.

```php
$delete
    ->returning('id')
    ->returning('title', 'content');
// SQL: DELETE ... RETURNING id, title, content
```

## Flags

You can set flags recognized by your database server using the `setFlag()`
method. For example, you can set a MySQL `LOW_PRIORITY` flag like so:

```php
$delete
    ->from('posts')
    ->where('id', 10)
    ->setFlag('LOW_PRIORITY');
// SQL: DELETE LOW_PRIORITY posts WHERE ...
```

## Performing the query

Once you have built the query, call the `perform()` method to execute it and
get back a _PDOStatement_.

```php
$result = $delete->perform(); // : PDOStatement

$deletedRows = $result->rowCount();
```

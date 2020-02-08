# UPDATE statements

## Create the query

```php
use Sirius\Sql\Update;
$select = new Update($connection);
```

or use the factory
```php
use Sirius\Sql\QueryFactory;

$factory = new QueryFactory($connection);
$select = $factory->newUpdate();
```
### Table

Use the `table()` method to specify the table to update.

```php
$update->table('posts');
```

## Columns

You can set a named placeholder and its corresponding bound value using the
`column()` method.

```php
$update->column('title', $title);
// SQL: UPDATE posts SET title = :title
```

Note that the PDO parameter type will automatically be set for strings,
integers, floats, and nulls. If you want to set a PDO parameter type yourself,
pass it as an optional third parameter.

```php
$update->column('content', $content, \PDO::PARAM_LOB);
// UPDATE foo SET content = :content
```

You can set several placeholders and their corresponding values all at once by
using the `columns()` method:

```php
// UPDATE foo SET bar = :bar, baz = :baz
$update->columns([
    'title' => $title,
    'content' => $content
]);
```

However, you will not be able to specify a particular PDO parameter type when
doing do.

Bound values are automatically quoted and escaped; in some cases, this will be
inappropriate, so you can use the `raw()` method to set column to an unquoted
and unescaped expression.

```php
$update->column('publish_date', $update->raw('NOW()'));
// SQL UPDATE posts SET bar = NOW()
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
    ->from('foo')
    ->where('id', 10)
    ->setFlag('LOW_PRIORITY');
// SQL: DELETE LOW_PRIORITY foo WHERE ...
```

## Performing the query

Once you have built the query, call the `perform()` method to execute it and
get back a _PDOStatement_.

```php
$pdoStatement = $update->perform();
$affectedRows = $pdoStatement->rowCount();
```

If you added a `RETURNING` clause with the `returning()` method, you can
retrieve those column values with the returned _PDOStatement_:

```php
$pdoStatement = $update->perform();
$values = $pdoStatement->fetch(); // : array
```

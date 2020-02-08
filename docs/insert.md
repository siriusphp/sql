# INSERT statements


## Create the query

```php
use Sirius\Sql\Insert;
$select = new Insert($connection);
```

or use the factory
```php
use Sirius\Sql\QueryFactory;

$factory = new QueryFactory($connection);
$select = $factory->newInsert();
```

## Into

```php
$insert->into('posts');
```

## Columns

```php
$insert->column('title', $title);
// SQL: INSERT INTO foo (title) VALUES (:title)
```

Note that the PDO parameter type will automatically be set for strings,
integers, floats, and nulls. If you want to set a PDO parameter type yourself,
pass it as an optional third parameter.

```php
$insert->column('content', $content, \PDO::PARAM_LOB);
```

You can set several placeholders and their corresponding values all at once by
using the `columns()` method:

```php
$insert->columns([
    'title' => $title,
    'content' => $content
]);
// SQL: INSERT INTO foo (bar) VALUES (:bar)
```

However, you will not be able to specify a particular PDO parameter type when
doing do.

Bound values are automatically quoted and escaped; in some cases, this will be
inappropriate, so you can use the `raw()` method to set column to an unquoted
and unescaped expression.

```php
$insert->column('publish_date', $insert->raw('NOW()');
// SQL: INSERT INTO posts (publish_date) VALUES (NOW())
```

## RETURNING

Some databases (notably PostgreSQL) recognize a `RETURNING` clause. You can add
one to the _Insert_ using the `returning()` method, specifying columns as
variadic arguments.

```php
// INSERT ... RETURNING foo, bar, baz
$insert
    ->returning('id')
    ->returning('title', 'content');
```

## Flags

You can set flags recognized by your database server using the `setFlag()`
method. For example, you can set a MySQL `LOW_PRIORITY` flag like so:

```php
$insert->setFlag('LOW_PRIORITY');
// INSERT LOW_PRIORITY INTO posts...
```

## Performing the query

Once you have built the query, call the `perform()` method to execute it and
get back a _PDOStatement_.

```php
$pdoStatement = $insert->perform();
```

### Last insert ID

If the database autoincrements a column while performing the query, you can get
back that value using the `getLastInsertId()` method:

```php
$id = $insert->getLastInsertId();
```

> **Note:**
>
> You can pass a sequence name as an optional parameter to `getLastInsertId()`;
> this may be required with PostgreSQL.

### RETURNING

If you added a `RETURNING` clause with the `returning()` method, you can
retrieve those column values with the returned _PDOStatement_:

```php
$pdoStatement = $insert->perform();
$values = $pdoStatement->fetch(); // : array
```

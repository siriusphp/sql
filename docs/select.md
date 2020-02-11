# SELECT statements

## Create the query

```php
use Sirius\Sql\Select;
$select = new Select($connection);
```

or use the factory
```php
use Sirius\Sql\QueryFactory;

$factory = new QueryFactory($connection);
$select = $factory->newSelect();
```

## Select columns

```php
// one at a time
$select->columns('id');

// multiple at a time
$select->columns('posts.name', 'title');

// protect from SQL injection
$select->columns($select->quoteIdentifier($columnNameFromUserInput));

// if you change your mind, reset
$select->resetColumns();
```

## From table or subselect

```php
$select->from('posts');

$select->from($select->subSelect()
    ->columns('id', 'name')
    ->from('posts')
    ->where('parent_id', null)
    ->as('root_posts')
    ->getStatement()
); // SQL: SELECT FROM (SELECT id, name FROM posts WHERE parent_id IS NULL) as root_posts

// reset
$select->resetFrom();
```

## Conditions

#### Basic usage

```php
// one column bound immediately to a values
$select->where('title', $someValue);

// one column smartly bound to multiple values (IN / NOT IN)
$select->where('id', [10, 11, 12]);

// one column to be bound later
$select->where('title = :title');

// multiple conditions
$select->where('published_date < NOW()')
    ->where('title', $title)
    ->orWhere('title', $anotherTitle); // SQL: WHERE published_date < NOW AND title = $title OR title = $anotherTitle 

// multiple conditions properly grouped
$select->where('published_date < NOW()')
    ->whereStartSet()
    ->where('title', $title)
    ->orWhere('title', $anotherTitle)
    ->endSet(); // SQL: WHERE published_date < NOW AND (title = $title OR title = $anotherTitle) 

```

#### Frequent search criteria

The library optimizes for frequent use-cases

```php
use Sirius\Sql\ConditionsEnum;

$select->where('id', [1, 2, 3], ConditionsEnum::NOT_EQUALS); // id NOT IN [1, 2, 3]

$select->where('title', 'ABC', ConditionsEnum::STARTS_WITH); // title LIKE 'ABC%'
$select->where('title', 'ABC', ConditionsEnum::DOES_NOT_START_WITH); // title NOT LIKE 'ABC%'

$select->where('title', 'ABC', ConditionsEnum::ENDS_WITH); // title LIKE '%ABC'
$select->where('title', 'ABC', ConditionsEnum::DOES_NOT_END_WITH); // title NOT LIKE '%ABC'

$select->where('title', 'ABC', ConditionsEnum::CONTAINS); // title LIKE '%ABC%'
$select->where('title', 'ABC', ConditionsEnum::DOES_NOT_CONTAIN); // title NOT LIKE '%ABC%'

$select->where('date', ['2020-01-01', '2020-12-31'], ConditionsEnum::BETWEEN);

// reset all conditions
$select->resetWhere();
```

#### Flexible conditions using `sprintf()`-like functionality

Use only `%s` inside conditions and you can freely bind any values. Carefull: With great power, comes great responsibility!

```php
$select->whereSprintf("publish_date <= NOW() AND parent_id = %s AND title LIKE %s", 10, "%ABC%")
    ->orWhereSprintf("author_id = %s", 20);
// SQL: publish_date <= NOW() and parent_id = 10 AND title LIKE '%ABC%' OR author_id = 20
```

#### Grouping existing conditions

There are times when some piece of code generates some conditions for a query and later another piece of code (via events, callbacks or decorators) attach additional conditions that work on top of the current conditions.

```php
$select->where('id', 10)
    ->orWhere('id', 15);
// SQL at this point: WHERE id = 10 OR id = 15

// later you restrict everything
$select->groupCurrentWhere()
    ->where('author_id', 1);
// SQL: WHERE (id = 10 OR id = 15) AND author_id = 15
```

#### Multi-column IN condition

The most common scenario for retrieving multiple rows is like so `SELECT * FROM table WHERE id IN (1, 2, 3)`. This does not work if the primary key uses more than 1 column. For this scenario you have access to `whereMultiple()`

```php
$values = [
    [1, 2],
    [2, 4]
];
$select->whereMultiple(['col_1', 'col_2'], $values);
// SQL: WHERE ((col_1 = 1 AND col_2 = 2) OR (col_1 = 3 AND col_2 = 4))
```

## JOINs

```php
// simple join
$select->join('LEFT', 'comments', 'comments.post_id = posts.id');

// join with bounded params via sprintf
$select->join('LEFT', 'comments', 'comments.commentable_id = posts.id AND comments.commentable_type = %s', 'posts');
```

You can even do subselects

```php
$select->join('LEFT', 
    $select->subSelect()
        ->columns('*')
        ->from('comments')
        ->where('commentable_type', 'posts')
        ->as('post_comments'),
    'post_comments.commentable_id = posts.id'
    );
```

## Sorting

```php
// one or more columns
$select->sortBy('display_order', 'publish_date DESC');

// reset
$select->resetOrderBy();
```

## LIMIT, OFFSET and pagination

```php
// manage yourself
$select->offset(20)->limit(10); // SQL: LIMIT 10 OFFSET 20

// or paginate
$select->perPage(10)->page(3); // SQL: LIMIT 10 OFFSET 20

// reset
$select->resetLimit();
```

## HAVING

Since `HAVING` conditions are rarely used they work only using `sprintf`-like functionality and without nesting possibilities

```php
// bind later
$select->having('content LIKE :having_content_like');


// sprintf functionality
$select->having('content LIKE %s', '%abc%')
    ->orHaving('content NOT LIKE %s', '%xyz%');
// SQL: HAVING content like '%abc%' OR content NOT LIKE '%xyz%'

// for nesting you have to do it yourself
$select->having('(content LIKE %s OR content LIKE %s) AND content NOT LIKE %s', '%abc', '%def', '%xyz%')
```

## UNION

```php
$select->union()
    ->columns('*')
    ->from('another_table')
    ->where('id', null, ConditionsEnum::NOT_EQUAL)
    ->unionAll()
    ->columns('*')
    ->from('yet_another_table')
    ->where('parent_id', null);
```

## Binding parameters

If you decided to name your bindings you have bind the values before performing the query

```php
$select->where('title = :title');

// before executing the query
$select->bindValue('title', 'Some title');

// you can force the parameter type
$select->bindValue('id', $someId, PDO::PARAM_INT);

// you can bind multiple parameters at once
$select->bindValues([
    'title' => 'Some title',
    'id' => $someId
]);
```


## Performing the query

Once you have built the query, call the `perform()` method to execute it and
get back a _PDOStatement_.

```php
$result = $select->perform();
```

The _Select_ proxies all `fetch*()` and `yield()` method calls to the underlying
_Connection_ object via the magic `__call()` method, which means you can both
build the query and perform it using the same _Select_ object.

The _Connection_ `fetch*()` and `yield*()` methods proxied through the _Select_
are as follows:

- `fetchAll() : array`
- `fetchAffected() : int`
- `fetchColumn(int $column = 0) : array`
- `fetchGroup(int $style = PDO::FETCH_COLUMN) : array`
- `fetchKeyPair() : array`
- `fetchObject(string $class = 'stdClass', array $args = []) : object`
- `fetchObjects(string $class = 'stdClass', array $args = []) : array`
- `fetchOne() : ?array`
- `fetchUnique() : array`
- `fetchValue() : mixed`
- `yieldAll() : Generator`
- `yieldColumn(int $column = 0) : Generator`
- `yieldKeyPair() : Generator`
- `yieldObjects(string $class = 'stdClass', array $args = []) : Generator`
- `yieldUnique() : Generator`

For example, to build a query and get back an array of all results:

```php
// SELECT * FROM foo WHERE bar > :__1__
$result = $select
    ->columns('*')
    ->from('foo')
    ->where('bar > ', $value)
    ->fetchAll();

foreach ($result as $key => $val) {
    echo $val['bar'] . PHP_EOL;
}
```
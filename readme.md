# Sirius Sql

[![Source Code](http://img.shields.io/badge/source-siriusphp/sql-blue.svg?style=flat-square)](https://github.com/siriusphp/sql)
[![Latest Version](https://img.shields.io/packagist/v/siriusphp/sql.svg?style=flat-square)](https://github.com/siriusphp/sql/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/siriusphp/sql/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/siriusphp/sql/master.svg?style=flat-square)](https://travis-ci.org/siriusphp/sql)
[![PHP 7 ready](http://php7ready.timesplinter.ch/siriusphp/sql/master/badge.svg)](https://travis-ci.org/siriusphp/sql)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/siriusphp/sql.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/sql/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/siriusphp/sql.svg?style=flat-square)](https://scrutinizer-ci.com/g/siriusphp/sql)

The `siriusphp/sql` library is designed to help you build and execute SQL simple and complex queries in a fast and safe way. 

The vocabulary is as close to SQL as possible as you may see from the example below:

```php
use Atlas\Pdo\Connection;
use Sirius\Sql\Select;
use Sirius\Sql\ConditionsEnum;

$connection = Connection::new('sqlite::memory:');
$select = new Select($connection);

// find the 10 "cool" posts that are published and also retrieve the comments count
$select->distinct()
    ->columns('posts.*', 'COUNT(comments.id) AS comments_count')
    ->from('posts')
    ->join('LEFT', 'comments', 'comments.commentable_id = posts.id AND comments.commentable_type = %s', 'posts')
    ->where('posts.published_at < NOW()')
    ->where('posts.title', 'cool', ConditionsEnum::CONTAINS)
    ->groupBy('posts.id')
    ->limit(10);

$posts = $select->fectchAll();
```  

## Links

- [documentation](http://sirius.ro/php/sirius/sql/)
- [changelog](CHANGELOG.md)


## Acknowledgements

This library is a derivative work of [atlas/query](http://atlasphp.io/cassini/query/). I made this library for 2 reasons:
- to reduce cognitive load by removing some methods and implementing other ways to achieve the same goals (eg: nested conditions)
- to optimize some operations for the most common scenarios (eg: `where($column, $str, 'does_not_contain')` vs `where($column . ' LIKE ', '%' . $str . '%')`

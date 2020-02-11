<?php

namespace Sirius\Sql\Tests;


use Sirius\Sql\ConditionsEnum;
use Sirius\Sql\Select;

class SelectTest extends QueryTestCase
{
    public function newSelect()
    {
        return new Select($this->getConnectionMock());
    }

    public function test_statement_and_bindings()
    {
        $select = $this->newSelect();
        $select->forUpdate()
               ->from('posts')
               ->distinct()
               ->columns('title', 'content')
               ->where('id', 10)
               ->orderBy('parent_id DESC', 'published_date')
               ->perPage(10)
               ->page(2)
               ->limit(25) // overwrite with limit & offset
               ->offset(50);

        $this->assertSameStatement('SELECT DISTINCT title, content FROM posts WHERE id = :__1__ ORDER BY parent_id DESC, published_date LIMIT 25 OFFSET 50 FOR UPDATE', $select->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT]
        ], $select->getBindValues());
        $this->assertTrue($select->hasColumns());
    }

    public function test_union()
    {
        $select = $this->newSelect();
        $select->columns('*')
               ->from('comments')
               ->union()
               ->columns('*')
               ->from('posts')
               ->unionAll()
               ->columns('*')
               ->from('users');

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    comments
UNION
SELECT
    *
FROM
    posts
UNION ALL
SELECT
    *
FROM
    users
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_group_by()
    {
        $select = $this->newSelect();
        $select->from('comments')
               ->columns('post_id', 'COUNT(id) AS comments')
               ->groupBy('post_id');

        $expectedStatement = <<<SQL
SELECT
    post_id,
    COUNT(id) AS comments
FROM
    comments
GROUP BY
    post_id
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_having()
    {
        $select = $this->newSelect();
        $select->from('comments')
               ->columns('post_id', 'COUNT(id) AS comments')
               ->groupBy('post_id')
               ->having('comments > %s', 10)
               ->orHaving('comments = %s', 10);

        $expectedStatement = <<<SQL
SELECT
    post_id,
    COUNT(id) AS comments
FROM
    comments
GROUP BY
    post_id
HAVING comments > :__1__ OR comments = :__2__
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_subselect()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->join(
                   'INNER',
                   $select->subSelect()
                          ->from('posts')
                          ->columns('id')
                          ->where('title', 'ABC', ConditionsEnum::CONTAINS)
                          ->as('parents'),
                   'posts.parent_id = parents.id'
               )
               ->limit(10)
               ->offset(100)
               ->perPage(10) // overwrite with pagination
               ->page(2);


        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
    INNER JOIN (
        SELECT
            id
        FROM
            posts
        WHERE
            title LIKE :__1__
    ) AS parents ON posts.parent_id = parents.id
    LIMIT 10 OFFSET 10
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_joins()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('title', 'content')
               ->join('INNER JOIN', 'posts as parents', 'posts.parent_id = parents.id AND parents.type = %s', 'page')
               ->where('id', 10);

        $this->assertSameStatement('SELECT title, content FROM posts INNER JOIN posts as parents ON posts.parent_id = parents.id AND parents.type = :__1__ WHERE id = :__2__', $select->getStatement());
        $this->assertSame([
            '__1__' => ['page', \PDO::PARAM_STR],
            '__2__' => [10, \PDO::PARAM_INT]
        ], $select->getBindValues());

    }

    public function test_nested_conditions()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('id', 10)
               ->whereStartSet()
               ->where('parent_id', 10)
               ->orWhere('parent_id', 11)
               ->orWhereStartSet()
               ->where('parent_id', 12)
               ->where('parent_id', 13)
               ->endSet()
               ->endSet();

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    id = :__1__
    AND (
        parent_id = :__2__
        OR parent_id = :__3__
        OR (
            parent_id = :__4__
            AND parent_id = :__5__
        )
    )
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
        $this->assertSame([
            '__1__' => [10, \PDO::PARAM_INT],
            '__2__' => [10, \PDO::PARAM_INT],
            '__3__' => [11, \PDO::PARAM_INT],
            '__4__' => [12, \PDO::PARAM_INT],
            '__5__' => [13, \PDO::PARAM_INT],
        ], $select->getBindValues());
    }


    public function test_auto_closing_groups()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('id', 10)
               ->whereStartSet()
               ->where('parent_id', 10)
               ->orWhere('parent_id', 11);

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    id = :__1__
    AND (
        parent_id = :__2__
        OR parent_id = :__3__
    )
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_grouping_current_conditions()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('id', 10)
               ->orWhere('id', 11)
               ->groupCurrentWhere()
               ->where('title', 'abc');

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    (id = :__1__
    OR id = :__2__)
    AND title = :__3__
SQL;

        $this->assertSamestatement($expectedStatement, $select->getStatement());
    }

    public function test_raw_conditions()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('published_date > NOW()')
               ->orWhere('status = "draft"');

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    published_date > NOW()
    OR status = "draft"
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_where_all_conditions()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->whereAll([
                   'id'             => [10, 11],
                   'author_id'      => 10,
                   'published_date' => $select->raw('NOW()'),
                   'parent_id'      => null,
                   'deleted_at IS NULL'
               ]);

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    (id IN (:__1__, :__2__)
    AND author_id = :__3__
    AND published_date = NOW()
    AND parent_id IS NULL
    AND deleted_at IS NULL)
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
    }

    public function test_sprintf_conditions()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->whereSprintf('published_date > %s AND published_date <= %s', '2010-01-01', '2010-12-31')
               ->orWhereSprintf('status = %s', 'draft');

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    published_date > :__1__ AND published_date <= :__2__
    OR status = :__3__
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
        $this->assertSame([
            '__1__' => ['2010-01-01', \PDO::PARAM_STR],
            '__2__' => ['2010-12-31', \PDO::PARAM_STR],
            '__3__' => ['draft', \PDO::PARAM_STR],
        ], $select->getBindValues());
    }

    public function test_condition_enum_rules()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('id', [10, 11, 12, 13])
               ->where('title', 'ABC', ConditionsEnum::STARTS_WITH)
               ->where('title', 'ABCD', ConditionsEnum::DOES_NOT_START_WITH)
               ->where('title', 'XYZ', ConditionsEnum::ENDS_WITH)
               ->where('title', 'XYZW', ConditionsEnum::DOES_NOT_END_WITH)
               ->where('content', 'qwerty', ConditionsEnum::CONTAINS)
               ->where('content', 'zxcvbn', ConditionsEnum::DOES_NOT_CONTAIN)
               ->where('published_at', ['2010-01-01', '2010-12-31'], ConditionsEnum::BETWEEN)
               ->where('published_at', $select->raw('NOW()'), '<')
               ->where('parent_id', [])
               ->where('author_id', null, ConditionsEnum::NOT_EQUALS);

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    id IN (:__1__, :__2__, :__3__, :__4__)
    AND title LIKE :__5__
    AND title NOT LIKE :__6__
    AND title LIKE :__7__
    AND title NOT LIKE :__8__
    AND content LIKE :__9__
    AND content NOT LIKE :__10__
    AND published_at BETWEEN :__11__ AND :__12__
    AND published_at < NOW()
    AND parent_id IN (:__13__)
    AND author_id IS NOT NULL
SQL;

        $this->assertSameStatement($expectedStatement, $select->getStatement());
        $this->assertSame([
            '__1__'  => [10, \PDO::PARAM_INT],
            '__2__'  => [11, \PDO::PARAM_INT],
            '__3__'  => [12, \PDO::PARAM_INT],
            '__4__'  => [13, \PDO::PARAM_INT],
            '__5__'  => ['ABC%', \PDO::PARAM_STR],
            '__6__'  => ['ABCD%', \PDO::PARAM_STR],
            '__7__'  => ['%XYZ', \PDO::PARAM_STR],
            '__8__'  => ['%XYZW', \PDO::PARAM_STR],
            '__9__'  => ['%qwerty%', \PDO::PARAM_STR],
            '__10__' => ['%zxcvbn%', \PDO::PARAM_STR],
            '__11__' => ['2010-01-01', \PDO::PARAM_STR],
            '__12__' => ['2010-12-31', \PDO::PARAM_STR],
            '__13__' => [-PHP_INT_MAX, \PDO::PARAM_INT],
        ], $select->getBindValues());
    }

    public function test_binding_methods()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('title = ' . $select->bindInline('Cool title'))
               ->where($select->bindSprintf('date BETWEEN %s AND %s', '2010-01-01', '2010-12-31'))
               ->where('parent_id = :parent_id')
               ->where('author_id = :author_id');

        $expectedStatement = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    title = :__1__
    AND date BETWEEN :__2__ AND :__3__
    AND parent_id = :parent_id
    AND author_id = :author_id
SQL;
        $select->bindValues(['parent_id' => 10]);
        $select->bindValue('author_id', 20);

        $this->assertSameStatement($expectedStatement, $select->getStatement());
        $this->assertSame([
            '__1__'     => ['Cool title', \PDO::PARAM_STR],
            '__2__'     => ['2010-01-01', \PDO::PARAM_STR],
            '__3__'     => ['2010-12-31', \PDO::PARAM_STR],
            'parent_id' => [10, \PDO::PARAM_INT],
            'author_id' => [20, \PDO::PARAM_INT],
        ], $select->getBindValues());
    }

    public function test_fetchAll()
    {
        $select = $this->newSelect();
        $select->from('posts');

        $this->assertSame([true], $select->fetchAll());
    }

    public function test_cloning()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where('title = :title')
               ->bindValue('title', 'Cool title');

        $clonedSelect = clone $select;

        $this->assertSame([
            'title' => ['Cool title', \PDO::PARAM_STR]
        ], $clonedSelect->getBindValues());
    }

    public function test_where_multiple()
    {
        $select = $this->newSelect();
        $select->from('posts')
               ->columns('*')
               ->where(['key_1', 'key_2'], [[1, 2], [3, 4]]);

        $expectedSql = <<<SQL
SELECT
    *
FROM
    posts
WHERE
    (
        (
        key_1 = :__1__
        AND key_2 = :__2__
        )
        OR (
        key_1 = :__3__
        AND key_2 = :__4__
        )
    )
SQL;

        $this->assertSameStatement($expectedSql, $select->getStatement());
        $this->assertSame([
            '__1__' => [1, \PDO::PARAM_INT],
            '__2__' => [2, \PDO::PARAM_INT],
            '__3__' => [3, \PDO::PARAM_INT],
            '__4__' => [4, \PDO::PARAM_INT],
        ], $select->getBindValues());
    }
}
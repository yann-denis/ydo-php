<?php

namespace YannDenis\YDO;

use PHPUnit\Framework\TestCase;
use YannDenis\YDO\Exception\Query\EmptyINException;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class QueryTest extends TestCase
{
    public function testPrepareSelectNoReplacements()
    {
        $aQueryAndValues = Query::prepareQueryAndValues('SELECT F.`id` FROM `foo` F', [], Query::TYPE_SELECT);

        $this->assertEquals('SELECT F.`id` FROM `foo` F', $aQueryAndValues[0]);
        $this->assertEquals([], $aQueryAndValues[1]);
    }

    public function testPrepareSelectWithBoolean()
    {
        // Check FALSE
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => false,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ false, \PDO::PARAM_BOOL ],
            ],
            $aQueryAndValues[1]
        );

        // Check TRUE
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => true,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ true, \PDO::PARAM_BOOL ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithZero()
    {
        // Check 0
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => 0,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ 0, \PDO::PARAM_INT ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithDouble()
    {
        // Check positive value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => 3.14,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ '3.14', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );

        // Check negative value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => -3.14,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ '-3.14', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithInteger()
    {
        // Check positive value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => 123,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ 123, \PDO::PARAM_INT ],
            ],
            $aQueryAndValues[1]
        );

        // Check negative value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => -123,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ -123, \PDO::PARAM_INT ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithDateTime()
    {
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => \DateTime::createFromFormat('Y-m-d H:i:s', '1987-07-23 22:45:59'),
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ '1987-07-23 22:45:59', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithString()
    {
        // Check empty value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => '',
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ '', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );

        // Check non empty value
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar',
            [
                'bar' => 'Hello World!',
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar' => [ 'Hello World!', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithNULL()
    {
        // Check IS NULL
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` = :bar OR F.`bar`=:bar OR F.`bar`   =   :bar',
            [
                'bar' => null,
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` IS NULL OR F.`bar` IS NULL OR F.`bar` IS NULL', $aQueryAndValues[0]);
        $this->assertEquals([], $aQueryAndValues[1]);

        // Check IS NOT NULL
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` != :bar OR F.`bar`!=:bar OR F.`bar`   !=   :bar OR F.`bar` <> :bar OR F.`bar`<>:bar OR F.`bar`   <>   :bar',
            [
                'bar' => null,
            ],
            Query::TYPE_SELECT);

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` IS NOT NULL OR F.`bar` IS NOT NULL OR F.`bar` IS NOT NULL OR F.`bar` IS NOT NULL OR F.`bar` IS NOT NULL OR F.`bar` IS NOT NULL', $aQueryAndValues[0]);
        $this->assertEquals([], $aQueryAndValues[1]);
    }

    public function testPrepareSelectWithCollection()
    {
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` IN (:bar) OR F.`bar` NOT IN (:bar)',
            [
                'bar' => [
                    true,
                    false,
                    0,
                    3.14,
                    -3.14,
                    123,
                    -123,
                    '',
                    'Hello World!',
                ],
            ],
            Query::TYPE_SELECT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` IN (:bar_0, :bar_1, :bar_2, :bar_3, :bar_4, :bar_5, :bar_6, :bar_7, :bar_8) OR F.`bar` NOT IN (:bar_0, :bar_1, :bar_2, :bar_3, :bar_4, :bar_5, :bar_6, :bar_7, :bar_8)', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar_0' => [ true, \PDO::PARAM_BOOL ],
                'bar_1' => [ false, \PDO::PARAM_BOOL ],
                'bar_2' => [ 0, \PDO::PARAM_INT ],
                'bar_3' => [ '3.14', \PDO::PARAM_STR ],
                'bar_4' => [ '-3.14', \PDO::PARAM_STR ],
                'bar_5' => [ 123, \PDO::PARAM_INT ],
                'bar_6' => [ -123, \PDO::PARAM_INT ],
                'bar_7' => [ '', \PDO::PARAM_STR ],
                'bar_8' => [ 'Hello World!', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );
    }

    public function testPrepareSelectWithEmptyCollection()
    {
        $this->expectException(EmptyINException::class);

        Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` IN (:bar)',
            [
                'bar' => [],
            ],
            Query::TYPE_SELECT
        );
    }

    public function testPrepareInsert()
    {
        $aQueryAndValues = Query::prepareQueryAndValues(
            'SELECT F.`id` FROM `foo` F WHERE F.`bar` IN (:bar) OR F.`bar` NOT IN (:bar)',
            [
                'bar' => [
                    true,
                    false,
                    0,
                    3.14,
                    -3.14,
                    123,
                    -123,
                    '',
                    'Hello World!',
                ],
            ],
            Query::TYPE_INSERT
        );

        $this->assertEquals('SELECT F.`id` FROM `foo` F WHERE F.`bar` IN (:bar_0, :bar_1, :bar_2, :bar_3, :bar_4, :bar_5, :bar_6, :bar_7, :bar_8) OR F.`bar` NOT IN (:bar_0, :bar_1, :bar_2, :bar_3, :bar_4, :bar_5, :bar_6, :bar_7, :bar_8)', $aQueryAndValues[0]);
        $this->assertEquals(
            [
                'bar_0' => [ true, \PDO::PARAM_BOOL ],
                'bar_1' => [ false, \PDO::PARAM_BOOL ],
                'bar_2' => [ 0, \PDO::PARAM_INT ],
                'bar_3' => [ '3.14', \PDO::PARAM_STR ],
                'bar_4' => [ '-3.14', \PDO::PARAM_STR ],
                'bar_5' => [ 123, \PDO::PARAM_INT ],
                'bar_6' => [ -123, \PDO::PARAM_INT ],
                'bar_7' => [ '', \PDO::PARAM_STR ],
                'bar_8' => [ 'Hello World!', \PDO::PARAM_STR ],
            ],
            $aQueryAndValues[1]
        );
    }
}

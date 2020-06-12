<?php

namespace Yann\YDO;

use PHPUnit\Framework\TestCase;

/**
 * @author Yann DENIS <denis.yann@gmail.com>
 */
class DataTest extends TestCase
{
    public function testDecodeBoolean()
    {
        // Check FALSE
        $this->assertEquals(false, Data::decode('0', YDO::TYPE_BOOL));
        $this->assertEquals(false, Data::decode('0', YDO::TYPE_BOOLEAN));

        // Check TRUE
        $this->assertEquals(true, Data::decode('1', YDO::TYPE_BOOL));
        $this->assertEquals(true, Data::decode('1', YDO::TYPE_BOOLEAN));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_BOOL));
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_BOOLEAN));
    }

    public function testDecodeInteger()
    {
        // Check zero
        $this->assertEquals(0, Data::decode('0', YDO::TYPE_INT));
        $this->assertEquals(0, Data::decode('0', YDO::TYPE_INTEGER));

        // Check positive value
        $this->assertEquals(123, Data::decode('123', YDO::TYPE_INT));
        $this->assertEquals(123, Data::decode('123', YDO::TYPE_INTEGER));

        // Check negative value
        $this->assertEquals(-123, Data::decode('-123', YDO::TYPE_INT));
        $this->assertEquals(-123, Data::decode('-123', YDO::TYPE_INTEGER));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_INT));
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_INTEGER));
    }

    public function testDecodeFloat()
    {
        // Check zero
        $this->assertEquals(0.0, Data::decode('0', YDO::TYPE_DOUBLE));
        $this->assertEquals(0.0, Data::decode('0', YDO::TYPE_FLOAT));

        // Check positive value without round
        $this->assertEquals(123.2, Data::decode('123.2', YDO::TYPE_DOUBLE));
        $this->assertEquals(123.2, Data::decode('123.2', YDO::TYPE_FLOAT));
        $this->assertEquals(123.8, Data::decode('123.8', YDO::TYPE_DOUBLE));
        $this->assertEquals(123.8, Data::decode('123.8', YDO::TYPE_FLOAT));

        // Check negative value without round
        $this->assertEquals(-123.2, Data::decode('-123.2', YDO::TYPE_DOUBLE));
        $this->assertEquals(-123.2, Data::decode('-123.2', YDO::TYPE_FLOAT));
        $this->assertEquals(-123.8, Data::decode('-123.8', YDO::TYPE_DOUBLE));
        $this->assertEquals(-123.8, Data::decode('-123.8', YDO::TYPE_FLOAT));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_DOUBLE));
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_FLOAT));
    }

    public function testDecodeString()
    {
        // Check empty
        $this->assertEquals('', Data::decode('', YDO::TYPE_STR));
        $this->assertEquals('', Data::decode('', YDO::TYPE_STRING));

        // Check not empty
        $this->assertEquals('Hello World!', Data::decode('Hello World!', YDO::TYPE_STR));
        $this->assertEquals('Hello World!', Data::decode('Hello World!', YDO::TYPE_STRING));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_STR));
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_STRING));
    }

    public function testDecodeSerialize()
    {
        // Check boolean
        $this->assertEquals(true, Data::decode('b:1;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(true, Data::decode('b:1;', YDO::TYPE_SERIALIZED));
        $this->assertEquals(false, Data::decode('b:0;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(false, Data::decode('b:0;', YDO::TYPE_SERIALIZED));

        // Check integer
        $this->assertEquals(42, Data::decode('i:42;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(42, Data::decode('i:42;', YDO::TYPE_SERIALIZED));
        $this->assertEquals(-42, Data::decode('i:-42;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(-42, Data::decode('i:-42;', YDO::TYPE_SERIALIZED));

        // Check float
        $this->assertEquals(3.14, Data::decode('d:3.14;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(3.14, Data::decode('d:3.14;', YDO::TYPE_SERIALIZED));
        $this->assertEquals(-3.14, Data::decode('d:-3.14;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(-3.14, Data::decode('d:-3.14;', YDO::TYPE_SERIALIZED));

        // Check string
        $this->assertEquals('', Data::decode('s:0:"";', YDO::TYPE_SERIALIZE));
        $this->assertEquals('', Data::decode('s:0:"";', YDO::TYPE_SERIALIZED));
        $this->assertEquals('Hello World!', Data::decode('s:12:"Hello World!";', YDO::TYPE_SERIALIZE));
        $this->assertEquals('Hello World!', Data::decode('s:12:"Hello World!";', YDO::TYPE_SERIALIZED));

        // Check NULL
        $this->assertEquals(null, Data::decode('N;', YDO::TYPE_SERIALIZE));
        $this->assertEquals(null, Data::decode('N;', YDO::TYPE_SERIALIZED));

        // Check array
        $this->assertEquals(
            [ true, false, 42, -42, 3.14, -3.14, '', 'Hello World!', null ],
            Data::decode('a:9:{i:0;b:1;i:1;b:0;i:2;i:42;i:3;i:-42;i:4;d:3.14;i:5;d:-3.14;i:6;s:0:"";i:7;s:12:"Hello World!";i:8;N;}', YDO::TYPE_SERIALIZE)
        );
        $this->assertEquals(
            [ true, false, 42, -42, 3.14, -3.14, '', 'Hello World!', null ],
            Data::decode('a:9:{i:0;b:1;i:1;b:0;i:2;i:42;i:3;i:-42;i:4;d:3.14;i:5;d:-3.14;i:6;s:0:"";i:7;s:12:"Hello World!";i:8;N;}', YDO::TYPE_SERIALIZED)
        );

        // Check associative array
        $this->assertEquals(
            [ '1st' => true, '2nd' => false, '3rd' => 42, '4th' => -42, '5th' => 3.14, '6th' => -3.14, '7th' => '', '8th' => 'Hello World!', '9th' => null ],
            Data::decode('a:9:{s:3:"1st";b:1;s:3:"2nd";b:0;s:3:"3rd";i:42;s:3:"4th";i:-42;s:3:"5th";d:3.14;s:3:"6th";d:-3.14;s:3:"7th";s:0:"";s:3:"8th";s:12:"Hello World!";s:3:"9th";N;}', YDO::TYPE_SERIALIZE)
        );
        $this->assertEquals(
            [ '1st' => true, '2nd' => false, '3rd' => 42, '4th' => -42, '5th' => 3.14, '6th' => -3.14, '7th' => '', '8th' => 'Hello World!', '9th' => null ],
            Data::decode('a:9:{s:3:"1st";b:1;s:3:"2nd";b:0;s:3:"3rd";i:42;s:3:"4th";i:-42;s:3:"5th";d:3.14;s:3:"6th";d:-3.14;s:3:"7th";s:0:"";s:3:"8th";s:12:"Hello World!";s:3:"9th";N;}', YDO::TYPE_SERIALIZED)
        );
    }

    public function testDecodeDate()
    {
        // Check date
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '1987-07-23 00:00:00'), Data::decode('1987-07-23', YDO::TYPE_DATE));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_DATE));
    }

    public function testDecodeDateTime()
    {
        // Check date
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d H:i:s', '1987-07-23 22:45:13'), Data::decode('1987-07-23 22:45:13', YDO::TYPE_DATETIME));

        // Check NULL
        $this->assertEquals(null, Data::decode(null, YDO::TYPE_DATETIME));
    }
}

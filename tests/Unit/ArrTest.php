<?php

namespace Ekok\Utils\Tests;

use Ekok\Utils\Arr;
use Ekok\Utils\Payload;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testEach()
    {
        $src = array('foo', 'bar', 'baz');

        $this->assertEquals($src, Arr::each($src, fn(Payload $item) => $item));
        $this->assertEquals(array('f', 'b', 'b'), Arr::each($src, fn(Payload $item) => substr($item->value, 0, 1)));
        $this->assertEquals(array('foo', 'baz'), Arr::each($src, fn(Payload $item) => $item->value === 'bar' ? $item->value(null) : $item->key(null), true));
    }

    public function testFilter()
    {
        $src = array('foo' => 1, 'one');

        $this->assertEquals($src, Arr::filter($src + array('x' => false), fn(Payload $item) => ($item->keyType('string') || $item->valType('string')) && $item->value));
        $this->assertEquals(array('one'), Arr::filter($src, fn(Payload $item) => $item->indexed()));
    }

    public function testWalk()
    {
        $expected = array('foo', 'bar', 'baz');
        $actual = array();

        Arr::walk($expected, function (Payload $item) use (&$actual) {
            $actual[] = $item->value;
        });

        $this->assertSame($expected, $actual);
    }

    public function testSome()
    {
        $this->assertTrue(Arr::some(array('foo', 'bar'), fn (Payload $item) => $item->value === 'bar', $actual));
        $this->assertEquals('bar', $actual);
        $this->assertFalse(Arr::some(array('foo', 'bar'), fn (Payload $item) => $item->value === 'baz'));
    }

    public function testEvery()
    {
        $this->assertTrue(Arr::every(array('foo', 'bar'), fn (Payload $item) => !!$item->value));
        $this->assertFalse(Arr::every(array('foo', 'bar'), fn (Payload $item) => $item->value === 'foo'));
    }

    public function testFirst()
    {
        $expected = 'foo';
        $actual = Arr::first(array('foo', 'bar'), fn(Payload $item) => $item);
        $second = Arr::first(array(null), fn(Payload $item) => $item);

        $this->assertSame($expected, $actual);
        $this->assertNull($second);
    }

    public function testReduce()
    {
        $expected = '1,2,3,4,5';
        $actual = Arr::reduce(range(1, 5), fn ($prev, Payload $item) => $prev . ($item->key > 0 ? ',' : '') . $item->value);

        $this->assertSame($expected, $actual);
    }

    public function testMerge()
    {
        $expected = array('one' => 4, 'two' => 2);
        $actual = Arr::merge(array('one' => 1), array('two' => 2, 'one' => 4), null);

        $this->assertSame($expected, $actual);
    }

    public function testWithout()
    {
        $expected = array('one' => 1, 'three' => 3);
        $actual = Arr::without(array('one' => 1, 'two' => 2, 'three' => 3), 'two');

        $this->assertSame($expected, $actual);
    }

    public function testEnsure()
    {
        $expected = array('1', '2', '3');
        $actual = Arr::ensure('1,2,3,');
        $actual2 = Arr::ensure($expected);

        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actual2);
    }

    public function testFill()
    {
        $expected = array('1' => true, '2' => true, '3' => true);
        $actual = Arr::fill('1,2,3,');

        $this->assertSame($expected, $actual);
    }
}

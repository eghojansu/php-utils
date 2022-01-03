<?php

namespace Ekok\Utils\Tests;

use Ekok\Utils\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testEach()
    {
        $src = array('foo', 'bar', 'baz');

        $this->assertEquals($src, Arr::each($src, fn($item) => $item));
        $this->assertEquals(array('f', 'b', 'b'), Arr::each($src, fn($item) => substr($item->value, 0, 1)));
        $this->assertEquals(array('foo', 'baz'), Arr::each($src, fn($item) => $item->value === 'bar' ? $item->value(null) : $item->key(null), true));
    }

    public function testFilter()
    {
        $src = array('foo' => 1, 'one');

        $this->assertEquals($src, Arr::filter($src + array('x' => false), fn($item) => ($item->keyType('string') || $item->valType('string')) && $item->value));
    }

    public function testWalk()
    {
        $expected = array('foo', 'bar', 'baz');
        $actual = array();

        Arr::walk($expected, function ($item) use (&$actual) {
            $actual[] = $item->value;
        });

        $this->assertSame($expected, $actual);
    }

    public function testSome()
    {
        $this->assertTrue(Arr::some(array('foo', 'bar'), fn ($item) => $item->value === 'bar', $actual));
        $this->assertEquals('bar', $actual);
        $this->assertFalse(Arr::some(array('foo', 'bar'), fn ($item) => $item->value === 'baz'));
    }

    public function testEvery()
    {
        $this->assertTrue(Arr::every(array('foo', 'bar'), fn ($item) => !!$item->value));
        $this->assertFalse(Arr::every(array('foo', 'bar'), fn ($item) => $item->value === 'foo'));
    }

    public function testFirst()
    {
        $expected = 'foo';
        $actual = Arr::first(array('foo', 'bar'), fn($item) => $item);
        $second = Arr::first(array(null), fn($item) => $item);

        $this->assertSame($expected, $actual);
        $this->assertNull($second);
    }

    public function testReduce()
    {
        $expected = '1,2,3,4,5';
        $actual = Arr::reduce(range(1, 5), fn ($prev, $item) => $prev . ($item->key > 0 ? ',' : '') . $item->value);

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
}

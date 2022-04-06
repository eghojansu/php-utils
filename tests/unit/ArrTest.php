<?php

use Ekok\Utils\Arr;

class ArrTest extends \Codeception\Test\Unit
{
    public function testFormatTrace()
    {
        $trace = Arr::formatTrace(debug_backtrace());
        $expected = __CLASS__ . '->' . __FUNCTION__;

        $this->assertNotEmpty($trace);
        $this->assertStringEndsWith($expected, $trace[0]);
    }

    public function testIndexed()
    {
        $this->assertTrue(Arr::indexed(array(1,2,3)));
        $this->assertFalse(Arr::indexed(array('foo' => 1,2,3)));
    }

    public function testFilter()
    {
        $src = array('foo' => 1, 'one');

        $this->assertEquals($src, Arr::filter($src + array('x' => false), fn($item, $key) => (is_string($key) || is_string($item)) && $item));
        $this->assertEquals(array('one'), Arr::filter($src, fn(...$args) => is_numeric($args[1])));
    }

    public function testEach()
    {
        $src = array('foo', 'bar', 'baz');

        $this->assertEquals($src, Arr::each($src, fn($item) => $item));
        $this->assertEquals(array('f', 'b', 'b'), Arr::each($src, fn($item) => substr($item, 0, 1)));
        $this->assertEquals(array('foo', 2 => 'baz'), Arr::each($src, fn($item) => $item === 'bar' ? null : $item, true));
        $this->assertEquals(array('foo', 'baz'), Arr::each($src, fn($item) => $item === 'bar' ? null : $item, true, true));
    }

    public function testSome()
    {
        $this->assertTrue(Arr::some(array('foo', 'bar'), fn ($item) => $item === 'bar', $actual));
        $this->assertEquals(array(1, 'bar'), $actual);
        $this->assertFalse(Arr::some(array('foo', 'bar'), fn ($item) => $item === 'baz'));
    }

    public function testEvery()
    {
        $this->assertTrue(Arr::every(array('foo', 'bar'), fn ($item) => !!$item));
        $this->assertFalse(Arr::every(array('foo', 'bar'), fn ($item) => $item === 'foo'));
        $this->assertFalse(Arr::every(array(), fn ($item) => $item === 'foo'));
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
        $actual = Arr::reduce(range(1, 5), fn ($prev, $item, $key) => $prev . ($key > 0 ? ',' : '') . $item);

        $this->assertSame($expected, $actual);
    }

    public function testMerge()
    {
        $expected = array('one' => 4, 'two' => 2);
        $actual = Arr::merge(array('one' => 1), array('two' => 2, 'one' => 4), null);

        $this->assertSame($expected, $actual);
    }

    public function testIgnore()
    {
        $expected = array('one' => 1, 'three' => 3);
        $actual = Arr::ignore(array('one' => 1, 'two' => 2, 'three' => 3), 'two');

        $this->assertSame($expected, $actual);
    }

    public function testEnsure()
    {
        $expected = array('1', '2', '3');
        $actual = Arr::ensure('1,2,3,');
        $actual2 = Arr::ensure($expected);

        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actual2);
        $this->assertSame(array(), Arr::ensure(null));
    }

    public function testFill()
    {
        $expected = array('1' => true, '2' => true, '3' => true);
        $actual = Arr::fill('1,2,3,');

        $this->assertSame($expected, $actual);
    }

    public function testIncludes()
    {
        $this->assertTrue(Arr::includes(array(1, 2, 3), 1));
        $this->assertTrue(Arr::includes(array(1, 2, 3), array(1, 3)));
        $this->assertFalse(Arr::includes(array(1, 2, 3), 4));
    }

    public function testQuoteKeys()
    {
        $actual = Arr::quoteKeys(array('foo' => 'bar'), '{}');
        $expected = array(
            '{foo}' => 'bar',
        );

        $this->assertSame($expected, $actual);
    }

    public function testWalk()
    {
        $last = null;

        Arr::walk(range(1,3), static function ($assign) use (&$last) {
            $last = $assign;
        });

        $this->assertSame(3, $last);
    }
}

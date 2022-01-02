<?php

namespace Ekok\Utils\Tests;

use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testEach()
    {
        $src = array('foo', 'bar', 'baz');
    }

    public function eachProvider()
    {
        $fn = static function ($value) {
            return $value ? $value . '-a' : null;
        };

        return array(
            'keep keys' => array(
                array('foo' => 'bar-a', 1 => 'baz-a', 'qux' => 'quux-a'),
                array('foo' => 'bar', 1 => 'baz', 'qux' => 'quux', 0 => null),
                $fn,
                true,
            ),
            'indexed keys' => array(
                array('bar-a', 'baz-a', 'quux-a', null),
                array('foo' => 'bar', 1 => 'baz', 'qux' => 'quux', null),
                $fn,
                false,
                false,
            ),
        );
    }

    public function testWalk()
    {
        $expected = array('foo', 'bar', 'baz');
        $actual = array();

        Arr\walk($expected, function ($value) use (&$actual) {
            $actual[] = $value;
        });

        $this->assertSame($expected, $actual);
    }

    public function testFirst()
    {
        $expected = 'foo';
        $actual = Arr\first(array('foo', 'bar'), fn ($value) => $value);
        $second = Arr\first(array(null), fn ($value) => $value);

        $this->assertSame($expected, $actual);
        $this->assertNull($second);
    }

    public function testReduce()
    {
        $expected = '1,2,3,4,5';
        $actual = Arr\reduce(range(1, 5), fn ($prev, $value, $key) => $prev . ($key > 0 ? ',' : '') . $value);

        $this->assertSame($expected, $actual);
    }

    public function testMerge()
    {
        $expected = array('one' => 4, 'two' => 2);
        $actual = Arr\merge(array('one' => 1), array('two' => 2, 'one' => 4), null);

        $this->assertSame($expected, $actual);
    }

    public function testWithout()
    {
        $expected = array('one' => 1, 'three' => 3);
        $actual = Arr\without(array('one' => 1, 'two' => 2, 'three' => 3), 'two');

        $this->assertSame($expected, $actual);
    }

    public function testEnsure()
    {
        $expected = array(1, 2, 3);
        $actual = Arr\ensure('1,2,3,');
        $actual2 = Arr\ensure($expected);

        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actual2);
    }
}

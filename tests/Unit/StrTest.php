<?php

namespace Ekok\Utils\Tests;

use Ekok\Utils\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /** @dataProvider fixslashesProvider */
    public function testFixSlashes(string $expected, ...$arguments)
    {
        $actual = Str::fixslashes(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function fixslashesProvider()
    {
        return array(
            array('/foo/bar', '\\foo//bar'),
            array('foo/bar/', 'foo//bar\\'),
            array('', ''),
        );
    }

    /** @dataProvider splitProvider */
    public function testSplit(array $expected, ...$arguments)
    {
        $actual = Str::split(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function splitProvider()
    {
        return array(
            array(array('foo', 'bar'), 'foo,bar'),
            array(array('foo', ' bar'), 'foo, bar'),
            array(array('foo', ' bar', ' '), 'foo, bar, ,'),
        );
    }

    public function testQuote()
    {
        $this->assertSame('"foo"', Str::quote('foo'));
        $this->assertSame('"foo"."bar"', Str::quote('foo.bar'));
        $this->assertSame('`foo`.`bar`', Str::quote('foo.bar', '`'));
        $this->assertSame('[foo]', Str::quote('foo', '[', ']'));
        $this->assertSame('[foo].[bar]', Str::quote('foo.bar', '[', ']'));
    }

    /** @dataProvider caseSnakeProvider */
    public function testCaseSnake(string $expected, ...$arguments)
    {
        $actual = Str::caseSnake(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function caseSnakeProvider()
    {
        return array(
            array('snake_case', 'snakeCase'),
            array('snake_case', 'SnakeCase'),
        );
    }

    /** @dataProvider caseCamelProvider */
    public function testCaseCamel(string $expected, ...$arguments)
    {
        $actual = Str::caseCamel(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function caseCamelProvider()
    {
        return array(
            array('camelCase', 'camel_case'),
            array('camelCase', 'Camel_Case'),
        );
    }

    /** @dataProvider casePascalProvider */
    public function testPascalCamel(string $expected, ...$arguments)
    {
        $actual = Str::casePascal(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function casePascalProvider()
    {
        return array(
            array('PascalCase', 'pascal_case'),
            array('PascalCase', 'Pascal_Case'),
        );
    }

    public function testClassName()
    {
        $this->assertSame('FooBar', Str::className('FooBar'));
        $this->assertSame('FooBar', Str::className('Test\\FooBar'));
        $this->assertSame('foo_bar', Str::className('FooBar', true));
    }

    public function testRandom()
    {
        $this->assertNotEquals(Str::random(), Str::random());
        $this->assertNotEquals(Str::random(), Str::random());
        $this->assertNotEquals(Str::random(), Str::random());
        $this->assertNotEquals(Str::random(), Str::random());
        $this->assertNotEquals(Str::random(), Str::random());
        $this->assertEquals(5, strlen(Str::random(5)));
        $this->assertTrue(!!preg_match('/[[:lower:]]/', Str::random()));
        $this->assertTrue(!preg_match('/[[:lower:]]/', Str::random(8, false)));
    }

    public function testCast()
    {
        $this->assertSame(1234, Str::cast('1234'));
        $this->assertSame(83, Str::cast('0123'));
        $this->assertSame(26, Str::cast('0x1A'));
        $this->assertSame(255, Str::cast('0b11111111'));
        $this->assertSame(true, Str::cast('true'));
        $this->assertSame(null, Str::cast('null'));
        $this->assertSame('1_234_567', Str::cast('1_234_567'));
    }
}

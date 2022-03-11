<?php

use Ekok\Utils\Str;

class StrTest extends \Codeception\Test\Unit
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
            array('foo/bar/', 'foo//\\//\\bar\\/'),
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
        $this->assertSame('"foo.bar"', Str::quote('foo.bar'));
        $this->assertSame('"foo"."bar"', Str::quote('foo.bar', null, '.'));
        $this->assertSame('`foo`.`bar`', Str::quote('foo.bar', '`', '.'));
        $this->assertSame('[foo]', Str::quote('foo', '[]'));
        $this->assertSame('[foo].[bar]', Str::quote('foo.bar', array('[', ']'), '.'));
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

    /** @dataProvider caseTitleProvider */
    public function testTitleCamel(string $expected, ...$arguments)
    {
        $actual = Str::caseTitle(...$arguments);

        $this->assertSame($expected, $actual);
    }

    public function caseTitleProvider()
    {
        return array(
            array('Pascal Case', 'pascal_case'),
            array('Pascal Case', 'Pascal_Case'),
            array('Pascal Case', 'Pascal-Case'),
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

    public function testStartsWith()
    {
        $this->assertSame('foo', Str::startsWith('foobar', 'foo'));
        $this->assertSame('foo', Str::startsWith('foobar', 'bar', 'foo'));
        $this->assertNull(Str::startsWith('foobar', 'bar'));
    }

    public function testEndsWith()
    {
        $this->assertSame('bar', Str::endsWith('foobar', 'bar'));
        $this->assertSame('bar', Str::endsWith('foobar', 'foo', 'bar'));
        $this->assertNull(Str::endsWith('foobar', 'foo'));
    }

    public function testHash()
    {
        $this->assertSame('1xnmsgr3l2f5f', Str::hash('foo'));
        $this->assertSame('46yp6ywiun2j', Str::hash(str_repeat('foobar', 77)));
    }
}

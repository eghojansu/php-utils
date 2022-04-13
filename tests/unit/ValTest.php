<?php

use Ekok\Utils\Val;

class ValTest extends \Codeception\Test\Unit
{
    public function testIsEmpty()
    {
        $this->assertTrue(Val::isEmpty(''));
        $this->assertTrue(Val::isEmpty(null));
        $this->assertTrue(Val::isEmpty(array()));
        $this->assertTrue(Val::isEmpty('foo', false));
    }

    public function testCompare()
    {
        $this->assertFalse(Val::compare(true, false));
        $this->assertTrue(Val::compare(static fn() => 'foo', 'foo'));
        $this->assertTrue(Val::compare(true, true));
        $this->assertTrue(Val::isTrue(fn() => true));
        $this->assertTrue(Val::isFalse(fn() => false));
    }

    public function testRef()
    {
        $source = array(
            'foo' => array(
                'bar' => array(
                    'baz' => 'qux',
                ),
                'string' => 'foobar',
                'tmp' => tmpfile(),
                'obj' => new class {
                    public $name = 'AClass';
                    public function getDescription()
                    {
                        return 'Class description';
                    }
                },
            ),
        );
        $copy = $source;

        $this->assertSame($source['foo'], Val::ref('foo', $source, false, $exists, $parts));
        $this->assertSame(array('foo'), $parts);
        $this->assertTrue($exists);

        // get with extra unused dot
        $this->assertSame('qux', Val::ref('foo..bar..baz.', $source, false, $exists, $parts));
        $this->assertSame(array('foo', 'bar', 'baz'), $parts);
        $this->assertTrue($exists);

        $this->assertNull(Val::ref('unknown', $source));
        $this->assertNull(Val::ref(1, $source, false, $exists, $parts));
        $this->assertSame(array(1), $parts);
        $this->assertFalse($exists);
        $this->assertEquals($copy, $source);

        $this->assertSame($source['foo']['bar'], Val::ref('foo.bar', $source, false, $exists, $parts));
        $this->assertSame(array('foo', 'bar'), $parts);
        $this->assertTrue($exists);

        $this->assertSame(null, Val::ref('foo.unknown.member', $source, false, $exists, $parts));
        $this->assertSame(array('foo', 'unknown', 'member'), $parts);
        $this->assertFalse($exists);

        $this->assertEquals('foobar', Val::ref('foo.string', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'string'), $parts);
        $this->assertTrue($exists);

        $this->assertEquals(null, Val::ref('foo.string.member', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'string', 'member'), $parts);
        $this->assertFalse($exists);

        $this->assertEquals(null, Val::ref('foo.tmp.member', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'tmp', 'member'), $parts);
        $this->assertFalse($exists);

        $this->assertEquals('AClass', Val::ref('foo.obj.name', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'obj', 'name'), $parts);
        $this->assertTrue($exists);

        $this->assertEquals('Class description', Val::ref('foo.obj.description', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'obj', 'description'), $parts);
        $this->assertTrue($exists);

        // adding reference
        $add = &Val::ref('add', $source, true, $exists);
        $add = 'value';

        $this->assertFalse($exists);
        $this->assertArrayHasKey('add', $source);
        $this->assertEquals('value', Val::ref('add', $source, false, $exists));
        $this->assertTrue($exists);

        $member = &Val::ref('foo.new.member', $source, true, $exists);
        $member = 'added';

        $this->assertFalse($exists);
        $this->assertArrayHasKey('new', $source['foo']);
        $this->assertArrayHasKey('member', $source['foo']['new']);
        $this->assertEquals('added', Val::ref('foo.new.member', $source, false, $exists));
        $this->assertTrue($exists);
        $this->assertEquals(array('member' => 'added'), Val::ref('foo.new', $source, false, $exists));
        $this->assertTrue($exists);

        if (is_resource($source['foo']['tmp'])) {
            fclose($source['foo']['tmp']);
        }

        if (is_resource($copy['foo']['tmp'])) {
            fclose($copy['foo']['tmp']);
        }
    }

    public function testRefEscaping()
    {
        $src = array(
            'foo' => array(
                'bar.baz' => 'qux',
            ),
        );

        $this->assertSame('qux', Val::ref('foo.bar\.baz', $src, false, $exists, $parts));
        $this->assertTrue($exists);
        $this->assertSame(array('foo', 'bar.baz'), $parts);

        $this->assertSame(array('bar.baz' => 'qux'), Val::ref('foo', $src, false, $exists, $parts));
        $this->assertTrue($exists);
        $this->assertSame(array('foo'), $parts);
    }

    public function testUnref()
    {
        $src = fn() => array(
            'bar' => array(
                'baz' => 'qux',
            ),
            'obj' => new class {
                public $data = 'data';
                public $data2 = 'data2';
                public function removeData()
                {
                    $this->data = 'removed';
                }
            },
        );
        $data = $src();

        Val::unref('bar.baz', $data);
        Val::unref('obj.data', $data);
        Val::unref('obj.data2', $data);

        $this->assertArrayNotHasKey('baz', $data['bar']);
        $this->assertEquals('removed', $data['obj']->data);
        $this->assertFalse(isset($data['obj']->data2));

        // remove directly
        Val::unref('bar', $data);

        $this->assertArrayNotHasKey('bar', $data);
    }

    public function testCast()
    {
        $this->assertSame(1234, Val::cast('1234'));
        $this->assertSame(83, Val::cast('0123'));
        $this->assertSame(26, Val::cast('0x1A'));
        $this->assertSame(255, Val::cast('0b11111111'));
        $this->assertSame(true, Val::cast('true'));
        $this->assertSame(null, Val::cast('null'));
        $this->assertSame('1_234_567', Val::cast('1_234_567'));
    }

    public function testAccess()
    {
        $obj = new class {
            public function getFoo() {}
            public function isFooBar() {}
            public function setBar() {}
        };

        $this->assertFalse(Val::access($obj, 'none'));
        $this->assertTrue(Val::access($obj, 'foo', $method));
        $this->assertSame('getfoo', $method);

        $this->assertTrue(Val::access($obj, 'foo_bar', $method));
        $this->assertSame('isFooBar', $method);

        $this->assertTrue(Val::access($obj, 'bar', $method, true, true));
        $this->assertSame('setbar', $method);

        $this->assertFalse(Val::access($obj, 'bar'));
    }
}

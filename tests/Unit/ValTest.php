<?php

namespace Ekok\Utils\Tests;

use Ekok\Utils\Val;
use PHPUnit\Framework\TestCase;

class ValTest extends TestCase
{

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

        $this->assertEquals($source['foo'], Val::ref('foo', $source, false, $exists, $parts));
        $this->assertEquals(array('foo'), $parts);
        $this->assertTrue($exists);
        // get ref with extra dot (invalid)
        $this->assertEquals($source['foo'], Val::ref('foo.', $source));

        $this->assertEquals(null, Val::ref('unknown', $source));
        $this->assertEquals(null, Val::ref(1, $source, false, $exists, $parts));
        $this->assertEquals(array(1), $parts);
        $this->assertFalse($exists);
        $this->assertEquals($copy, $source);

        $this->assertEquals($source['foo']['bar'], Val::ref('foo.bar', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'bar'), $parts);
        $this->assertTrue($exists);

        $this->assertEquals(null, Val::ref('foo.unknown.member', $source, false, $exists, $parts));
        $this->assertEquals(array('foo', 'unknown', 'member'), $parts);
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
}

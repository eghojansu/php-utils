<?php

use Ekok\Utils\Call;
use Ekok\Utils\Context;

class CallTest extends \Codeception\Test\Unit
{
    public function testCheck()
    {
        $this->assertTrue(Call::check('foo@bar', $pos));
        $this->assertSame(3, $pos);

        $this->assertTrue(Call::check('foo:bar', $pos));
        $this->assertSame(3, $pos);

        $this->assertTrue(Call::check('foo::bar', $pos));
        $this->assertSame(3, $pos);

        $this->assertFalse(Call::check('foo'));
    }

    public function testStandarize()
    {
        $this->assertSame('foo@bar', Call::standarize('foo', 'bar'));
        $this->assertSame('foo::bar', Call::standarize('foo', 'bar', true));
        $this->assertCount(2, Call::standarize(new stdClass(), 'bar'));
    }

    public function testChain()
    {
        $actual = Call::chain(
            1,
            static fn (int $no, Context $ctx) => $ctx->push($no, 2), // val: 1, 2
            static fn (int $no, Context $ctx) => $ctx->unshift(array($no)), // val: [1], 1, 2
            static fn (int $no, Context $ctx) => $ctx->shift(), // val: 1, 2
            static fn (array $nos, Context $ctx) => $ctx->pop(), // val: 2
            static fn (int $no, Context $ctx) => $ctx->setData($no), // val: 2
            static fn (int $no, Context $ctx) => $ctx->setValue($ctx->getData()), // val: 2
        );
        $expected = array(2);

        $this->assertSame($expected, $actual);
    }
}

<?php

use Ekok\Utils\Time;

class TimeTest extends \Codeception\Test\Unit
{
    public function testStamp()
    {
        $this->assertSame(date('Y-m-d H:i:s'), Time::now());
        $this->assertSame(date('Y-m-d'), Time::now('Y-m-d'));
    }

    public function testElapsed()
    {
        $mark = Time::mark();

        $this->assertGreaterThan(0, Time::elapsed($mark));
        $this->assertMatchesRegularExpression('/^[.\d]+ seconds$/', Time::elapsedTime($mark));
    }
}

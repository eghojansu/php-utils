<?php

namespace Ekok\Utils\Tests;

use Ekok\Utils\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testFile()
    {
        $tmp = TEMP_ROOT . '/file-touch';
        $file = $tmp . '/test.txt';

        if (is_dir($tmp)) {
            $files = glob($tmp . '/*.txt');

            array_map('unlink', $files);
            rmdir($tmp);
        }

        $this->assertTrue(File::touch($file));
        $this->assertFileExists($file);
        $this->assertSame('', file_get_contents($file));
        $this->assertTrue(File::touch($file));

        $this->assertTrue(File::touch($file, 'foo'));
        $this->assertSame('foo', file_get_contents($file));

        unlink($file);
        $this->assertTrue(File::touch($file, 'bar'));
        $this->assertSame('bar', file_get_contents($file));

        $this->assertSame('ok', File::load(TEST_FIXTURES . '/files/load.php', $exists));
        $this->assertTrue($exists);
        $this->assertNull(File::load('__none__.php', $exists));
        $this->assertFalse($exists);
    }
}

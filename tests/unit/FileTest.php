<?php

use Ekok\Utils\File;

class FileTest extends \Codeception\Test\Unit
{
    public function testFile()
    {
        $tmp = TEST_TMP . '/file-touch';
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

        $expected = array($file);
        $actual = array_keys(iterator_to_array(File::traverse(TEST_TMP)));

        $this->assertSame($expected, $actual);

        unlink($file);
        $this->assertTrue(File::touch($file, 'bar'));
        $this->assertSame('bar', file_get_contents($file));

        $this->assertSame('ok', File::load(TEST_DATA . '/files/load.php', array('return' => 'ok'), $exists));
        $this->assertTrue($exists);
        $this->assertNull(File::load('__none__.php', null, $exists));
        $this->assertFalse($exists);
    }
}

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
        $this->assertSame($expected, File::traverseGlob(TEST_TMP . '/*.txt'));

        unlink($file);
        $this->assertTrue(File::touch($file, 'bar'));
        $this->assertSame('bar', file_get_contents($file));

        $this->assertSame('ok', File::load(TEST_DATA . '/files/load.php', array('return' => 'ok')));
        $this->assertSame('ok', File::load(TEST_DATA . '/files/load.php', array('return' => 'ok'), true, $output));
        $this->assertSame('', $output);
        $this->assertSame(null, File::load(TEST_DATA . '/files/none.php'));
    }

    public function testLoadFileNotExists()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessageMatches('/^File not found: .+\/none.php$/');

        File::load(TEST_DATA . '/files/none.php', null, false);
    }

    public function testLoadFileErrorThrown()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessageMatches('/^Error in file: .+\/error.php \(Error from loaded file\)$/');

        File::load(TEST_DATA . '/files/error.php', null, false);
    }

    public function testGetClasses()
    {
        $actual = File::getClassByScan(TEST_PROJECT . '/src/*.php');
        $expected = array(
            'Ekok\\Utils\\Arr',
            'Ekok\\Utils\\Call',
            'Ekok\\Utils\\Context',
            'Ekok\\Utils\\File',
            'Ekok\\Utils\\Str',
            'Ekok\\Utils\\Val',
        );

        $this->assertSame($expected, $actual);
    }

    public function testGetSymbolsNothing()
    {
        $this->assertCount(0, File::getSymbols('any'));
    }
}

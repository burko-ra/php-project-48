<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Cli\getFullPath;

class ComparisonTest extends TestCase
{
    public function testGendiffStylish(): void
    {
        $file1 = 'tests/fixtures/file1.json';
        $file2 = 'tests/fixtures/file2.json';
        $file3 = 'tests/fixtures/file1.yml';
        $file4 = 'tests/fixtures/file2.yaml';
        $fileResult = 'tests/fixtures/gendiffStylish';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2));
        $this->assertEquals($result, gendiff($file3, $file4));
        $this->assertEquals($result, gendiff($file1, $file2), 'stylish');
    }

    public function testGendiffPlain(): void
    {
        $file1 = 'tests/fixtures/file1.json';
        $file2 = 'tests/fixtures/file2.yaml';
        $fileResult = 'tests/fixtures/gendiffPlain';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2, 'plain'));
    }

    public function testGendiffJson(): void
    {
        $file1 = 'tests/fixtures/file1.json';
        $file2 = 'tests/fixtures/file2.yaml';
        $fileResult = 'tests/fixtures/gendiffJson';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2, 'json'));
    }
}

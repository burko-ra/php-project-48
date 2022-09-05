<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Comparison\stringifyIfIndexArray;
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

    public function teststringifyIfIndexArray(): void
    {
        $var1 = 1;
        $var2 = ['key1' => 'value1', 'key2' => ['key3' => 'value3']];
        $var3 = [1, 4, [7, 10, [13]]];

        $this->assertEquals($var1, stringifyIfIndexArray($var1));
        $this->assertEquals($var2, stringifyIfIndexArray($var2));

        $result3 = "[1, 4, [7, 10, [13]]]";
        $this->assertEquals($result3, stringifyIfIndexArray($var3));
    }
}

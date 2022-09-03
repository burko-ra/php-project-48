<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Cli\getFullPath;

class ComparisonTest extends TestCase
{
    public function testGendiffJson(): void
    {
        $file1 = 'tests/fixtures/nested1.json';
        $file2 = 'tests/fixtures/nested2.json';
        $fileResult = 'tests/fixtures/gendiffNestedStylish';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2));
    }

    public function testGendiffYaml(): void
    {
        $file1 = 'tests/fixtures/nested1.yml';
        $file2 = 'tests/fixtures/nested2.yaml';
        $fileResult = 'tests/fixtures/gendiffNestedStylish';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2));
    }

    public function testGendiffPlain(): void
    {
        $file1 = 'tests/fixtures/nested1.json';
        $file2 = 'tests/fixtures/nested2.yaml';
        $fileResult = 'tests/fixtures/gendiffNestedPlain';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2, 'plain'));
    }

    public function testGendiffJson(): void
    {
        $file1 = 'tests/fixtures/nested1.json';
        $file2 = 'tests/fixtures/nested2.yaml';
        $fileResult = 'tests/fixtures/gendiffNestedJson';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->assertEquals($result, gendiff($file1, $file2, 'json'));
    }
}

<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Comparison\getFullPath;

class ComparisonTest extends TestCase
{
    public function testGendiffFlatJson(): void
    {
        $file1 = 'tests/fixtures/flat1.json';
        $file2 = 'tests/fixtures/flat2.json';
        $fileResult = 'tests/fixtures/gendiffFlatJson';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->expectOutputString($result);

        gendiff($file1, $file2, 'stylish');
    }

    public function testGendiffFlatYaml(): void
    {
        $file3 = 'tests/fixtures/flat1.yml';
        $file4 = 'tests/fixtures/flat2.yaml';
        $fileResult = 'tests/fixtures/gendiffFlatJson';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->expectOutputString($result);
        gendiff($file3, $file4, 'stylish');
    }
}

<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Cli\getFullPath;

class ComparisonTest extends TestCase
{
    public function testGendiffFlatJson(): void
    {
        $file1 = 'tests/fixtures/nested1.json';
        $file2 = 'tests/fixtures/nested2.json';
        $fileResult = 'tests/fixtures/gendiffNested';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->expectOutputString($result);

        gendiff($file1, $file2, 'stylish');
    }

    public function testGendiffFlatYaml(): void
    {
        $file3 = 'tests/fixtures/nested1.yml';
        $file4 = 'tests/fixtures/nested2.yaml';
        $fileResult = 'tests/fixtures/gendiffNested';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->expectOutputString($result);
        gendiff($file3, $file4, 'stylish');
    }
}

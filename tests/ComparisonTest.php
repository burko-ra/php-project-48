<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\Comparison\gendiff;
use function Gendiff\Comparison\getFullPath;

class ComparisonTest extends TestCase
{
    public function testGendiff(): void
    {
        $file1 = 'tests/fixtures/flatJson1';
        $file2 = 'tests/fixtures/flatJson2';
        $fileResult = 'tests/fixtures/gendiffFlatJson';

        $result = file_get_contents(getFullPath($fileResult), true);

        $this->expectOutputString($result);

        gendiff($file1, $file2, 'stylish');
    }
}

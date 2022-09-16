<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\gendiff;

class DifferTest extends TestCase
{
    /**
     * @param string $file1
     * @param string $file2
     * @param string $format
     * @param string $fileResult
     * @return void
     * @dataProvider gendiffProvider
     */
    public function testGendiff($file1, $file2, $format, $fileResult): void
    {
        $this->assertStringEqualsFile($fileResult, gendiff($file1, $file2, $format));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function gendiffProvider(): array
    {
        return [
            'test JSON-stylish' => [
                'tests/fixtures/file1.json',
                'tests/fixtures/file2.json',
                'stylish',
                'tests/fixtures/gendiffStylish',
            ],
            'test YAML-stylish' => [
                'tests/fixtures/file1.yml',
                'tests/fixtures/file2.yaml',
                'stylish',
                'tests/fixtures/gendiffStylish',
            ],
            'test JSON-plain' => [
                'tests/fixtures/file1.json',
                'tests/fixtures/file2.json',
                'plain',
                'tests/fixtures/gendiffPlain',
            ],
            'test YAML-plain' => [
                'tests/fixtures/file1.yml',
                'tests/fixtures/file2.yaml',
                'plain',
                'tests/fixtures/gendiffPlain',
            ],
            'test JSON-json' => [
                'tests/fixtures/file1.json',
                'tests/fixtures/file2.json',
                'json',
                'tests/fixtures/gendiffJson',
            ],
            'test YAML-json' => [
                'tests/fixtures/file1.yml',
                'tests/fixtures/file2.yaml',
                'json',
                'tests/fixtures/gendiffJson',
            ]
        ];
    }
}

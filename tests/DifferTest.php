<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\gendiff;

class DifferTest extends TestCase
{
    /**
     * @param string $pathToFile
     * @return string
     */
    private function getFixtureFullPath(string $pathToFile): string
    {
        return __DIR__ . "/fixtures/" . $pathToFile;
    }

    /**
     * @param string $file1
     * @param string $file2
     * @param string $format
     * @param string $fileResult
     * @return void
     * @dataProvider gendiffProvider
     */
    public function testGendiff(string $file1, string $file2, string $format, string $fileResult): void
    {
        $fixture1 = $this->getFixtureFullPath($file1);
        $fixture2 = $this->getFixtureFullPath($file2);
        $fixtureResult = $this->getFixtureFullPath($fileResult);
        $this->assertStringEqualsFile($fixtureResult, gendiff($fixture1, $fixture2, $format));
    }


    /**
     * @return array<string, array<int, string>>
     */
    public function gendiffProvider(): array
    {
        return [
            'test JSON-stylish' => [
                'file1.json',
                'file2.json',
                'stylish',
                'gendiffStylish.txt',
            ],
            'test YAML-stylish' => [
                'file1.yml',
                'file2.yaml',
                'stylish',
                'gendiffStylish.txt',
            ],
            'test JSON-plain' => [
                'file1.json',
                'file2.json',
                'plain',
                'gendiffPlain.txt',
            ],
            'test YAML-plain' => [
                'file1.yml',
                'file2.yaml',
                'plain',
                'gendiffPlain.txt',
            ],
            'test JSON-json' => [
                'file1.json',
                'file2.json',
                'json',
                'gendiffJson.txt',
            ],
            'test YAML-json' => [
                'file1.yml',
                'file2.yaml',
                'json',
                'gendiffJson.txt',
            ]
        ];
    }
}

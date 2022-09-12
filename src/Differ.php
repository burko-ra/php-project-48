<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\formatDiff;
use function Differ\Diff\makeDiff;

/**
 * @param string $pathToFile
 * @return string
 */
function getRealPath(string $pathToFile): string
{
    $addedPart = $pathToFile[0] === '/' ? '' : __DIR__ . "/../";
    $fullPath = $addedPart . $pathToFile;

    $realPath = realpath($fullPath);
    if ($realPath === false) {
        throw new \Exception("Invalid path to file: '{$pathToFile}'");
    }
    return $realPath;
}

/**
 * @param string $pathToFile
 * @return string
 */
function readFile(string $pathToFile): string
{
    $content = file_get_contents($pathToFile, true);
    if ($content === false) {
        throw new \Exception("Cannot read the file: '{$pathToFile}'");
    }

    return $content;
}

/**
 * @param string $pathToFile
 * @return array<mixed>
 */
function getContentAndParse(string $pathToFile): array
{
    $realpath = getRealPath($pathToFile);
    $file = readFile($realpath);
    $extension = pathinfo($realpath, PATHINFO_EXTENSION);
    return parse($file, $extension);
}

/**
 * @param string $pathToFile1
 * @param string $pathToFile2
 * @param string $format
 * @return string
 */
function gendiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $file1 = getContentAndParse($pathToFile1);
    $file2 = getContentAndParse($pathToFile2);
    $diff = makeDiff($file1, $file2);
    return formatDiff($diff, $format);
}

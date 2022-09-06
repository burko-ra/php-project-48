<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\formatDiff;

function getRealPath(string $path): string
{
    $addedPart = $path[0] === '/' ? '' : __DIR__ . "/../";
    $fullPath = $addedPart . $path;
    return realpath($fullPath);
}

function readFile(string $path): string
{
    return file_get_contents($path, true);
}

function prepareFileToComparison(string $pathToFile): array
{
    $realpath = getRealPath($pathToFile);
    $file = readFile($realpath);
    $extension = pathinfo($realpath, PATHINFO_EXTENSION);
    return parse($file, $realpath);
}

function makeStructureIter($key, $value1, $value2 = null, string $diff = 'changed')
{
    return ['key' => $key, 'value1' => $value1, 'value2' => $value2, 'diff' => $diff];
}

function isAssociativeArray($array): bool
{
    if (!is_array($array)) {
        return false;
    }
    $filtered = array_filter($array, fn($item) => is_int($item), ARRAY_FILTER_USE_KEY);
    return $array !== $filtered;
}

function stringifyIfIndexArray($var)
{
    if (!is_array($var) || isAssociativeArray($var)) {
        return $var;
    }

    $iter = function ($value) use (&$iter) {
        if (!is_array($value)) {
            return trim(var_export($value, true), "'");
        }

        $lines = array_map(fn($item) => $iter($item), $value);
        return "[" . implode(", ", $lines) . "]";
    };

    return $iter($var);
}

function makeStructureRec($key, $value1, $value2 = null, string $diff = 'unchanged'): array
{
    $result1 = isAssociativeArray($value1) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value1), $value1) :
        stringifyIfIndexArray($value1);

    $result2 = isAssociativeArray($value2) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value2), $value2) :
        stringifyIfIndexArray($value2);

    return ['key' => $key, 'value1' => $result1, 'value2' => $result2, 'diff' => $diff];
}

function makeDiff(array $file1, array $file2): array
{
    $iter = function ($file1, $file2) use (&$iter) {
        $keys1 = array_keys($file1);
        $keys2 = array_keys($file2);
        $keys = array_unique(array_merge($keys1, $keys2));
        sort($keys);

        $callback = function ($key) use ($iter, $file1, $file2) {
            $value1 = $file1[$key] ?? null;
            $value2 = $file2[$key] ?? null;

            if (!array_key_exists($key, $file1)) {
                return makeStructureRec($key, $value2, null, 'added');
            }

            if (!array_key_exists($key, $file2)) {
                return makeStructureRec($key, $value1, null, 'removed');
            }

            if ($value1 === $value2) {
                return makeStructureRec($key, $value1, null, 'unchanged');
            }

            if (!isAssociativeArray($value1) || !isAssociativeArray($value2)) {
                return makeStructureRec($key, $value2, $value1, 'updated');
            }

            return makeStructureIter($key, $iter($value1, $value2));
        };

        return array_map($callback, $keys);
    };

    return makeStructureIter('', $iter($file1, $file2));
}

function gendiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $file1 = prepareFileToComparison($pathToFile1);
    $file2 = prepareFileToComparison($pathToFile2);
    $diff = makeDiff($file1, $file2);
    return formatDiff($diff, $format);
}

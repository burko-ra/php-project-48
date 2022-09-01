<?php

namespace Gendiff\Comparison;

use function Gendiff\Parsers\parseJson;
use function Gendiff\Parsers\parseYaml;

function readFile($path)
{
    return file_get_contents($path, true);
}

function parse($file, $extension)
{
    if ($extension === 'json') {
        return parseJson($file);
    }
    if ($extension === 'yml' || $extension === 'yaml') {
        return parseYaml($file);
    }
    throw new \Exception("Unknown extension: '{$extension}'");
}

function prepareFileToComparison($pathToFile)
{
    $file = readFile($pathToFile);
    $extension = pathinfo($pathToFile, PATHINFO_EXTENSION);
    return parse($file, $extension);
}

function gendiff($pathToFile1, $pathToFile2, $format)
{
    $file1 = prepareFileToComparison($pathToFile1);
    $file2 = prepareFileToComparison($pathToFile2);
    $diff = makeDiff($file1, $file2);
    $diffToPrint = stringifyDiff($diff);
    print $diffToPrint;
}

function isAssociativeArray($array)
{
    if (!is_array($array)) {
        return false;
    }
    $filtered = array_filter($array, fn($item) => is_int($item), ARRAY_FILTER_USE_KEY);
    return $array !== $filtered;
}

function makeStructureRec($key, $value, $sign = ' ')
{
    $result = !isAssociativeArray($value) ?
        toString($value) :
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value), $value);
    return ['sign' => $sign, 'key' => $key, 'value' => $result];
}

function makeStructureIter($key, $value, $sign = ' ')
{
    return ['sign' => $sign, 'key' => $key, 'value' => $value];
}

function makeDiff($file1, $file2)
{
    $iter = function ($file1, $file2) use (&$iter) {
        $keys1 = array_keys($file1);
        $keys2 = array_keys($file2);
        $keys = array_unique(array_merge($keys1, $keys2));
        sort($keys);

        $callback = function ($acc, $key) use ($iter, $file1, $file2) {
            $value1 = $file1[$key] ?? null;
            $value2 = $file2[$key] ?? null;

            if (!array_key_exists($key, $file1)) {
                return [...$acc, makeStructureRec($key, $value2, '+')];
            }

            if (!array_key_exists($key, $file2)) {
                return [...$acc, makeStructureRec($key, $value1, '-')];
            }

            if ($value1 === $value2) {
                return [...$acc, makeStructureRec($key, $value1, ' ')];
            }

            if (!isAssociativeArray($value1) || !isAssociativeArray($value2)) {
                return [...$acc, makeStructureRec($key, $value1, '-'), makeStructureRec($key, $value2, '+')];
            }

            return [...$acc, makeStructureIter($key, $iter($value1, $value2), ' ')];
        };

        return array_reduce($keys, $callback, []);
    };

    return makeStructureIter('', $iter($file1, $file2));
}

function toString($var)
{
    $iter = function ($value) use (&$iter) {
        if (!is_array($value)) {
            return is_null($value) ? "null" : trim(var_export($value, true), "'");
        }

        $lines = array_map(function ($item) use ($iter) {
            return $iter($item);
        }, $value);
        return "[" . implode(", ", $lines) . "]";
    };

    return $iter($var);
}

function stringifyDiff($diff)
{
    $iter = function ($currentValue, $depth) use (&$iter) {
        $children = $currentValue['value'];
        if (!is_array($children)) {
            return $children;
        }

        $indent = str_repeat('    ', $depth - 1);
        $lines = array_map(function ($item) use ($iter, $indent, $depth) {
            $value = $iter($item, $depth + 1);
            $spaceBeforeValue = empty($value) ? "" : " ";
            return "{$indent}  {$item['sign']} {$item['key']}:{$spaceBeforeValue}{$value}";
        }, $children);

        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    };

    return $iter($diff, 1) . "\n";
}

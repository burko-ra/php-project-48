<?php

namespace Gendiff\Comparison;

use function Gendiff\Parsers\parseJson;
use function Gendiff\Parsers\parseYaml;

const OPERATION_SIGNS = [
    'added' => '+',
    'removed' => '-',
    'unchanged' => ' ',
    'changed' => ' ',
    'updated' => ['-', '+']
];

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
    $diffToPrint = formatDiffStylish($diff);
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

function makeStructureRec($key, $value1, $value2 = null, $diff = 'unchanged')
{
    $result1 = isAssociativeArray($value1) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value1), $value1) :
        stringifyIfIndexArray($value1);

    $result2 = isAssociativeArray($value2) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value2), $value2) :
        stringifyIfIndexArray($value2);
    return ['key' => $key, 'value1' => $result1, 'value2' => $result2, 'diff' => $diff];
}

function makeStructureIter($key, $value1, $value2 = null, $diff = 'changed')
{
    return ['key' => $key, 'value1' => $value1, 'value2' => $value2, 'diff' => $diff];
}

function makeDiff($file1, $file2)
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
                return makeStructureRec($key, $value1, $value2, 'updated');
            }

            return makeStructureIter($key, $iter($value1, $value2), null, 'changed');
        };

        return array_map($callback, $keys);
    };

    return makeStructureIter('', $iter($file1, $file2));
}

function toStringStylish($value)
{
    return is_null($value) ? "null" : trim(var_export($value, true), "'");
}

function formatDiffStylish($diff)
{
    $iter = function ($currentValue, $typeOfValue, $depth) use (&$iter) {
        $children = $currentValue[$typeOfValue];
        if (!is_array($children)) {
            return toStringStylish($children);
        }

        $indent = str_repeat('    ', $depth - 1);

        $callback = function ($acc, $item) use ($iter, $indent, $depth) {
            $key = $item['key'];
            $difference = $item['diff'];

            $value1 = $iter($item, 'value1', $depth + 1);
            $spaceBeforeValue1 = empty($value1) ? "" : " ";

            if ($difference !== 'updated') {
                $sign = OPERATION_SIGNS[$difference];
                return [...$acc, "{$indent}  {$sign} {$key}:{$spaceBeforeValue1}{$value1}"];
            }

            $value2 = $iter($item, 'value2', $depth + 1);
            $spaceBeforeValue2 = empty($value2) ? "" : " ";
            [$sign1, $sign2] = OPERATION_SIGNS[$difference];
            return [
                ...$acc,
                "{$indent}  {$sign1} {$key}:{$spaceBeforeValue1}{$value1}",
                "{$indent}  {$sign2} {$key}:{$spaceBeforeValue2}{$value2}"
            ];
        };

        $lines = array_reduce($children, $callback, []);
        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    };

    return $iter($diff, 'value1', 1) . "\n";
}

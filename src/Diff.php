<?php

namespace Differ\Diff;

use function Functional\sort;

/**
 * @param mixed $key
 * @param mixed $value1
 * @param mixed $value2
 * @param string $operation
 * @return array<mixed>
 */

function makeStructureIter($key, $value1, $value2 = null, string $operation = 'changed'): array
{
    return ['key' => $key, 'value1' => $value1, 'value2' => $value2, 'operation' => $operation];
}

/**
 * @param mixed $value
 * @return bool
 */

function isAssociativeArray($value): bool
{
    if (!is_array($value)) {
        return false;
    }
    $filtered = array_filter($value, fn($item) => is_int($item), ARRAY_FILTER_USE_KEY);
    return $value !== $filtered;
}

/**
 * @param mixed $value
 * @return mixed
 */

function stringifyIfIndexArray($value)
{
    if (!is_array($value) || isAssociativeArray($value)) {
        return $value;
    }

    $iter = function ($array) use (&$iter) {
        if (!is_array($array)) {
            return trim(var_export($array, true), "'");
        }

        $lines = array_map(fn($item) => $iter($item), $array);
        return "[" . implode(", ", $lines) . "]";
    };

    return $iter($value);
}

/**
 * @param mixed $key
 * @param mixed $value1
 * @param mixed $value2
 * @param string $operation
 * @return array<mixed>
 */

function makeStructureRec($key, $value1, $value2 = null, string $operation = 'unchanged'): array
{
    $iter = function ($value) {
        return isAssociativeArray($value) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value), $value) :
        stringifyIfIndexArray($value);
    };

    $result1 = $iter($value1);
    $result2 = $iter($value2);

    return ['key' => $key, 'value1' => $result1, 'value2' => $result2, 'operation' => $operation];
}

/**
 * @param array<mixed> $file1
 * @param array<mixed> $file2
 * @return array<mixed>
 */

function makeDiff(array $file1, array $file2): array
{
    $iter = function ($file1, $file2) use (&$iter) {
        $keys1 = array_keys($file1);
        $keys2 = array_keys($file2);
        $keys = array_unique(array_merge($keys1, $keys2));
        $sortedKeys = sort($keys, fn ($left, $right) => strcmp($left, $right));

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

        return array_map($callback, $sortedKeys);
    };

    return makeStructureIter('', $iter($file1, $file2));
}

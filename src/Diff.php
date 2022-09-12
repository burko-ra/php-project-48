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
 * @param array<mixed> $diff
 * @return mixed
 */
function getKey($diff)
{
    return $diff['key'];
}

/**
 * @param array<mixed> $diff
 * @return mixed
 */
function getValue1($diff)
{
    return $diff['value1'];
}

/**
 * @param array<mixed> $diff
 * @return mixed
 */
function getValue2($diff)
{
    return $diff['value2'];
}

/**
 * @param array<mixed> $diff
 * @return string
 */
function getOperation($diff): string
{
    return $diff['operation'];
}

/**
 * @param array<mixed> $diff
 * @param string $typeOfValue
 * @return mixed
 */
function getValue($diff, string $typeOfValue)
{
    if ($typeOfValue === 'value1') {
        return getValue1($diff);
    }

    if ($typeOfValue === 'value2') {
        return getValue2($diff);
    }

    throw new \Exception("Selector must be only value1 or value2, '{$typeOfValue}' given\n");
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
 * @param mixed $key
 * @param mixed $value1
 * @param mixed $value2
 * @param string $operation
 * @return array<mixed>
 */
function makeStructureRec($key, $value1, $value2 = null, string $operation = 'unchanged'): array
{
    $iter = function ($value) {
        return is_array($value) ?
        array_map(fn($newKey, $newValue) => makeStructureRec($newKey, $newValue), array_keys($value), $value) :
        $value;
    };

    $result1 = $iter($value1);
    $result2 = $iter($value2);

    return makeStructureIter($key, $result1, $result2, $operation);
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
                return makeStructureRec($key, $value1, $value2, 'added');
            }

            if (!array_key_exists($key, $file2)) {
                return makeStructureRec($key, $value1, $value2, 'removed');
            }

            if ($value1 === $value2) {
                return makeStructureRec($key, $value1, $value2, 'unchanged');
            }

            if (!isAssociativeArray($value1) || !isAssociativeArray($value2)) {
                return makeStructureRec($key, $value1, $value2, 'updated');
            }

            return makeStructureIter($key, $iter($value1, $value2));
        };

        return array_map($callback, $sortedKeys);
    };

    return makeStructureIter('', $iter($file1, $file2));
}

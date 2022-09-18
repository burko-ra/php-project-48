<?php

namespace Differ\Diff;

use function Functional\sort;

/**
 * @param string $key
 * @param string $operation
 * @param mixed $value1
 * @param mixed $value2
 * @return array<mixed>
 */
function makeStructureIter(string $key, string $operation, $value1, $value2 = null): array
{
    return ['key' => $key, 'operation' => $operation, 'value1' => $value1, 'value2' => $value2];
}

/**
 * @param array<mixed> $diff
 * @return string
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

    throw new \Exception("'typeOfValue' must be only value1 or value2, '{$typeOfValue}' given\n");
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
 * @param string $key
 * @param string $operation
 * @param mixed $value1
 * @param mixed $value2
 * @return array<mixed>
 */
function makeStructureRec(string $key, string $operation, $value1, $value2 = null): array
{
    $iter = function ($value) {
        return is_array($value) ?
        array_map(
            fn($newKey, $newValue) => makeStructureRec($newKey, 'unchanged', $newValue),
            array_keys($value),
            $value
        ) :
        $value;
    };

    $result1 = $iter($value1);
    $result2 = $iter($value2);

    return makeStructureIter($key, $operation, $result1, $result2);
}

/**
 * @param array<mixed> $file1
 * @param array<mixed> $file2
 * @param string $key
 * @return array<mixed>
 */
function makeDiff(array $file1, array $file2, $key = ''): array
{
    $keys1 = array_keys($file1);
    $keys2 = array_keys($file2);
    $keys = array_unique(array_merge($keys1, $keys2));
    $sortedKeys = sort($keys, fn ($left, $right) => strcmp($left, $right));

    $callback = function ($key) use ($file1, $file2) {
        $value1 = $file1[$key] ?? null;
        $value2 = $file2[$key] ?? null;

        if (!array_key_exists($key, $file1)) {
            return makeStructureRec($key, 'added', $value2);
        }

        if (!array_key_exists($key, $file2)) {
            return makeStructureRec($key, 'removed', $value1);
        }

        if ($value1 === $value2) {
            return makeStructureRec($key, 'unchanged', $value1);
        }

        if (!isAssociativeArray($value1) || !isAssociativeArray($value2)) {
            return makeStructureRec($key, 'updated', $value1, $value2);
        }

        return makeDiff($value1, $value2, $key);
    };

    $result = array_map($callback, $sortedKeys);

    return makeStructureIter($key, 'hasChangesInChildren', $result);
}

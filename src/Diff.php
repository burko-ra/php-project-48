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
function makeStructure(string $key, string $operation, $value1, $value2 = null): array
{
    return ['key' => $key, 'operation' => $operation, 'value1' => $value1, 'value2' => $value2];
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

    return makeStructure($key, $operation, $result1, $result2);
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
 * @param array<mixed> $root
 * @return mixed
 */
function getChildren($root)
{
    return $root['children'];
}

/**
 * @param array<mixed> $content1
 * @param array<mixed> $content2
 * @return array<mixed>
 */
function makeNodes(array $content1, array $content2): array
{
    $keys1 = array_keys($content1);
    $keys2 = array_keys($content2);
    $keys = array_unique(array_merge($keys1, $keys2));
    $sortedKeys = sort($keys, fn ($left, $right) => strcmp($left, $right));

    $callback = function ($key) use ($content1, $content2) {
        $value1 = $content1[$key] ?? null;
        $value2 = $content2[$key] ?? null;

        if (!array_key_exists($key, $content1)) {
            return makeStructureRec($key, 'added', $value2);
        }

        if (!array_key_exists($key, $content2)) {
            return makeStructureRec($key, 'removed', $value1);
        }

        if ($value1 === $value2) {
            return makeStructureRec($key, 'unchanged', $value1);
        }

        if (!is_array($value1) || !is_array($value2)) {
            return makeStructureRec($key, 'updated', $value1, $value2);
        }

        $result = makeNodes($value1, $value2);

        return makeStructure($key, 'hasChangesInChildren', $result);
    };

    return array_map($callback, $sortedKeys);
}

/**
 * @param array<mixed> $content1
 * @param array<mixed> $content2
 * @return array<mixed>
 */
function makeDiff($content1, $content2)
{
    $children = makeNodes($content1, $content2);
    return [
        'key' => 'root',
        'operation' => 'hasChangesInChildren',
        'value1' => $children,
        'value2' => null,
        'children' => $children
    ];
}

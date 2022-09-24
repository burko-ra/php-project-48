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
function makeStructureLeaf(string $key, string $operation, $value1, $value2 = null): array
{
    return ['key' => $key, 'operation' => $operation, 'value1' => $value1, 'value2' => $value2];
}

/**
 * @param string $key
 * @param string $operation
 * @param array<mixed> $children
 * @return array<mixed>
 */
function makeStructureNode(string $key, string $operation, array $children): array
{
    return ['key' => $key, 'operation' => $operation, 'children' => $children];
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
function makeTree(array $content1, array $content2): array
{
    $keys1 = array_keys($content1);
    $keys2 = array_keys($content2);
    $keys = array_unique(array_merge($keys1, $keys2));
    $sortedKeys = sort($keys, fn ($left, $right) => strcmp($left, $right));

    $callback = function ($key) use ($content1, $content2) {
        $value1 = $content1[$key] ?? null;
        $value2 = $content2[$key] ?? null;

        if (!array_key_exists($key, $content1)) {
            return makeStructureLeaf($key, 'added', $value2);
        }

        if (!array_key_exists($key, $content2)) {
            return makeStructureLeaf($key, 'removed', $value1);
        }

        if ($value1 === $value2) {
            return makeStructureLeaf($key, 'unchanged', $value1);
        }

        if (!is_array($value1) || !is_array($value2)) {
            return makeStructureLeaf($key, 'updated', $value1, $value2);
        }

        $result = makeTree($value1, $value2);

        return makeStructureNode($key, 'hasChangesInChildren', $result);
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
    $children = makeTree($content1, $content2);
    return [
        'operation' => 'root',
        'children' => $children
    ];
}

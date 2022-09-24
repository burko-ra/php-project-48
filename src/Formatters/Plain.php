<?php

namespace Differ\Formatters\Plain;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue1;
use function Differ\Diff\getValue2;
use function Differ\Diff\getOperation;
use function Differ\Diff\getChildren;

/**
 * @param mixed $value
 * @return string
 */
function toString($value): string
{
    if (is_array($value)) {
        return "[complex value]";
    }

    if (is_null($value)) {
        return "null";
    }

    return var_export($value, true);
}

/**
 * @param array<mixed> $currentValue
 * @param string $currentPath
 * @param array<string> $acc
 * @return array<string>
 */
function makeStructure(array $currentValue, string $currentPath, $acc): array
{
    $operation = getOperation($currentValue);
    $key = getKey($currentValue);
    $property = $currentPath . $key;

    if ($operation === 'hasChangesInChildren') {
        $children = getChildren($currentValue);
        $newPath = "{$property}.";
        return array_reduce($children, fn($newAcc, $item) => makeStructure($item, $newPath, $newAcc), $acc);
    }

    $value1 = toString(getValue1($currentValue));
    $value2 = toString(getValue2($currentValue));

    if ($operation === 'added') {
        return array_merge($acc, ["Property '{$property}' was added with value: {$value1}"]);
    }

    if ($operation === 'removed') {
        return array_merge($acc, ["Property '{$property}' was removed"]);
    }

    if ($operation === 'updated') {
        return array_merge($acc, ["Property '{$property}' was updated. From {$value1} to {$value2}"]);
    }

    return $acc;
}

/**
 * @param array<mixed> $diff
 * @return string
 */
function formatDiff(array $diff): string
{
    $children = getChildren($diff);
    $lines = array_reduce($children, fn($acc, $item) => makeStructure($item, '', $acc), []);
    return implode("\n", $lines);
}

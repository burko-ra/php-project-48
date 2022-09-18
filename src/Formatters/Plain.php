<?php

namespace Differ\Formatters\Plain;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue1;
use function Differ\Diff\getValue2;
use function Differ\Diff\getOperation;

/**
 * @param mixed $value
 * @return string
 */
function toStringPlain($value): string
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
 * @param array<mixed> $diff
 * @return string
 */

function formatDiffPlain(array $diff): string
{
    return implode("\n", makePlain(($diff), '', []));
}

/**
 * @param array<mixed> $currentValue
 * @param string $currentPath
 * @param array<string> $acc
 * @return array<string>
 */
function makePlain(array $currentValue, string $currentPath, $acc): array
{
    $key = getKey($currentValue);
    $property = $currentPath . $key;
    $operation = getOperation($currentValue);

    $value1 = toStringPlain(getValue1($currentValue));
    $value2 = toStringPlain(getValue2($currentValue));

    if ($operation === 'added') {
        return array_merge($acc, ["Property '{$property}' was added with value: {$value1}"]);
    }

    if ($operation === 'removed') {
        return array_merge($acc, ["Property '{$property}' was removed"]);
    }

    if ($operation === 'updated') {
        return array_merge($acc, ["Property '{$property}' was updated. From {$value1} to {$value2}"]);
    }

    if ($operation === 'hasChangesInChildren') {
        $children = getValue1($currentValue);
        $newPath = $key === '' ? "{$property}" : "{$property}.";
        return array_reduce($children, fn($newAcc, $item) => makePlain($item, $newPath, $newAcc), $acc);
    }

    return $acc;
}

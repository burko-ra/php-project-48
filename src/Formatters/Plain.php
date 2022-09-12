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
 * @param array<mixed> $operation
 * @return string
 */
function formatDiffPlain(array $operation): string
{
    $iter = function ($currentValue, $currentPath, $depth, $acc) use (&$iter) {
        $property = $currentPath . getKey($currentValue);
        $operation = getOperation($currentValue);

        $value1 = toStringPlain(getValue1($currentValue));
        $value2 = toStringPlain(getValue2($currentValue));

        if ($operation === 'added') {
            return array_merge($acc, ["Property '{$property}' was added with value: {$value2}"]);
        }

        if ($operation === 'removed') {
            return array_merge($acc, ["Property '{$property}' was removed"]);
        }

        if ($operation === 'updated') {
            return array_merge($acc, ["Property '{$property}' was updated. From {$value1} to {$value2}"]);
        }

        if ($operation === 'changed') {
            $children = getValue1($currentValue);
            $newPath = ($depth === 1) ? $property : "{$property}.";
            return array_reduce($children, fn($newAcc, $item) => $iter($item, $newPath, $depth + 1, $newAcc), $acc);
        }

        return $acc;
    };

    return implode("\n", $iter($operation, '', 1, []));
}

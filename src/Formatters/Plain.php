<?php

namespace Differ\Formatters\Plain;

/**
 * @param mixed $value
 * @return string
 */

function toStringPlain($value): string
{
    return is_null($value) ? "null" : var_export($value, true);
}

/**
 * @param array<mixed> $operation
 * @return string
 */

function formatDiffPlain(array $operation): string
{
    $iter = function ($currentValue, $currentPath, $depth, $acc) use (&$iter) {
        $property = $currentPath . $currentValue['key'];
        $operation = $currentValue['operation'];

        $value1 = is_array($currentValue['value1']) ? "[complex value]" : toStringPlain($currentValue['value1']);
        if ($operation === 'added') {
            return array_merge($acc, ["Property '{$property}' was added with value: {$value1}"]);
        }

        if ($operation === 'removed') {
            return array_merge($acc, ["Property '{$property}' was removed"]);
        }

        if ($operation === 'updated') {
            $value2 = is_array($currentValue['value2']) ? "[complex value]" : toStringPlain($currentValue['value2']);
            return array_merge($acc, ["Property '{$property}' was updated. From {$value2} to {$value1}"]);
        }

        if ($operation === 'changed') {
            $children = $currentValue['value1'];
            $newPath = ($depth === 1) ? $property : "{$property}.";
            return array_reduce($children, fn($newAcc, $item) => $iter($item, $newPath, $depth + 1, $newAcc), $acc);
        }

        return $acc;
    };

    return implode("\n", $iter($operation, '', 1, []));
}

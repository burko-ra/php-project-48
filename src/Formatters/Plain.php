<?php

namespace Gendiff\Formatters\Plain;

function toStringPlain($value): string
{
    return is_null($value) ? "null" : var_export($value, true);
}

function formatDiffPlain(array $diff): string
{
    $iter = function ($currentValue, $currentPath, $depth, $acc) use (&$iter) {
        $property = $currentPath . $currentValue['key'];
        $difference = $currentValue['diff'];

        $value1 = is_array($currentValue['value1']) ? "[complex value]" : toStringPlain($currentValue['value1']);
        if ($difference === 'added') {
            return array_merge($acc, ["Property '{$property}' was added with value: {$value1}"]);
        }

        if ($difference === 'removed') {
            return array_merge($acc, ["Property '{$property}' was removed"]);
        }

        if ($difference === 'updated') {
            $value2 = is_array($currentValue['value2']) ? "[complex value]" : toStringPlain($currentValue['value2']);
            return array_merge($acc, ["Property '{$property}' was updated. From {$value2} to {$value1}"]);
        }

        if ($difference === 'changed') {
            $children = $currentValue['value1'];
            $newPath = ($depth === 1) ? $property : "{$property}.";
            return array_reduce($children, fn($newAcc, $item) => $iter($item, $newPath, $depth + 1, $newAcc), $acc);
        }

        return $acc;
    };

    return implode("\n", $iter($diff, '', 1, [])) . "\n";
}

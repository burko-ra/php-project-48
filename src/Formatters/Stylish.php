<?php

namespace Differ\Formatters\Stylish;

const OPERATION_SIGNS = [
    'added' => '+',
    'removed' => '-',
    'unchanged' => ' ',
    'changed' => ' ',
];

/**
 * @param mixed $value
 * @return string
 */

function toStringStylish($value): string
{
    return is_null($value) ? "null" : trim(var_export($value, true), "'");
}

/**
 * @param array<mixed> $operation
 * @return string
 */

function formatDiffStylish(array $operation): string
{
    $iter = function ($currentValue, $typeOfValue, $depth) use (&$iter) {
        $children = $currentValue[$typeOfValue];
        if (!is_array($children)) {
            return toStringStylish($children);
        }

        $indent = str_repeat('    ', $depth - 1);

        $callback = function ($acc, $item) use ($iter, $indent, $depth) {
            $key = $item['key'];
            $operation = $item['operation'];

            $value1 = $iter($item, 'value1', $depth + 1);

            if ($operation !== 'updated') {
                $sign = OPERATION_SIGNS[$operation];
                return [...$acc, "{$indent}  {$sign} {$key}: {$value1}"];
            }

            $value2 = $iter($item, 'value2', $depth + 1);
            $sign1 = OPERATION_SIGNS['removed'];
            $sign2 = OPERATION_SIGNS['added'];
            return [
                ...$acc,
                "{$indent}  {$sign1} {$key}: {$value2}",
                "{$indent}  {$sign2} {$key}: {$value1}"
            ];
        };

        $lines = array_reduce($children, $callback, []);
        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    };

    return $iter($operation, 'value1', 1);
}

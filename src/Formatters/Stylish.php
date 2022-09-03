<?php

namespace Gendiff\Formatters\Stylish;

const OPERATION_SIGNS = [
    'added' => '+',
    'removed' => '-',
    'unchanged' => ' ',
    'changed' => ' ',
    'updated' => ['-', '+']
];

function toStringStylish($value)
{
    return is_null($value) ? "null" : trim(var_export($value, true), "'");
}

function formatDiffStylish($diff)
{
    $iter = function ($currentValue, $typeOfValue, $depth) use (&$iter) {
        $children = $currentValue[$typeOfValue];
        if (!is_array($children)) {
            return toStringStylish($children);
        }

        $indent = str_repeat('    ', $depth - 1);

        $callback = function ($acc, $item) use ($iter, $indent, $depth) {
            $key = $item['key'];
            $difference = $item['diff'];

            $value1 = $iter($item, 'value1', $depth + 1);
            $spaceBeforeValue1 = empty($value1) ? "" : " ";

            if ($difference !== 'updated') {
                $sign = OPERATION_SIGNS[$difference];
                return [...$acc, "{$indent}  {$sign} {$key}:{$spaceBeforeValue1}{$value1}"];
            }

            $value2 = $iter($item, 'value2', $depth + 1);
            $spaceBeforeValue2 = empty($value2) ? "" : " ";

            [$sign1, $sign2] = OPERATION_SIGNS[$difference];
            return [
                ...$acc,
                "{$indent}  {$sign1} {$key}:{$spaceBeforeValue2}{$value2}",
                "{$indent}  {$sign2} {$key}:{$spaceBeforeValue1}{$value1}"
            ];
        };

        $lines = array_reduce($children, $callback, []);
        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    };

    return $iter($diff, 'value1', 1) . "\n";
}

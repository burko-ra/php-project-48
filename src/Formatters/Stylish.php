<?php

namespace Differ\Formatters\Stylish;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue;
use function Differ\Diff\getOperation;

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
        $children = getValue($currentValue, $typeOfValue);
        if (!is_array($children)) {
            return toStringStylish($children);
        }

        $indent = str_repeat('    ', $depth - 1);

        $callback = function ($acc, $item) use ($iter, $indent, $depth) {
            $key = getKey($item);
            $operation = getOperation($item);

            $value1 = $iter($item, 'value1', $depth + 1);
            $value2 = $iter($item, 'value2', $depth + 1);

            if ($operation === 'added') {
                return [...$acc, "{$indent}  + {$key}: {$value2}"];
            }

            if ($operation === 'removed') {
                return [...$acc, "{$indent}  - {$key}: {$value1}"];
            }

            if ($operation === 'updated') {
                return [
                    ...$acc,
                    "{$indent}  - {$key}: {$value1}",
                    "{$indent}  + {$key}: {$value2}"
                ];
            }

            return [...$acc, "{$indent}    {$key}: {$value1}"];
        };

        $lines = array_reduce($children, $callback, []);
        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    };

    return $iter($operation, 'value1', 1);
}

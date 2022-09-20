<?php

namespace Differ\Formatters\Stylish;

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
    return is_null($value) ? "null" : trim(var_export($value, true), "'");
}

/**
 * @param mixed $currentValue
 * @param int $depth
 * @return string
 */
function makeStructure($currentValue, int $depth): string
{
    if (!is_array($currentValue)) {
        return toString($currentValue);
    }

    $indent = str_repeat('    ', $depth - 1);

    $callback = function ($acc, $item) use ($indent, $depth) {
        $key = getKey($item);
        $operation = getOperation($item);

        $value1 = makeStructure(getValue1($item), $depth + 1);
        $value2 = makeStructure(getValue2($item), $depth + 1);

        if ($operation === 'added') {
            return [...$acc, "{$indent}  + {$key}: {$value1}"];
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

    $lines = array_reduce($currentValue, $callback, []);
    return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
}

/**
 * @param array<mixed> $diff
 * @return string
 */
function formatDiff(array $diff): string
{
    return makeStructure(getChildren($diff), 1);
}

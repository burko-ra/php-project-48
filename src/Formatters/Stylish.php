<?php

namespace Differ\Formatters\Stylish;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue1;
use function Differ\Diff\getValue2;
use function Differ\Diff\getType;
use function Differ\Diff\getChildren;

/**
 * @param int $depth
 * @return string
 */
function getIndent(int $depth): string
{
    return str_repeat('    ', $depth - 1);
}

/**
 * @param array<string> $lines
 * @param string $indent
 * @return string
 */
function wrapLines(array $lines, string $indent): string
{
    return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
}

/**
 * @param mixed $item
 * @param int $depth
 * @return string
 */
function toString($item, int $depth): string
{
    if (!is_array($item)) {
        return is_null($item) ? "null" : trim(var_export($item, true), "'");
    }

    $indent = getIndent($depth);

    $lines = array_map(function ($key, $value) use ($indent, $depth) {
        $stringValue = toString($value, $depth + 1);
        return "{$indent}    {$key}: {$stringValue}";
    }, array_keys($item), $item);

    return wrapLines($lines, $indent);
}

/**
 * @param mixed $currentValue
 * @param int $depth
 * @return string
 */
function makeFormat($currentValue, int $depth): string
{
    $indent = getIndent($depth);

    $callback = function ($acc, $item) use ($indent, $depth) {
        $key = getKey($item);
        $type = getType($item);

        if ($type === 'nested') {
            $children = makeFormat(getChildren($item), $depth + 1);
            return [...$acc, "{$indent}    {$key}: {$children}"];
        }

        $value1 = toString(getValue1($item), $depth + 1);
        $value2 = toString(getValue2($item), $depth + 1);

        if ($type === 'added') {
            return [...$acc, "{$indent}  + {$key}: {$value1}"];
        }

        if ($type === 'removed') {
            return [...$acc, "{$indent}  - {$key}: {$value1}"];
        }

        if ($type === 'updated') {
            return [
                ...$acc,
                "{$indent}  - {$key}: {$value1}",
                "{$indent}  + {$key}: {$value2}"
            ];
        }

        return [...$acc, "{$indent}    {$key}: {$value1}"];
    };
    $lines = array_reduce($currentValue, $callback, []);

    return wrapLines($lines, $indent);
}

/**
 * @param array<mixed> $diff
 * @return string
 */
function formatDiff(array $diff): string
{
    return makeFormat(getChildren($diff), 1);
}

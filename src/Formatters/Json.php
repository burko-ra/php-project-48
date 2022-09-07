<?php

namespace Differ\Formatters\Json;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue1;
use function Differ\Diff\getValue2;
use function Differ\Diff\getOperation;

/**
 * @param mixed $value
 * @return mixed
 */

function makeAssociativeArray($value)
{
    if (!is_array($value)) {
        return $value;
    }
    return array_reduce($value, function ($acc, $item) {
        $newKey = getKey($item);
        $newValue = makeAssociativeArray(getValue1($item));
        return array_merge($acc, [$newKey => $newValue]);
    }, []);
}

/**
 * @param string $property
 * @param array<mixed> $element
 * @return array<mixed>
 */

function makeStructure(string $property, array $element): array
{
    $operation = getOperation($element);
    $value1 = getValue1($element);
    $value2 = getValue2($element);

    return [$property => [
        'operation' => $operation,
        'value1' => makeAssociativeArray($value1),
        'value2' => makeAssociativeArray($value2)
        ]
    ];
}

/**
 * @param mixed $value
 * @return string
 */

function toStringJson($value): string
{
    return is_null($value) ? "null" : var_export($value, true);
}

/**
 * @param array<mixed> $operation
 * @return string
 */

function formatDiffJson(array $operation): string
{
    $iter = function ($currentValue, $currentPath, $depth, $acc) use (&$iter) {
        $property = $currentPath . getKey($currentValue);
        $operation = getOperation($currentValue);

        if ($operation === 'added' || $operation === 'removed' || $operation === 'updated') {
            return array_merge($acc, makeStructure($property, $currentValue));
        }

        if ($operation === 'changed') {
            $children = getValue1($currentValue);
            $newPath = ($depth === 1) ? $property : "{$property}.";
            return array_reduce($children, fn($newAcc, $item) => $iter($item, $newPath, $depth + 1, $newAcc), $acc);
        }

        return $acc;
    };

    $encoded = json_encode($iter($operation, '', 1, []), JSON_PRETTY_PRINT);
    if ($encoded === false) {
        throw new \Exception("Invalid diff. Cannot encode to json");
    }
    return $encoded;
}

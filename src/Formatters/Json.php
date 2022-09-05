<?php

namespace Gendiff\Formatters\Json;

function makeKeyValue(mixed $value): mixed
{
    if (!is_array($value)) {
        return $value;
    }
    return array_reduce($value, function ($acc, $item) {
        $newKey = $item['key'];
        $newValue = makeKeyValue($item['value1']);
        return array_merge($acc, [$newKey => $newValue]);
    }, []);
}

function makeStructure(mixed $property, array $element): array
{
    $operation = $element['diff'];
    $value = $element['value1'];
    $structure = [$property => [
        'operation' => $operation,
        'value' => makeKeyValue($value)
        ]
    ];
    return $structure;
}

function toStringJson(mixed $value): string
{
    return is_null($value) ? "null" : var_export($value, true);
}

function formatDiffJson(array $diff): string
{
    $iter = function ($currentValue, $currentPath, $depth, $acc) use (&$iter) {
        $property = $currentPath . $currentValue['key'];
        $difference = $currentValue['diff'];

        $value1 = is_array($currentValue['value1']) ? "[complex value]" : toStringJson($currentValue['value1']);
        if ($difference === 'added') {
            return array_merge($acc, makeStructure($property, $currentValue));
        }

        if ($difference === 'removed') {
            return array_merge($acc, makeStructure($property, $currentValue));
        }

        if ($difference === 'updated') {
            return array_merge($acc, makeStructure($property, $currentValue));
        }

        if ($difference === 'changed') {
            $children = $currentValue['value1'];
            $newPath = ($depth === 1) ? $property : "{$property}.";
            return array_reduce($children, fn($newAcc, $item) => $iter($item, $newPath, $depth + 1, $newAcc), $acc);
        }

        return $acc;
    };

    return json_encode($iter($diff, '', 1, []), JSON_PRETTY_PRINT) . "\n";
}

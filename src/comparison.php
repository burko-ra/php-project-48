<?php

namespace Gendiff\Comparison;

function readFile($path)
{
    return file_get_contents($path, true);
}

function prepareFileToComparison($file)
{
    $decoded = json_decode($file, true);
    return array_reduce(array_keys($decoded), function ($acc, $key) use ($decoded) {
        $value = $decoded[$key];
        $stringValue = makeStringFromValue($value);
        return array_merge($acc, [$key => $stringValue]);
    }, []);
}

function makeStringFromValue($value)
{
    if (is_bool($value)) {
        return $value === true ? "true" : "false";
    }
    return (string) $value;
}

function compare($file1, $file2)
{
    $keys = array_unique([...array_keys($file1), ...array_keys($file2)]);
    sort($keys);
    return array_reduce($keys, function ($acc, $key) use ($file1, $file2) {
        if (array_key_exists($key, $file1) && !array_key_exists($key, $file2)) {
            $diff = "  - " . $key . ": " . $file1[$key];
            return array_merge($acc, [$diff]);
        }
        if (array_key_exists($key, $file2) && !array_key_exists($key, $file1)) {
            $diff = "  + " . $key . ": " . $file2[$key];
            return array_merge($acc, [$diff]);
        }
        if ($file1[$key] !== $file2[$key]) {
            $diff1 = "  - " . $key . ": " . $file1[$key];
            $diff2 = "  + " . $key . ": " . $file2[$key];
            return array_merge($acc, [$diff1, $diff2]);
        }
        $diff = "    " . $key . ": " . $file1[$key];
        return array_merge($acc, [$diff]);
    }, []);
}

function gendiff($pathToFile1, $pathToFile2, $format)
{
    $sourceFile1 = readFile($pathToFile1);
    $sourceFile2 = readFile($pathToFile2);
    $file1 = prepareFileToComparison($sourceFile1);
    $file2 = prepareFileToComparison($sourceFile2);
    $compared = compare($file1, $file2);
    print_r("{\n" . implode("\n", $compared) . "\n}\n");
}

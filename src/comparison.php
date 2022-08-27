<?php

namespace Gendiff\Comparison;

use Docopt;

use function Gendiff\Parsers\parseJson;
use function Gendiff\Parsers\parseYaml;

const DOC = <<<D
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: stylish]

D;

function getFullPath($path)
{
    $addedPart = $path[0] === '/' ? '' : __DIR__ . "/../";
    return $addedPart . $path;
}

function run()
{
    $args = Docopt::handle(DOC);
    $pathToFile1 = getFullPath($args['<firstFile>']);
    $pathToFile2 = getFullPath($args['<secondFile>']);
    $format = $args['--format'];
    gendiff($pathToFile1, $pathToFile2, $format);
}

function readFile($path)
{
    return file_get_contents($path, true);
}

function parse($file, $extension)
{
    if ($extension === 'json') {
        return parseJson($file);
    }
    if ($extension === 'yml' || $extension === 'yaml') {
        return parseYaml($file);
    }
    throw new \Exception("Unknown extension: '{$extension}'");
}

function prepareFileToComparison($pathToFile)
{
    $file = readFile($pathToFile);
    $extension = pathinfo($pathToFile, PATHINFO_EXTENSION);
    $parsed = parse($file, $extension);
    return array_reduce(array_keys($parsed), function ($acc, $key) use ($parsed) {
        $value = $parsed[$key];
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
    $file1 = prepareFileToComparison($pathToFile1);
    $file2 = prepareFileToComparison($pathToFile2);
    $compared = compare($file1, $file2);
    print_r("{\n" . implode("\n", $compared) . "\n}\n");
}

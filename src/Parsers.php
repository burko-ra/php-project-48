<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @param string $file
 * @param string $pathToFile
 * @return array<mixed>
 */
function parse(string $file, string $pathToFile): array
{
    $extension = pathinfo($pathToFile, PATHINFO_EXTENSION);

    if ($extension === 'json') {
        return parseJson($file, $pathToFile);
    }

    if ($extension === 'yml' || $extension === 'yaml') {
        return parseYaml($pathToFile);
    }

    throw new \Exception("Unknown extension: '{$extension}'");
}

/**
 * @param string $file
 * @param string $pathToFile
 * @return array<mixed>
 */
function parseJson(string $file, string $pathToFile): array
{
    $decoded = json_decode($file, true);
    if ($decoded === null) {
        throw new \Exception("This JSON cannot be decoded: '{$pathToFile}'\n");
    }
    return $decoded;
}

/**
 * @param string $pathToFile
 * @return array<mixed>
 */
function parseYaml(string $pathToFile): array
{
    return Yaml::parseFile($pathToFile);
}

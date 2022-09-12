<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @param string $file
 * @param string $extension
 * @return array<mixed>
 */
function parse(string $file, string $extension): array
{
    if ($extension === 'json') {
        return parseJson($file);
    }

    if ($extension === 'yml' || $extension === 'yaml') {
        return Yaml::parse($file);
    }

    throw new \Exception("Unknown extension: '{$extension}'");
}

/**
 * @param string $file
 * @return array<mixed>
 */
function parseJson(string $file): array
{
    $decoded = json_decode($file, true);
    if ($decoded === null) {
        throw new \Exception("Cannot parse the file\n");
    }
    return $decoded;
}

<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

/**
 * @param string $content
 * @param string $extension
 * @return array<mixed>
 */
function parse(string $content, string $extension): array
{
    if ($extension === 'json') {
        return parseJson($content);
    }

    if ($extension === 'yml' || $extension === 'yaml') {
        return Yaml::parse($content);
    }

    throw new \Exception("Unknown extension: '{$extension}'");
}

/**
 * @param string $content
 * @return array<mixed>
 */
function parseJson(string $content): array
{
    $decoded = json_decode($content, true);
    if ($decoded === null) {
        throw new \Exception("Cannot parse the file\n");
    }
    return $decoded;
}

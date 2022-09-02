<?php

namespace Gendiff\Parsers;

use Symfony\Component\Yaml\Yaml;

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

function parseJson($file)
{
    return json_decode($file, true);
}

function parseYaml($file)
{
    return Yaml::parse($file);
}

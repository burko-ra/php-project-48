<?php

namespace Gendiff\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson($file)
{
    return json_decode($file, true);
}

function parseYaml($file)
{
    return Yaml::parse($file);
}

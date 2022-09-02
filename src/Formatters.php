<?php

namespace Gendiff\Formatters;

use function Gendiff\Formatters\Stylish\formatDiffStylish;
use function Gendiff\Formatters\Plain\formatDiffPlain;

function formatDiff($diff, $format)
{
    switch ($format) {
        case 'stylish':
            return formatDiffStylish($diff);
        case 'plain':
            return formatDiffPlain($diff);
        default:
            throw new \Exception("Unknown format: '{$format}'");
    }
}

<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\formatDiffStylish;
use function Differ\Formatters\Plain\formatDiffPlain;
use function Differ\Formatters\Json\formatDiffJson;

function formatDiff(array $diff, string $format): string
{
    switch ($format) {
        case 'stylish':
            return formatDiffStylish($diff);
        case 'plain':
            return formatDiffPlain($diff);
        case 'json':
            return formatDiffJson($diff);
        default:
            throw new \Exception("Unknown format: '{$format}'");
    }
}

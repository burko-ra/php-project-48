<?php

namespace Differ\Formatters;

use function Differ\Formatters\Stylish\formatDiff as formatDiffStylish;
use function Differ\Formatters\Plain\formatDiff as formatDiffPlain;
use function Differ\Formatters\Json\formatDiff as formatDiffJson;

/**
 * @param array<mixed> $diff
 * @param string $format
 * @return string
 */
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

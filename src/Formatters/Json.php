<?php

namespace Differ\Formatters\Json;

/**
 * @param array<mixed> $diff
 * @return string
 */
function formatDiff(array $diff): string
{
    $encoded = json_encode($diff, JSON_PRETTY_PRINT);
    if ($encoded === false) {
        throw new \Exception("Invalid diff. Cannot encode to json");
    }
    return $encoded;
}

<?php

namespace Differ\Formatters\Json;

use function Differ\Diff\getKey;
use function Differ\Diff\getValue1;
use function Differ\Diff\getValue2;
use function Differ\Diff\getOperation;

/**
 * @param array<mixed> $diff
 * @return string
 */
function formatDiffJson(array $diff): string
{
    $encoded = json_encode($diff, JSON_PRETTY_PRINT);
    if ($encoded === false) {
        throw new \Exception("Invalid diff. Cannot encode to json");
    }
    return $encoded;
}

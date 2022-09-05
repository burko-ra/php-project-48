<?php

namespace Differ\Cli;

use Docopt;

use function Differ\Differ\gendiff;

const DOC = <<<EOF
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: stylish]

EOF;

function getRealPath(string $path): string
{
    $addedPart = $path[0] === '/' ? '' : __DIR__ . "/../";
    $fullPath = $addedPart . $path;
    return realpath($fullPath);
}

function run(): void
{
    $args = Docopt::handle(DOC);
    $pathToFile1 = getRealPath($args['<firstFile>']);
    $pathToFile2 = getRealPath($args['<secondFile>']);
    $format = $args['--format'];

    print_r(gendiff($pathToFile1, $pathToFile2, $format));
}

<?php

namespace Gendiff\Cli;

use Docopt;

use function Gendiff\Comparison\gendiff;

const DOC = <<<EOF
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>
S
Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: stylish]

EOF;

function getFullPath($path)
{
    $addedPart = $path[0] === '/' ? '' : __DIR__ . "/../";
    return $addedPart . $path;
}

function run()
{
    $args = Docopt::handle(DOC);
    $pathToFile1 = getFullPath($args['<firstFile>']);
    $pathToFile2 = getFullPath($args['<secondFile>']);
    $format = $args['--format'];
    print_r(gendiff($pathToFile1, $pathToFile2, $format));
}

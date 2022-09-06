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

function run(): string
{
    $args = Docopt::handle(DOC);
    $pathToFile1 = $args['<firstFile>'];
    $pathToFile2 = $args['<secondFile>'];
    $format = $args['--format'];

    return gendiff($pathToFile1, $pathToFile2, $format);
}

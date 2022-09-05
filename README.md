### Hexlet tests and linter status:
[![Actions Status](https://github.com/burko-ra/php-project-48/workflows/hexlet-check/badge.svg)](https://github.com/burko-ra/php-project-48/actions)
[![PHP CI](https://github.com/burko-ra/php-project-48/actions/workflows/workflow.yml/badge.svg)](https://github.com/burko-ra/php-project-48/actions/workflows/workflow.yml)
<a href="https://codeclimate.com/github/burko-ra/php-project-48/maintainability"><img src="https://api.codeclimate.com/v1/badges/bc6e9a51f6c1b10f0d3c/maintainability" /></a>
<a href="https://codeclimate.com/github/burko-ra/php-project-48/test_coverage"><img src="https://api.codeclimate.com/v1/badges/bc6e9a51f6c1b10f0d3c/test_coverage" /></a>

## Difference Calculator
Difference Calculator is a command line tool for finding differences in configuration files (JSON, YAML). It generates reports in the form of plain text, tree and json.

### Usage
  gendiff (-h|--help)
  
  gendiff (-v|--version)
  
  gendiff [--format <fmt>] <firstFile> <secondFile>
  
### Report formats:
<ul>
<li>plain
<li>stylish
<li>json
</ul>

### Requirements

PHP: >= 7.4

Composer: ^2.3

GNU make: ^4.2

### Setup

```sh
$ git clone git@github.com:burko-ra/php-project-48.git

$ cd php-project-48

$ make install
```

### Example:
<a href="https://asciinema.org/a/519067" target="_blank"><img src="https://asciinema.org/a/519067.svg" /></a>

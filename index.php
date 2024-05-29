<?php

use Twilight\Compiler;
use Twilight\Directives;
use Twilight\Tokenizer;
use Twilight\NodeTree;
use Twilight\Directives\IfDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\AttributesDirective;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

$timer = microtime(true);
$input = file_get_contents(__DIR__ . '/demo-input.twig');

$directives = new Directives;
$directives->register('if', IfDirective::class);
$directives->register('attributes', AttributesDirective::class);
$directives->register('for', ForDirective::class);
$directives->register('html', HtmlDirective::class);
$directives->register('text', TextDirective::class);


$tokenizer = new Tokenizer($input);

$tree = new NodeTree($tokenizer->tokenize(), $directives);
$elements = $tree->create();

// print_r($elements);

$compiler = new Compiler();
echo $compiler->compile($elements);

echo PHP_EOL . 'Execution time: ' . (number_format(microtime(true) - $timer, 4)) . ' seconds' . PHP_EOL;
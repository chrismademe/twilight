<?php

use Twilight\Compiler;
use Twilight\Directives;
use Twilight\Tokenizer;
use Twilight\NodeTree;
use Twilight\Directives\IfDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\TextDirective;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

$input = file_get_contents(__DIR__ . '/demo-input.twig');

$directives = new Directives;
$directives->register('if', IfDirective::class);
$directives->register('for', ForDirective::class);
$directives->register('html', HtmlDirective::class);
$directives->register('text', TextDirective::class);

$tokenizer = new Tokenizer($input);
$tree = new NodeTree($tokenizer->tokenize(), $directives);
$elements = $tree->create();

$compiler = new Compiler();
echo $compiler->compile($elements);
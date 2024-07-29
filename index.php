<?php

use Twilight\Directives;
use Twilight\Compiler\Compiler;
use Twilight\Directives\IfDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\UnlessDirective;
use Twilight\Directives\AttributesDirective;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

$directives = new Directives;
$directives->register('if', IfDirective::class);
$directives->register('unless', UnlessDirective::class);
$directives->register('attributes', AttributesDirective::class);
$directives->register('for', ForDirective::class);
$directives->register('html', HtmlDirective::class);
$directives->register('text', TextDirective::class);

$compiler = new Compiler([
    'input' => __DIR__ . '/demo/src',
    'output' => __DIR__ . '/demo/dist',
    'directives' => $directives,
    'ignore' => ['InnerBlocks'],
    'hoist' => ['Style', 'Script']
]);

$result = $compiler->compile();

print_r($compiler->get_hoisted_elements());

print_r($result);
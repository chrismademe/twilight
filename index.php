<?php

use function Twilight\compile;
use function Twilight\render;
use Twilight\Twig\Twig;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

Twig::option( 'paths', [ __DIR__ . '/demo/dist' ] );

$result = compile(
    input: __DIR__ . '/demo/src',
    output: __DIR__ . '/demo/dist'
);

echo render(
    template: 'test.twig',
    context: []
);
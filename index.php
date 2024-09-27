<?php

use Twilight\Twilight;
use Twilight\Twig\Twig;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

Twilight::compile(
    input: __DIR__ . '/demo/src',
    output: __DIR__ . '/demo/dist',
    assets: __DIR__ . '/demo/assets'
);

Twilight::find( __DIR__ . '/demo/src', 'test.twig' );

Twilight::render(
    template: 'test.twig',
    context: []
);
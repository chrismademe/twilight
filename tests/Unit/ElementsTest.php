<?php

test('simple html renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/html/01-simple-html.twig');
    $output = file_get_contents(__DIR__ . '/output/html/01-simple-html.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('simple html with class renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/html/02-simple-html-with-class.twig');
    $output = file_get_contents(__DIR__ . '/output/html/02-simple-html-with-class.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('simple self closing html element renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/html/03-simple-html-self-closing.twig');
    $output = file_get_contents(__DIR__ . '/output/html/03-simple-html-self-closing.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('html with dynamic attributes renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/html/04-html-with-dynamic-attributes.twig');
    $output = file_get_contents(__DIR__ . '/output/html/04-html-with-dynamic-attributes.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('html directives renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/html/05-html-with-directives.twig');
    $output = file_get_contents(__DIR__ . '/output/html/05-html-with-directives.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});
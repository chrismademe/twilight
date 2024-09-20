<?php

test('self closing component renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/component/01-simple-component.twig');
    $output = file_get_contents(__DIR__ . '/output/component/01-simple-component.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('self closing component with attributes renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/component/02-component-with-attributes.twig');
    $output = file_get_contents(__DIR__ . '/output/component/02-component-with-attributes.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('self closing component with dynamic attributes renders correctly', function () {
    $input = file_get_contents(__DIR__ . '/input/component/03-component-with-dynamic-attributes.twig');
    $output = file_get_contents(__DIR__ . '/output/component/03-component-with-dynamic-attributes.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('ignored components renders as a string', function () {
    $input = file_get_contents(__DIR__ . '/input/component/04-ignored-component.twig');
    $output = file_get_contents(__DIR__ . '/output/component/04-ignored-component.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});

test('directives render in the correct order', function () {
    $input = file_get_contents(__DIR__ . '/input/component/05-directives-render-in-the-correct-order.twig');
    $output = file_get_contents(__DIR__ . '/output/component/05-directives-render-in-the-correct-order.twig');
    $compiled_input = compile($input);

    expect($compiled_input)->toEqual($output);
});
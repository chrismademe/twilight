<?php

$tests = get_tests(__DIR__ . '/element/*');

foreach ( $tests as $test ) {
    test($test['name'], function () use ($test) {
        $compiled_input = compile($test['input']);
        expect($compiled_input)->toEqual($test['output']);
    });
}
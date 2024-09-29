<?php

$tests = get_tests(__DIR__ . '/component/*');

foreach ( $tests as $test ) {
    test($test['name'], function () use ($test) {
        $compiled_input = compile($test['input']);
        expect($compiled_input)->toEqual($test['output']);
    });
}

test('reserved schema keys throw ReservedKeywordException', function () {
    $schema = [
        'attributes' => [ 'type' => 'array' ],
        'id' => [ 'type' => 'string' ],
    ];

    expect(fn() => new Twilight\Component\ValidateProps('test', [], $schema))
        ->toThrow(Twilight\Exception\ReservedKeywordException::class);
});
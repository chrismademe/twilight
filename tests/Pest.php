<?php

use Twilight\Renderer;
use Twilight\Directives;
use Twilight\Tokenizer;
use Twilight\NodeTree;
use Twilight\Directives\IfDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\AttributesDirective;
use Twilight\Directives\UnlessDirective;
use Twilight\Directives\CheckedDirective;
use Twilight\Directives\DisabledDirective;
use Twilight\Directives\SelectedDirective;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function compile( string $input ): string {
    $directives = new Directives;
    $directives->register('if', IfDirective::class);
    $directives->register('unless', UnlessDirective::class);
    $directives->register('attributes', AttributesDirective::class);
    $directives->register('for', ForDirective::class);
    $directives->register('html', HtmlDirective::class);
    $directives->register('text', TextDirective::class);
    $directives->register('checked', CheckedDirective::class);
    $directives->register('disabled', DisabledDirective::class);
    $directives->register('selected', SelectedDirective::class);

    $tokenizer = new Tokenizer($input);
    $tree = new NodeTree($tokenizer->tokenize(), [
        'ignore' => ['InnerBlocks'],
        'directives' => $directives
    ]);
    $elements = $tree->create();
    $renderer = new Renderer();
    return $renderer->render($elements);
}

function get_tests( string $path ) {
    $tests = [];
    $directories = glob( $path, GLOB_ONLYDIR );
    foreach ($directories as $dir) {
        $tests[] = [
            'name' => str_replace(__DIR__ . '/Unit/', '', $dir),
            'input' => file_get_contents($dir . '/input.twig'),
            'output' => file_get_contents($dir . '/output.twig')
        ];
    }
    return $tests;
}
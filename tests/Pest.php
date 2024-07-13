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

function compile( string $input ): string
{
    $directives = new Directives;
    $directives->register('if', IfDirective::class);
    $directives->register('attributes', AttributesDirective::class);
    $directives->register('for', ForDirective::class);
    $directives->register('html', HtmlDirective::class);
    $directives->register('text', TextDirective::class);

    $tokenizer = new Tokenizer($input);
    $tree = new NodeTree($tokenizer->tokenize(), [
        'ignore' => ['InnerBlocks'],
        'directives' => $directives
    ]);
    $elements = $tree->create();
    $compiler = new Compiler();
    return $compiler->compile($elements);
}

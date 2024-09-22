<?php

namespace Twilight;

use Twilight\Directives;
use Twilight\Compiler\Compiler;
use Twilight\Directives\IfDirective;
use Twilight\Directives\UnlessDirective;
use Twilight\Directives\AttributesDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\CheckedDirective;
use Twilight\Directives\DisabledDirective;
use Twilight\Directives\SelectedDirective;
use Twilight\Twig\Twig;

/**
 * Compile
 *
 * Compile all files in a given directory
 *
 * @param string $input Input directory
 * @param string $output Output directory
 */
function compile(
    string $input,
    string $output,
    array $hoist = [],
    array $ignore = [],
    bool $if = true,
): array|null {
    if ( $if !== true ) return null;

    $directives = new Directives;
    $directives->register( 'if', IfDirective::class );
    $directives->register( 'unless', UnlessDirective::class );
    $directives->register( 'attributes', AttributesDirective::class );
    $directives->register( 'for', ForDirective::class );
    $directives->register( 'html', HtmlDirective::class );
    $directives->register( 'text', TextDirective::class );
    $directives->register( 'checked', CheckedDirective::class );
    $directives->register( 'disabled', DisabledDirective::class );
    $directives->register( 'selected', SelectedDirective::class );

    $compile = new Compiler;
    $result = $compile
        ->from( $input )
        ->to( $output )
        ->hoist( array_merge( ['Style', 'Script'], $hoist ) )
        ->ignore( array_merge( ['InnerBlocks'], $ignore ) )
        ->directives( $directives )
        ->compile();

    return $result;
}

/**
 * Render
 *
 * Render a Twig template
 *
 * @param string $template Template path
 * @param array $context Data to pass to the template.
 */
function render( string $template, array $context = [] ): string {
    $twig = new Twig;
    return $twig->render( $template, $context );
}
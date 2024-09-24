<?php

namespace Twilight;

use Twilight\Compiler\Compiler;
use Twilight\Directives;
use Twilight\Directives\AttributesDirective;
use Twilight\Directives\CheckedDirective;
use Twilight\Directives\DisabledDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\IfDirective;
use Twilight\Directives\SelectedDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\UnlessDirective;
use Twilight\Events;
use Twilight\Exception\InvalidPropException;
use Twilight\Twig\Twig;
use Twilight\Component\ValidateProps;

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
    bool $autodiscover = true
): array|null {

    /**
     * Autodiscover Component PHP files
     *
     * If the component directory contains a component.php file,
     * we'll include it here.
     */
    if ( $autodiscover === true ) {
        $files = glob( $input . '/components/**/component.php' );
        if ( ! empty( $files ) ) {
            foreach ( $files as $file ) {
                require_once $file;
            }
        }
    }

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

/**
 * Props
 *
 * Takes in an array of Component prop names and an array of props and
 * returns a tuple, one array with the defined props and one
 * with all the rest.
 *
 * @param string $component Component Name
 * @param array $props All Props (the $context array)
 * @param array $schema Prop Schema
 */
function props( string $component, array $props, array $schema ): array {
    $validator = new ValidateProps( $component, $props, $schema );
    return $validator->context();
}

/**
 * On
 *
 * Registers an event listener.
 */
function on( string $name, callable $callback ): void {
    Events::on( $name, $callback );
}

/**
 * Filter
 *
 * Dispatches an event and allows for modification of the context.
 */
function filter( string $name, ...$context ): mixed {
    return Events::filter( $name, ...$context );
}

/**
 * Dispatch
 *
 * Dispatches an event without modifying the context.
 */
function dispatch( string $name, ...$context ): void {
    Events::dispatch( $name, ...$context );
}

/**
 * Classnames
 *
 * Conditionally returns a string of classnames based on the given array.
 * Works very similarly to the classnames npm package.
 *
 * @param array $classes
 * @return string
 */
function classnames( array $classes ): string {
    $classes_to_render = [];

    foreach ( $classes as $class => $condition ) {
        if ( ! is_int($class) && $condition !== false && $condition !== null ) {
            $classes_to_render[] = $class;
            continue;
        }

        /**
         * If the condition is a string, then it's actually just a class without
         * a condition, so include it
         */
        if ( is_string($condition) ) {
            $classes_to_render[] = $condition;
        }
    }

    return implode( ' ', $classes_to_render );
}
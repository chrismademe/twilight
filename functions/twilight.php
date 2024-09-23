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
    $schema_props = [];
    $rest = [];

    foreach ( $props as $key => $value ) {
        if ( array_key_exists( $key, $schema ) ) {
            $schema_props[ $key ] = $value;
        } else {

            // Skip reserved keys
            if ( $key === 'children' || $key === 'slots' ) {
                continue;
            }

            $rest[ $key ] = $value;
        }
    }

    // Validate Props
    $context = validate_props( $component, $schema_props, $schema );
    $context['attributes'] = $rest;
    $context['children'] = $props['children'] ?? '';
    $context['slots'] = $props['slots'] ?? [];

    return $context;
}

/**
 * Validate Props
 *
 * Validates defined props for the correct type,
 * whether it's required and sets a default value
 * if provided.
 *
 * @param string $component
 * @param array $props
 * @param array $schema
 */
function validate_props( string $component, array $props, array $schema ): array {

    foreach ( $schema as $key => $value ) {
        if ( ! array_key_exists( $key, $props ) ) {
            if ( array_key_exists( 'default', $value ) ) {
                $props[ $key ] = $value['default'];
            } else {
                if ( array_key_exists( 'required', $value ) && $value['required'] === true ) {
                    throw new InvalidPropException( sprintf( 'Prop `%s` is required in **%s**.', $key, $component ) );
                }
            }
        }

        /**
         * Validate the prop using a custom validator
         */
        if (
            array_key_exists( 'validator', $value )
            && is_callable( $value['validator'] )
            && array_key_exists( $key, $props )
        ) {
            $result = call_user_func(
                $value['validator'],
                $props[ $key ],
                $key,
                $props
            );
            if ( $result === false ) {
                $warning = sprintf(
                    'Prop `%s` failed validation in **%s**.',
                    $key,
                    $component
                );
                throw new InvalidPropException( $warning );
            }
        }

        if ( array_key_exists( 'type', $value ) && array_key_exists( $key, $props ) ) {
            if ( validate_prop_type( $props[ $key ], $value ) === false ) {

                if ( $value['type'] === 'enum' ) {
                    $warning = sprintf(
                        'Prop `%s` must be one of the following values: `%s` in **%s**.',
                        $key,
                        implode( ', ', $value['values'] ),
                        $component
                    );
                    throw new InvalidPropException( $warning );
                    continue;
                }

                if ( $value['type'] === 'instanceof' ) {
                    $warning = sprintf(
                        'Prop `%s` must be an instance of `%s` in **%s**.',
                        $key,
                        $value['instanceof'],
                        $component
                    );
                    throw new InvalidPropException( $warning );
                    continue;
                }

                $warning = sprintf(
                    'Prop `%s` is not of type `%s` in **%s**.',
                    $key,
                    $value['type'],
                    $component
                );
                throw new InvalidPropException( $warning );
            }
        }
    }

    return $props;
}

/**
 * Validate Prop Type
 *
 * Validates a prop type.
 *
 * @param mixed $prop
 * @param string $type
 */
function validate_prop_type( $value, array $type ): bool {

    if ( $type['type'] === 'string' && is_string( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'number' && is_numeric( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'array' && is_array( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'enum' && in_array( $value, $type['values'] ) ) {
        return true;
    }

    if ( $type['type'] === 'bool' && celeste_is_boolean_like( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'boolean' && celeste_is_boolean_like( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'int' && is_int( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'float' && is_float( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'object' && is_object( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'callable' && is_callable( $value ) ) {
        return true;
    }

    if ( $type['type'] === 'instanceof' && $value instanceof $type['instanceof'] ) {
        return true;
    }

    return false;

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
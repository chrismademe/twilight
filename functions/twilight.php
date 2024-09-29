<?php

namespace Twilight;

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

function make_element_attributes( array $attributes ): string {
    if ( empty( $attributes ) ) {
        return '';
    }

    $attributes_to_render = [];

    foreach ( $attributes as $attribute => $value ) {
        $is_dynamic = str_starts_with( $attribute, ':' );

        if ( $value === false || $value === null ) {
            continue;
        }

        if ( $value === true ) {
            $attributes_to_render[] = $attribute;
            continue;
        }

        $name = $is_dynamic ? substr( $attribute, 1 ) : $attribute;
        $attributes_to_render[] = sprintf( '%s="%s"', $name, $value );
    }

    return ' ' . implode( ' ', $attributes_to_render );
}
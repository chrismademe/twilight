<?php

namespace Twilight\Nodes;

trait HasHTMLAttributes {
    public array $attributes;

    public function set_attributes( array $attributes = [] ): void {
        foreach ( $attributes as $key => $value ) {
            $this->attributes[ $key ] = new HTMLAttribute($key, $value);
        }
    }

    public function has_attributes(): bool {
        return ! empty( $this->attributes );
    }

    public function set_attribute( string $key, $value ): void {
        $this->attributes[ $key ] = new HTMLAttribute($key, $value);
    }

    public function get_attribute( string $key ): HTMLAttribute {
        return $this->attributes[ $key ];
    }

    public function remove_attribute( string $key ): void {
        unset( $this->attributes[ $key ] );
    }

    public function has_attribute( string $key ): bool {
        return isset( $this->attributes[ $key ] );
    }

    public function is_compiler_attribute( string $key ): bool {
        return in_array( $key, [ 'is' ] );
    }
}
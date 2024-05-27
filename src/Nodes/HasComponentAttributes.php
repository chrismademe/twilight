<?php

namespace Twilight\Nodes;

trait HasComponentAttributes {
    public array $attributes;

    public function set_attributes( array $attributes = [] ): void {
        foreach ( $attributes as $key => $value ) {
            $this->attributes[ $key ] = new ComponentAttribute($key, $value);
        }
    }

    public function has_attributes(): bool {
        return ! empty( $this->attributes );
    }

    public function set_attribute( string $key, string $value ): void {
        $this->attributes[ $key ] = new ComponentAttribute($key, $value);
    }

    public function get_attribute( string $key ): ComponentAttribute {
        return $this->attributes[ $key ];
    }

    public function remove_attribute( string $key ): void {
        unset( $this->attributes[ $key ] );
    }

    public function has_attribute( string $key ): bool {
        return isset( $this->attributes[ $key ] );
    }
}
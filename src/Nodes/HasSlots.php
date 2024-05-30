<?php

namespace Twilight\Nodes;

trait HasSlots {
    public array $slots;

    public function set_slots( array $slots = [] ): void {
        foreach ( $attributes as $key => $value ) {
            $this->slots[ $key ] = new ComponentSlot($key, $value);
        }
    }

    public function get_slots(): array {
        return $this->slots;
    }

    public function has_slots(): bool {
        return ! empty( $this->slots );
    }

    public function set_slot( string $key, $value ): void {
        $this->slots[ $key ] = new ComponentSlot($key, $value);
    }

    public function get_slot( string $key ): ComponentSlot {
        return $this->slots[ $key ];
    }

    public function remove_slot( string $key ): void {
        unset( $this->slots[ $key ] );
    }

    public function has_slot( string $key ): bool {
        return isset( $this->slots[ $key ] );
    }
}
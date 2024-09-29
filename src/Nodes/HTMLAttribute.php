<?php

namespace Twilight\Nodes;

class HTMLAttribute {

    public function __construct( private string $name, private $value ) {}

    public function __get( string $key ) {
        return $this->$key;
    }

    public function render(): string {
        // $rendered_name = $this->is_dynamic()
        //     ? substr( $this->name, 1 )
        //     : $this->name;

        // $rendered_value = $this->is_dynamic()
        //     ? sprintf( '="{{ %s }}"', $this->value )
        //     : sprintf( '="%s"', $this->value );

        $value = $this->is_dynamic() ? $this->value : sprintf( '"%s"', $this->value );

        return sprintf(
            '"%s": %s, ',
            $this->name,
            $value
        );
    }

    public function is_dynamic(): bool {
        return str_starts_with( $this->name, ':' );
    }

    public function is_directive(): bool {
        return str_starts_with( $this->name, '@' );
    }

}
<?php

namespace Twilight;

use Exception;

class Directives {

    private array $directives = [];

    /**
     * Register
     *
     * @param string $name
     * @param $directive
     * @throws Exception
     * @return void
     */
    public function register( string $name, $directive ): void {

        if ( $this->is_registered( $name ) ) {
            throw new Exception( "Directive $name is already registered" );
        }

        $this->directives[$name] = new $directive;
    }

    /**
     * Is Registered
     *
     * @param string $name
     * @return bool
     */
    public function is_registered( string $name ): bool {
        return isset( $this->directives[$name] );
    }

    /**
     * Get All
     */
    public function all(): array {
        return $this->directives;
    }

    /**
     * Get
     *
     * Return a single directive
     * @param string $name
     */
    public function get( string $name ) {
        return $this->is_registered($name) ? $this->directives[$name] : null;
    }

    /**
     * Empty
     */
    public function is_empty(): bool {
        return empty( $this->directives );
    }

}
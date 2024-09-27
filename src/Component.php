<?php

namespace Twilight;

use Twilight\Events;
use Twilight\Component\ValidateProps;

class Component {

    private array $schema;
    private $render;
    private $found;

    public function __construct( private string $name ) {}

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @return self
     */
    public static function name( string $name ) {
        return new self( $name );
    }

    /**
     * Set the schema for the component.
     *
     * @param array $schema
     * @return self
     */
    public function schema( array $schema ) {
        $this->schema = $schema;
        return $this;
    }

    /**
     * Set the render callback for the component.
     *
     * @param callable $callback
     * @return self
     */
    public function data( callable $callback ) {
        $this->render = $callback;
        return $this;
    }

    /**
     * Set the found callback for the component.
     *
     * @param callable $callback
     * @return self
     */
    public function present( callable $callback ) {
        $this->found = $callback;
        return $this;
    }

    /**
     * Register the component and run all callbacks
     */
    public function register() {
        if ( is_callable($this->found) ) {
            Events::on(
                sprintf( 'component:%s:present', $this->name ),
                $this->found
            );
        }

        Events::on(
            sprintf( 'component:%s:render', $this->name ),
            function( $data ) {
                $data = ! empty($this->schema)
                    ? $this->validate_schema( $data )
                    : $data;

                return is_callable( $this->render )
                    ? call_user_func( $this->render, $data )
                    : $data;
            }
        );
    }

    /**
     * Validate the schema for the component.
     *
     * @param array|null $data
     * @return array
     */
    public function validate_schema( array|null $data = [] ) {
        $validator = new ValidateProps( $this->name, $data, $this->schema );
        return $validator->context();
    }

}
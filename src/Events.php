<?php

namespace Twilight;

class Events {

    private static $instance = null;
    private $events = [];

    /**
     * On
     *
     * Registers an event listener.
     *
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public static function on( string $name, callable $callback ): void {
        self::instance()->events[ $name ][] = $callback;
    }

    /**
     * Filter
     *
     * Dispatches an event and allows for modification of the context.
     *
     * @param string $name
     * @param mixed $context
     * @return mixed
     */
    public static function filter( string $name, ...$context ): mixed {
        if ( ! isset( self::instance()->events[ $name ] ) ) {
            return $context;
        }

        foreach ( self::instance()->events[ $name ] as $callback ) {
            $context = call_user_func_array( $callback, $context );
        }

        return $context;
    }

    /**
     * Dispatch
     *
     * Dispatches an event without modifying the context.
     *
     * @param string $name
     * @param mixed $context
     * @return void
     */
    public static function dispatch( string $name, ...$context ): void {
        if ( ! isset( self::instance()->events[ $name ] ) ) {
            return;
        }

        foreach ( self::instance()->events[ $name ] as $callback ) {
            call_user_func_array( $callback, $context );
        }
    }

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

}
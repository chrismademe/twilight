<?php

namespace Twilight\Component;

use Twilight\Exception\InvalidPropException;

class ValidateProps {

    private $schema_props = [];
    private $rest = [];

    public function __construct(
        private string $name,
        private array $props = [],
        private array $schema = []
    ) {

        foreach ( $this->props as $key => $value ) {

            // Skip reserved keys
            if ( $this->is_reserved_key( $key ) ) {
                continue;
            }

            if ( array_key_exists( $key, $this->schema ) ) {
                $this->schema_props[ $key ] = $value;
                continue;
            }

            $this->rest[ $key ] = $value;
        }

    }

    /**
     * Context
     *
     * Returns the context array with all the props
     *
     * @return array
     * @throws InvalidPropException
     */
    public function context() {
        $context = $this->validate();
        $context['attributes'] = $this->rest ?? null;
        $context['children'] = $this->props['children'] ?? '';
        $context['slots'] = $this->props['slots'] ?? [];
        return $context;
    }

    /**
     * Validate
     *
     * Validates defined props for the correct type,
     * whether it's required and sets a default value
     *
     * @return array
     * @throws InvalidPropException
     */
    private function validate() {
        foreach ( $this->schema as $key => $value ) {
            if ( ! array_key_exists( $key, $this->schema_props ) ) {
                if ( array_key_exists( 'default', $value ) ) {
                    $this->schema_props[ $key ] = $value['default'];
                } else {
                    if ( array_key_exists( 'required', $value ) && $value['required'] === true ) {
                        throw new InvalidPropException( sprintf( 'Prop `%s` is required in **%s**.', $key, $this->name ) );
                    }
                }
            }

            /**
             * Validate the prop using a custom validator
             */
            if (
                array_key_exists( 'validator', $value )
                && is_callable( $value['validator'] )
                && array_key_exists( $key, $this->schema_props )
            ) {
                $result = call_user_func(
                    $value['validator'],
                    $this->schema_props[ $key ],
                    $key,
                    $this->props
                );
                if ( $result === false ) {
                    $warning = sprintf(
                        'Prop `%s` failed validation in **%s**.',
                        $key,
                        $this->name
                    );
                    throw new InvalidPropException( $warning );
                }
            }

            if ( array_key_exists( 'type', $value ) && array_key_exists( $key, $this->schema_props ) ) {
                if ( $this->validate_type( $this->schema_props[ $key ], $value ) === false ) {

                    if ( $value['type'] === 'enum' ) {
                        $warning = sprintf(
                            'Prop `%s` must be one of the following values: `%s` in **%s**.',
                            $key,
                            implode( ', ', $value['values'] ),
                            $this->name
                        );
                        throw new InvalidPropException( $warning );
                        continue;
                    }

                    if ( $value['type'] === 'instanceof' ) {
                        $warning = sprintf(
                            'Prop `%s` must be an instance of `%s` in **%s**.',
                            $key,
                            $value['instanceof'],
                            $this->name
                        );
                        throw new InvalidPropException( $warning );
                        continue;
                    }

                    $warning = sprintf(
                        'Prop `%s` is not of type `%s` in **%s**.',
                        $key,
                        $value['type'],
                        $this->name
                    );
                    throw new InvalidPropException( $warning );
                }
            }
        }

        return $this->schema_props;
    }

    /**
     * Validate Type
     *
     * Validates the type of the prop
     *
     * @param mixed $value
     * @param array $type
     * @return bool
     */
    private function validate_type( $value, array $type ) {
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

        if ( $type['type'] === 'bool' && $this->is_boolean_like( $value ) ) {
            return true;
        }

        if ( $type['type'] === 'boolean' && $this->is_boolean_like( $value ) ) {
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
     * Is Boolean Like
     *
     * Check if a value is boolean-like
     *
     * @param mixed $value
     * @return bool
     */
    private function is_boolean_like( $value ) {
        return is_bool( $value ) || $value === 1 || $value === 0 || $value === '1' || $value === '0';
    }

    private function is_reserved_key( string $key ) {
        return in_array( $key, [ 'children', 'slots' ] );
    }

}
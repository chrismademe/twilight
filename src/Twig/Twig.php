<?php

namespace Twilight\Twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twilight\Events;

class Twig {

    private static array $paths = [];
    private static array $options = [];
	private FilesystemLoader $loader;
	private string $template;
    private Environment $instance;

	/**
	 * Constructor
	 *
	 * @param array $dir Path to the Twig templates directory.
	 */
	public function __construct() {

		$this->loader = new FilesystemLoader( self::$options['paths'] );
		$this->instance = new Environment( $this->loader, self::$options['twig'] ?? [] );

		$this->instance->addFunction(
			new TwigFunction(
				'get_component_context',
				[ $this, 'get_component_context' ],
				[ 'is_safe' => [ 'html' ] ]
			)
		);

		$this->instance->addfunction(
			new TwigFunction(
				'make_element_attributes',
				[ $this, 'make_element_attributes' ],
				[ 'is_safe' => [ 'html' ] ]
			)
		);

		$this->instance->addFilter(
			new TwigFilter(
				'cls',
				'\\Twilight\\classnames'
			)
		);

		Events::dispatch( 'twig:init', $this->instance );

	}

	/**
	 * Instance
	 *
	 * Returns the Twig instance.
	 *
	 * @return Environment
	 */
	public function instance() {
		return $this->instance;
	}

	/**
	 * Render a Twig Template
	 *
	 * @param string $template Template path
	 * @param array $context Data to pass to the template.
	 * @return string Rendered template.
	 */
	public function render( string $template, array|null $context = [] ): string {

		/**
		 * Filter the context before render
		 */
		$context = Events::filter( 'render', $context );
		$context = Events::filter( $template . ':render', $context );

		return $this->instance->render( $template, $context ?? [] );
	}

	/**
	 * Get Component Context
	 *
	 * Takes the props being passed to a component include and passes them
	 * through a filter to allow for modification of the context.
	 *
	 * @param string $name Component name
	 * @param array|null $context Component context
	 */
	public function get_component_context( string $name, $context ) {
		$context = Events::filter( 'component:render', $context );
		$context = Events::filter( 'component:' . $name . ':render', $context );
		return $context;
	}

	/**
	 * Make Element Attributes
	 *
	 * Conditionally returns a string of HTML attributes based on the given array.
	 * Given a string, it will return the string as is.
	 *
	 * @param array|string|null $attributes
	 * @return string
	 */
	public function make_element_attributes( array|string|null $attributes ): string {
		if ( empty( $attributes ) || is_null( $attributes ) ) {
			return '';
		}

		if ( is_string($attributes) ) {
			return ' ' . $attributes;
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

    /**
     * Option
     *
     * Set or Get an option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return mixed
     */
    public static function option( string $key, mixed $value = null ): mixed {
        if ( ! is_null( $value ) ) {
            self::$options[ $key ] = $value;
            return $value;
        }

        return self::$options[ $key ] ?? null;
    }

}
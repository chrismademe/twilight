<?php

namespace Twilight\Twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twilight\Events;
use function Twilight\classnames;

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
				'render_component',
				[ $this, 'render_component' ],
				[ 'is_safe' => [ 'html' ] ]
			)
		);

		$this->instance->addFilter(
			new TwigFilter(
				'cls',
				'\\Twilight\\classnames'
			)
		);

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
	public function render( string $template, array $context = [] ): string {
		return $this->instance->render( $template, $context );
	}

	/**
	 * Render a Component
	 *
	 * @param string $name Component name.
	 * @param array|null $context Context to pass to the component.
	 * @return void
	 */
	public function render_component( string $name, array|null $context = [] ) {
		$path = str_replace( ['_', '.'], '/', $name );

		/**
		 * Filter the context before rendering the component.
		 */
		$context = Events::filter( $name . ':render', $context );

		/**
		 * If a custom callback is set, use it to render the component.
		 */
		if (
			isset( self::$options['render_component_callback'] )
			&& is_callable( self::$options['render_component_callback'] )
		) {
			return call_user_func(
				self::$options['render_component_callback'],
				$name,
				$path,
				$context,
				$this
			);
		}

        return $this->render( 'components/' . $path . '/template.twig', $context );
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
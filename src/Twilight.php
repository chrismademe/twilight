<?php

namespace Twilight;

use Twilight\Compiler\Compiler;
use Twilight\Directives;
use Twilight\Directives\ConditionalAttributeDirective;
use Twilight\Directives\AttributesDirective;
use Twilight\Directives\CheckedDirective;
use Twilight\Directives\DisabledDirective;
use Twilight\Directives\ForDirective;
use Twilight\Directives\HtmlDirective;
use Twilight\Directives\IfDirective;
use Twilight\Directives\SelectedDirective;
use Twilight\Directives\TextDirective;
use Twilight\Directives\UnlessDirective;
use Twilight\Events;
use Twilight\Exception\InvalidPropException;
use Twilight\Twig\Twig;
use Twilight\Component\ValidateProps;
use Twilight\Component\FindComponents;

class Twilight {

    /**
     * Compile
     *
     * Compile all files in a given directory
     *
     * @param string $input Input directory
     * @param string $output Output directory
     */
    public static function compile(
        string $input,
        string $output,
        string $assets = null,
        array $hoist = [],
        array $ignore = [],
        bool $if = true,
        bool $autodiscover = true
    ): array|null {

        /**
         * Autodiscover Component PHP files
         *
         * If the component directory contains a component.php file,
         * we'll include it here.
         */
        if ( $autodiscover === true ) {
            $files = glob( $input . '/components/**/component.php' );
            if ( ! empty( $files ) ) {
                foreach ( $files as $file ) {
                    require_once $file;
                }
            }
        }

        /**
         * Set the Twig paths
         */
        Twig::option( 'paths', [ $output ] );

        if ( $if !== true ) return null;

        $directives = new Directives;
        $directives->register( 'if', IfDirective::class );
        $directives->register( 'unless', UnlessDirective::class );
        $directives->register( 'attributes', AttributesDirective::class );
        $directives->register( 'for', ForDirective::class );
        $directives->register( 'html', HtmlDirective::class );
        $directives->register( 'text', TextDirective::class );
        $directives->register( 'checked', CheckedDirective::class );
        $directives->register( 'disabled', DisabledDirective::class );
        $directives->register( 'selected', SelectedDirective::class );

        $compile = new Compiler;
        $result = $compile
            ->from( $input )
            ->to( $output )
            ->assets_to( $assets )
            ->hoist( array_merge( ['Style', 'Script'], $hoist ) )
            ->ignore( array_merge( ['InnerBlocks'], $ignore ) )
            ->directives( $directives )
            ->compile();

        return $result;
    }

    /**
     * Render
     *
     * Render a Twig template
     *
     * @param string $template Template path
     * @param array $context Data to pass to the template.
     * @param bool $to_string Echo the template or return it.
     */
    public static function render( string $template, array|null $context = [], bool $to_string = false ) {
        $twig = new Twig;
        $result = $twig->render( $template, $context );

        if ( $to_string === true ) {
            return $result;
        }

        echo $result;
    }

    /**
     * Find Components
     *
     * Searches a template for components and returns an array of component names.
     *
     * @param string $dir
     * @param string $template
     */
    public static function find( string $dir, string $template ) {
        $finder = new FindComponents( $dir );
        $components = $finder->find( $template );

        if ( ! empty($components) ) {
            foreach ( $components as $name ) {
                Events::dispatch( sprintf('component:%s:present', $name) );
            }
        }

        return $components;
    }

}
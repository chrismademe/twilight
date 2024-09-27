<?php

namespace Twilight\Component;

class FindComponents {

    const PATTERN = '/<([A-Z][A-Za-z0-9\.]*)(\s[^>]*)?>/';

    public function __construct( private string $dir ) {}

    public function find( string $template ) {
        // Get the component names from the template, then loop through them and
        // resurively find their components
        $components = $this->get_component_names_from_template( $template );

        foreach ( $components as $component ) {
            $template = sprintf( 'components/%s/template.twig', $this->format_component_name( $component ) );

            if ( file_exists( $this->dir . '/' . $template ) ) {
                $components = array_merge( $components, $this->find( $template ) );
            }
        }

        return array_unique( $components );
    }

    public function get_component_names_from_template( string $template ) {
        $template_content = file_get_contents( $this->dir . '/' . $template );
        preg_match_all( self::PATTERN, $template_content, $matches);
        return $matches[1] ?? [];
    }

    private function format_component_name( string $name ) {
        return str_replace( '.', '/', $name );
    }

}
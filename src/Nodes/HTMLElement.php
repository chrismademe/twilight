<?php

namespace Twilight\Nodes;

class HTMLElement implements NodeInterface {
    use CanBeSelfClosing, CanHaveDynamicName, HasHTMLAttributes, HasChildren, HasDirectives;

    public string $ref;

    public function __construct(
        public string $name
    ) {
        /**
         * Generate a unique reference for this element instance.
         * We use this when creating the Twig markup for child elements.
         */
        $this->ref = bin2hex( random_bytes(5) );
    }

    /**
     * Render the HTML element to Twig markup.
     */
    public function render(): string {
        $markup = '';

        $markup .= $this->process_directives('before');

        $markup .= $this->has_dynamic_name()
            ? sprintf( '<{{ %s }}', $this->dynamic_name )
            : sprintf( '<%s', $this->name );

        if ( $this->has_attributes() ) {
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $markup .= sprintf( ' %s', $attribute->render());
            }
        }

        $markup .= '>';

        // Self closing elements cannot have children, so we're done
        if ( $this->is_self_closing() ) {
            $markup .= $this->process_directives('after');
            return $markup;
        }

        if ( $this->has_children() ) {
            foreach ( $this->get_children() as $child ) {
                $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
            }
        }

        $markup .= $this->has_dynamic_name()
            ? sprintf( '</{{ %s }}>%s', $this->dynamic_name, PHP_EOL )
            : sprintf( '</%s>%s', $this->name, PHP_EOL );

        $markup .= $this->process_directives('after');

        return $markup;
    }
}
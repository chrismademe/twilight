<?php

namespace Twilight\Nodes;

class HTMLElement implements NodeInterface {
    use CanBeSelfClosing, CanHaveDynamicName, HasHTMLAttributes, HasChildren, HasDirectives;

    public function __construct(
        public string $name,
        public bool $is_self_closing = false,
    ) {}

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
                if ( $this->is_compiler_attribute($attribute->name) ) continue; // Skip compiler attributes
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $markup .= sprintf( ' %s', $attribute->render());
            }
        }

        $markup .= $this->is_self_closing ? ' />' : '>';

        // Self closing elements cannot have children, so we're done
        if ( $this->is_self_closing() ) {
            $markup .= $this->process_directives('after');
            return $markup;
        }

        if ( $this->has_children() ) {
            foreach ( $this->get_children() as $child ) {
                $markup .= $child->render();
            }
        }

        $markup .= $this->has_dynamic_name()
            ? sprintf( '</{{ %s }}>', $this->dynamic_name )
            : sprintf( '</%s>', $this->name );

        $markup .= $this->process_directives('after');

        return $markup;
    }
}
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

        if ( $this->has_attributes() ) {
            $__rendered_attributes = [];
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_compiler_attribute($attribute->name) ) continue; // Skip compiler attributes
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $__rendered_attributes[] = $attribute->render();
            }

            if ( ! empty($__rendered_attributes) ) {
                $attr_markup = sprintf( '{%% set %s_attributes = { ', $this->name );
                $attr_values = [];
                foreach ( $__rendered_attributes as $attr ) {
                    $attr_values[] = sprintf( "'%s': %s", $attr['name'], $attr['value'] );
                }
                $attr_markup .= implode( ', ', $attr_values );
                $attr_markup .= ' } %}';
                $markup .= $attr_markup;
            }
        }

        $markup .= $this->has_dynamic_name()
            ? sprintf( '<{{ %s }}', $this->dynamic_name )
            : sprintf( '<%s', $this->name );

        if ( $this->has_attributes() ) {
            if ( ! empty($__rendered_attributes) ) {
                $markup .= sprintf( '{%% for name, value in %s_attributes %%} {{ name }}="{{ value }}"{%% endfor %%}', $this->name );
            }
        }

        $markup .= $this->process_directives('tag');

        $markup .= $this->is_self_closing ? ' />' : '>';

        // Self closing elements cannot have children, so we're done
        if ( $this->is_self_closing() ) {
            $markup .= sprintf( '{%% set %s_attributes = null %%}', $this->name );
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

        if ( ! $this->is_self_closing() ) {
            $markup .= sprintf( '{%% set %s_attributes = null %%}', $this->name );
        }

        $markup .= $this->process_directives('after');
        $markup .= $this->process_directives('cleanup');

        return $markup;
    }
}
<?php

namespace Twilight\Nodes;

class Component implements NodeInterface {
    use HasComponentAttributes, HasChildren, HasDirectives;

    private string $ref;

    public function __construct( public string $name ) {
        /**
         * Generate a unique reference for this component instance.
         * We use this when creating the Twig markup for child elements.
         */
        $this->ref = bin2hex( random_bytes(5) );
    }

    /**
     * Render the component to Twig markup.
     */
    public function render(): string {
        $markup = sprintf( '<!-- %s [%s] -->%s', $this->name, $this->ref, PHP_EOL );

        $markup .= $this->process_directives('before');

        if ( $this->has_children() ) {
            $markup .= sprintf( '{%% set %s_%s_children %%}', $this->name, $this->ref );
            foreach ( $this->get_children() as $child ) {
                $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
            }
            $markup .= '{% endset %}';
        }

        $markup .= sprintf( '{{ render_component("%s"', $this->name );

        if ( $this->has_attributes() ) {
            $attributes = [];
            $markup .= ', {';
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $attributes[] = $attribute->render();
            }
            $markup .= implode(', ', $attributes);
        }

        if ( $this->has_attributes() && $this->has_children() ) {
            $markup .= sprintf( ', children: %s_%s_children', $this->name, $this->ref );
        }

        if ( ! $this->has_attributes() && $this->has_children() ) {
            $markup .= sprintf( ', { children: %s_%s_children }) }}', $this->name, $this->ref );
        }

        if ( $this->has_attributes() ) {
            $markup .= '}) }}';
        }

        $markup .= $this->process_directives('after');

        $markup .= sprintf( '<!-- /%s [%s] -->%s', $this->name, $this->ref, PHP_EOL );

        return $markup;
    }
}
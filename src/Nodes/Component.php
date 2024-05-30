<?php

namespace Twilight\Nodes;

class Component implements NodeInterface {
    use HasComponentAttributes, HasChildren, HasDirectives, HasSlots;

    public string $ref;

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
        $markup = '';

        $markup .= $this->process_directives('before');

        if ( $this->has_slots() ) {
            foreach ( $this->get_slots() as $slot ) {
                $markup .= sprintf( '{%% set %s_%s_slot_%s %%}', $this->name, $this->ref, $slot->name );
                foreach ( $slot->value as $child ) {
                    $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
                }
                $markup .= '{% endset %}';
            }

            $slot_variables = array_map( function($slot) {
                return sprintf( '"%s": %s_%s_slot_%s', $slot->name, $this->name, $this->ref, $slot->name );
            }, $this->get_slots() );

            $markup .= sprintf( '{%% set %s_%s_slots = { ', $this->name, $this->ref );
            $markup .= implode(', ', $slot_variables);
            $markup .= ' } %}';
        }

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
            $markup .= ', { ';
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $attributes[] = $attribute->render();
            }
            $markup .= implode(', ', $attributes);
        }

        if ( ! $this->has_attributes() && ( $this->has_slots() || $this->has_children() ) ) {
            $markup .= ', { ';
        }

        if ( $this->has_attributes() && ( $this->has_slots() || $this->has_children() ) ) {
            $markup .= ', ';
        }

        if ( $this->has_slots() ) {
            $props['slots'] = sprintf( '"slots": %s_%s_slots', $this->name, $this->ref );
        }

        if ( $this->has_children() ) {
            $props['children'] = sprintf( '"children": %s_%s_children', $this->name, $this->ref );
        }

        if ( isset($props) ) {
            $markup .= implode(', ', $props);
        }

        if ( $this->has_attributes() || $this->has_children() || $this->has_slots() ) {
            $markup .= ' }';
        }

        $markup .= ') }}';

        $markup .= $this->process_directives('after');

        return $markup;
    }
}
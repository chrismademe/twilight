<?php

namespace Twilight\Nodes;

class Component implements NodeInterface {
    use CanHaveDynamicName, HasComponentAttributes, HasChildren, HasDirectives, HasSlots;

    public function __construct( public string $name ) {}

    /**
     * Render the component to Twig markup.
     */
    public function render(): string {
        $markup = '';

        $markup .= $this->process_directives('before');
        $markup .= $this->process_directives('tag');

        $this->render_name = $this->render_name();

        if ( $this->has_slots() ) {
            foreach ( $this->get_slots() as $slot ) {
                $markup .= sprintf( '{%% set %s_slot_%s %%}', $this->render_name, $slot->name );
                foreach ( $slot->value as $child ) {
                    $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
                }
                $markup .= '{% endset %}';
            }

            $slot_variables = array_map( function($slot) {
                return sprintf( '"%s": %s_slot_%s', $slot->name, $this->render_name, $slot->name );
            }, $this->get_slots() );

            $markup .= sprintf( '{%% set %s_slots = { ', $this->render_name );
            $markup .= implode(', ', $slot_variables);
            $markup .= ' } %}';
        }

        if ( $this->has_children() ) {
            $markup .= sprintf( '{%% set %s_children %%}', $this->render_name );
            foreach ( $this->get_children() as $child ) {
                $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
            }
            $markup .= '{% endset %}' . PHP_EOL;
        }

        $name = $this->has_dynamic_name()
            ? $this->render_name
            : sprintf( '"%s"', $this->render_name );

        $markup .= sprintf( '{{ render_component(%s', $name );
        $attributes = []; // Keep track of rendered attributes

        if ( $this->has_attributes() ) {
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $attributes[] = $attribute->render();
            }
            $markup .= empty($attributes) ? '' : ', { ';
            $markup .= implode(', ', $attributes);
        }

        if ( empty($attributes) && ( $this->has_slots() || $this->has_children() ) ) {
            $markup .= ', { ';
        }

        if ( ! empty($attributes) && ( $this->has_slots() || $this->has_children() ) ) {
            $markup .= ', ';
        }

        if ( $this->has_slots() ) {
            $props['slots'] = sprintf( '"slots": %s_slots', $this->render_name );
        }

        if ( $this->has_children() ) {
            $props['children'] = sprintf( '"children": %s_children', $this->render_name );
        }

        if ( isset($props) ) {
            $markup .= implode(', ', $props);
        }

        if ( ! empty($attributes) || $this->has_children() || $this->has_slots() ) {
            $markup .= ' }';
        }

        $markup .= ') }}';

        $markup .= $this->process_directives('after');
        $markup .= $this->process_directives('cleanup');

        return $markup;
    }

    /**
     * Render Name
     *
     * Creates the correct render name, from dynamic or static name and
     * will replace . with _ to make it a valid Twig variable.
     * @return string
     */
    public function render_name(): string {
        $name = $this->has_dynamic_name()
            ? $this->dynamic_name
            : $this->name;

        return str_replace( '.', '_', $name );
    }
}
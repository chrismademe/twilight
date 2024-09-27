<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

class AttributesDirective extends Directive {

    public string $name = 'attributes';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@attributes');
    }

    /**
     * Modify the markup before the element
     *
     * @param Component|HTMLElement $element
     */
    public function before( Component|HTMLElement $element ) {
        $key = sprintf( '%s_merged_attributes', str_replace( '-', '_', $element->name ) );

        $attributes = $element->get_attribute('@attributes')->value === true
            ? 'attributes'
            : $element->get_attribute('@attributes')->value;

        // If the element is an HTML element, we need to create a custom markup
        if ( $element instanceof HTMLElement ) {
            return $this->create_markup_for_html_element($element, $key, $attributes);
        }

        // If the element is a component, we can just set the attributes
        $element->set_attribute( ':attributes', $attributes );
        return;
    }

    public function tag( Component|HTMLElement $element ) {
        if ( $element instanceof Component ) {
            return;
        }

        $key = sprintf( '%s_merged_attributes', str_replace( '-', '_', $element->name ) );
        return sprintf( ' {{ %s }}', $key );
    }

    private function create_markup_for_html_element( HTMLElement $element, string $key, $attributes ): string {
        $markup = sprintf( '{%% set %s %%}', $key );
        $markup .= sprintf( '{%% if %s is iterable %%}', $attributes );
        $markup .= sprintf( '{%% for name, value in %s %%}', $attributes );
        $markup .= '{% if value is not null %}{{ name }}="{{ value }}"{% endif %} ';
        $markup .= '{% endfor %}';
        $markup .= '{% else %}';
        $markup .= sprintf( '{{ %s | raw }}', $attributes );
        $markup .= '{% endif %}';
        $markup .= sprintf( '{%% endset %%}%s', PHP_EOL );

        return $markup;
    }

}
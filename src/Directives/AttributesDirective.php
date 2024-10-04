<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

class AttributesDirective extends Directive {

    public string $name = 'attributes';
    public int $priority = 10;

    private string $markup;

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
        $attributes = $element->get_attribute('@attributes')->value === '__empty__'
            ? 'attributes'
            : $element->get_attribute('@attributes')->value;

        // If the element is an HTML element, we need to create a custom markup
        if ( $element instanceof HTMLElement ) {
            $this->markup = sprintf( '{{ make_element_attributes(%s) | raw }}', $attributes );
            return;
        }

        // If the element is a component, we can just set the attributes
        $element->set_attribute( ':attributes', $attributes );
        return;
    }

    /**
     * Modify the markup of the element
     *
     * @param Component|HTMLElement $element
     * @return string|void
     */
    public function tag( Component|HTMLElement $element ) {
        if ( $element instanceof HTMLElement && ! empty( $this->markup ) ) {
            return $this->markup;
        }
    }

}
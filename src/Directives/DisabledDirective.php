<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

class DisabledDirective extends Directive {

    public string $name = 'disabled';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@disabled');
    }

    /**
     * Modify the markup of the element tag
     *
     * @param string $markup
     * @param Component|HTMLElement $element
     */
    public function tag( Component|HTMLElement $element ): string {
        $attribute = $element->get_attribute('@disabled');

        /**
         * Components don't support markup, so pass along the
         * Twig expression in a :disabled attribute
         */
        if ( $element instanceof Component ) {
            $element->set_attribute( ':disabled', sprintf( '%s', $attribute->value ) );
            return '';
        }

        /**
         * For an HTML element, write the Twig expression to place on the
         * opening tag. The space here is in case there are other
         * attributes.
         */
        return sprintf( " {{ %s ? 'disabled' : '' }}", $attribute->value );
    }

}
<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

/**
 * This is a band aid until I can figure out how to properly implement
 * conditional attributes in the compiler. Because it's a directive,
 * it only allows for one per element.
 */
class ConditionalAttributeDirective extends Directive {

    public string $name = 'attr';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@attr');
    }

    /**
     * Modify the markup of the opening tag
     *
     * @param Component|HTMLElement $element
     */
    public function tag( Component|HTMLElement $element ) {
        [ $name, $value, $condition ] = explode( ',', $element->get_attribute('@attr')->value );

        $name = trim( $name );
        $value = trim( $value );
        $condition = trim( $condition );

        if ( $element instanceof Component ) {
            $element->set_attribute( $name, sprintf( '%s is not null ? %s : null', $condition, $value ) );
            return;
        }

        return sprintf(
            '{%% if %s is not null %%} {{ %s }}="{{ %s }}"{%% endif %%}',
            $condition,
            $name,
            $value
        );
    }

}
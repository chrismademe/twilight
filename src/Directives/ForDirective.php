<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

class ForDirective extends Directive {

    public string $name = 'for';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@for') || $element->has_attribute('@each');
    }

    /**
     * Modify the markup before the element
     *
     * @param Component|HTMLElement $element
     */
    public function before( Component|HTMLElement $element ): string {
        $condition = $element->has_attribute('@for')
            ? $element->get_attribute('@for')
            : $element->get_attribute('@each');
        return sprintf( '{%% for %s %%}', $condition->value );
    }

    /**
     * Modify the markup after the element
     *
     * @param Component|HTMLElement $element
     */
    public function after( Component|HTMLElement $element ): string {
        return '{% endfor %}';
    }

}
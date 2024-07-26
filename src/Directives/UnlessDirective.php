<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

class UnlessDirective extends Directive {

    public string $name = 'unless';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@unless');
    }

    /**
     * Modify the markup before the element
     *
     * @param string $markup
     * @param Component|HTMLElement $element
     */
    public function before( Component|HTMLElement $element ): string {
        $condition = $element->get_attribute('@unless');
        return sprintf( '{%% if not %s %%}', $condition->value );
    }

    /**
     * Modify the markup after the element
     *
     * @param string $markup
     * @param Component|HTMLElement $element
     */
    public function after( Component|HTMLElement $element ): string {
        return '{% endif %}';
    }

}
<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;
use Twilight\Nodes\Text;

class TextDirective extends Directive {

    public string $name = 'text';
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {
        return $element->has_attribute('@text');
    }

    /**
     * Modify the content of the element
     *
     * @param Component|HTMLElement $element
     */
    public function before( Component|HTMLElement $element ) {
        $content = $element->get_attribute('@text');
        $element->set_children([
            new Text(sprintf('{{ %s }}', $content->value))
        ]);
    }

}
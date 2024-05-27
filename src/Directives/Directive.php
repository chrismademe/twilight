<?php

namespace Twilight\Directives;

use Twilight\Nodes\Component;
use Twilight\Nodes\HTMLElement;

abstract class Directive {

    public string $name;
    public int $priority = 10;

    /**
     * Should Run
     *
     * Returns a boolean indicating whether the directive should run
     * @param Component|HTMLElement $element
     * @return bool
     */
    public function should_run( Component|HTMLElement $element ): bool {}

    /**
     * Modify the markup before the element
     *
     * @param Component|HTMLElement $element
     */
    public function before( Component|HTMLElement $element ) {}

    /**
     * Modify the markup after the element
     *
     * @param Component|HTMLElement $element
     */
    public function after( Component|HTMLElement $element ) {}

    /**
     * Cleanup
     *
     * Runs after all methods could have been run
     */
    public function cleanup( Component|HTMLElement $element ) {}

}
<?php

namespace Twilight\Nodes;

use Twilight\Directives;

trait HasDirectives {

    public Directives $directives;

    public function set_directives(Directives $directives): void {
        $this->directives = $directives;
    }

    public function has_directive(string $name): bool {
        return $this->directives->is_registered($name);
    }

    public function is_directive(string $name): bool {
        return str_starts_with($name, '@') && $this->has_directive(ltrim($name, '@'));
    }

    public function process_directives(string $method): string {
        if ( $this->directives->is_empty() ) return '';

        $markup = '';

        /**
         * During the after method, reverse the order so end tags are placed
         * in the correct order.
         */
        $directives = $method === 'after'
            ? array_reverse( $this->directives->all() )
            : $this->directives->all();

        foreach ( $directives as $name => $directive ) {
            if ( ! $directive->should_run($this) ) continue;
            if ( ! method_exists($directive, $method) ) continue;
            $markup .= $directive->$method($this);
        }

        return $markup;
    }

}
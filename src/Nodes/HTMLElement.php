<?php

namespace Twilight\Nodes;

class HTMLElement implements NodeInterface {
    use CanBeSelfClosing, HasHTMLAttributes, HasChildren, HasDirectives;

    public function __construct(
        public string $name
    ) {}

    public function render(): string {
        $markup = '';

        $markup .= $this->process_directives('before');

        $markup .= sprintf( '<%s', $this->name );

        if ( $this->has_attributes() ) {
            foreach ( $this->attributes as $attribute ) {
                if ( $this->is_directive($attribute->name) ) continue; // Skip directives
                $markup .= sprintf( ' %s', $attribute->render());
            }
        }

        $markup .= '>';

        // Self closing elements cannot have children, so we're done
        if ( $this->is_self_closing() ) {
            $markup .= $this->process_directives('after');
            return $markup;
        }

        $markup .= $this->process_directives('content');

        if ( $this->has_children() ) {
            foreach ( $this->get_children() as $child ) {
                $markup .= sprintf( '%1$s%2$s%1$s', PHP_EOL, $child->render() );
            }
        }

        $markup .= sprintf( '</%s>%s', $this->name, PHP_EOL );

        $markup .= $this->process_directives('after');

        return $markup;
    }
}
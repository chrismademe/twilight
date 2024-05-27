<?php

namespace Twilight\Nodes;

trait CanBeSelfClosing {

    public function is_self_closing(): bool {
        return in_array( $this->name, [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr',
        ]);
    }

}
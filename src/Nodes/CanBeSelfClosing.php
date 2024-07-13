<?php

namespace Twilight\Nodes;

trait CanBeSelfClosing {

    public function is_self_closing(): bool {
        return $this->is_self_closing;
    }

}
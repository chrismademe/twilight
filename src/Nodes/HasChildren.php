<?php

namespace Twilight\Nodes;

trait HasChildren {

    public array $children;

    public function set_children(array $children): void {
        $this->children = $children;
    }

    public function get_children(): array {
        return $this->children;
    }

    public function has_children(): bool {
        return ! empty( $this->children );
    }

}
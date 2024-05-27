<?php

namespace Twilight\Nodes;

class Text implements NodeInterface {
    public function __construct(
        public string $value,
    ) {}

    public function render(): string {
        return $this->value;
    }
}
<?php

namespace Twilight\Nodes;

class HTMLComment implements NodeInterface {
    public function __construct(
        public string $value,
    ) {}

    public function render(): string {
        return sprintf( '%s%s', $this->value, PHP_EOL );
    }
}
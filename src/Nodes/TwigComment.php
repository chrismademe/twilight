<?php

namespace Twilight\Nodes;

class TwigComment implements NodeInterface {
    public function __construct(
        public string $value,
    ) {}

    public function render(): string {
        return '';
    }
}
<?php

namespace Twilight\Nodes;

trait CanHaveDynamicName {

    public string $dynamic_name;

    public function has_dynamic_name(): bool {
        return ! empty( $this->dynamic_name );
    }

    public function set_dynamic_name( string $dynamic_name ): void {
        $this->dynamic_name = $dynamic_name;
    }

}
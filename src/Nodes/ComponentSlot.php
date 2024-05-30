<?php

namespace Twilight\Nodes;

class ComponentSlot {

    public function __construct( private string $name, private $value ) {}

    public function __get( string $key ) {
        return $this->$key;
    }

}
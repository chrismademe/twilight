<?php

namespace Twilight\Exception;

use Exception;
use Throwable;

class InvalidPropException extends Exception
{
    public function __construct( string $message = 'Invalid prop', $code = 0, Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
}
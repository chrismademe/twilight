<?php

namespace Twilight\Exception;

use Exception;
use Throwable;

class ReservedKeywordException extends Exception
{
    public function __construct( string $message = 'Reserved keyword', $code = 0, Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
}
# Modifying the Twig Environment

Twig offers a variety of options that you can pass to the Environment, to do this with Twilight, you may hook into the `twig:instance` event and modify the instance. Here's an example of adding a custom Twig function:

```php
<?php

use Twig\TwigFunction;
use function Twilight\on;

on( 'twig:instance', function( $twig ) {
    $twig->addFunction( new TwigFunction( 'do_a_thing', 'do_a_thing' ) );
} );

```
# Passing Data to Components

To pass data to components, you can use HTML attributes. Hard-coded values can be passed as simple HTML attributes, as you would on any other element. To pass Twig expressions or variables, you should prefix the attribute name with a `:` character:

```html
<Button variant="success" :href="page.url">
    Contact Us
</Button>
```

**Note** If you're an AlpineJS user, you should use `x-bind:` instead of the shorthand `:` syntax to ensure the attributes are included in the output.

## Conditional Attributes

To hide an attribute from output, you can pass a `null` value. To do this use the dynamic modifier `:` with a Twig expression:

```html
<button :type="type ?? null">Submit</button>
```

In the above example, if the `type` variable exists or has a truthy value, the attribute will appear in the output, otherwise it'll be removed.

## Modifying or Passing Additional Data to a Component

Twilight will look for a `component.php` in your component directory, if one exists it'll automatically be included. In this file, you can use the `on` function to hook into the component rendering and pass or modify component data.

For example, if you want to load a WordPress post based on an ID prop, here's how you'd do that.

```php
<?php

use function Twilight\on;

on( 'Button:render', function( $context ) {
    $context['post'] = get_post( $context['id'] );
    return $context;
} );

```
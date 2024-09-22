# Ignored Components

Ignored components allow you to specify that Twilight should treat a components markup as if it were HTML. For example, ACF Blocks uses an `<InnerBlocks />` component that renders the children of a block.

Twilight will ignore `<InnerBlocks />` by default, however you may pass additional components to ignore in the `compile` function.

```php
<?php

use function Twilight\compile;

compile(
    input: get_template_directory() . '/views',
    output: WP_CONTENT_DIR . '/.views',
    ignore: [ 'IgnoreThisComponent' ]
);
```

Even though the compiler will not transform these elements into component calls, you may still use attribute features, such as dynamic attributes and directives as they'll be treated like HTML elements.
# Caching Compiled Views

While Twilight is pretty fast, in production, you don't need to compile your views on every request. To stop this, simply pass a boolean value to the `if` argument of the `compile` function.

In WordPress, you can use the `wp_get_environment_type` function:

```php
<?php

use function Twilight\compile;

compile(
    input: get_template_directory() . '/views',
    output: WP_CONTENT_DIR . '/.views',
    if: wp_get_environment_type() === 'local'
);
```

In Laravel, you could use the `env()` helper:

```php
<?php

use function Twilight\compile;

compile(
    input: 'resources/views',
    output: 'storage/app/views',
    if: env('APP_ENV') === 'development'
);
```
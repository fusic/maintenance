# Maintenance Plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require fusic/maintenance
```

## Usage

```php
// src/Application.php
<?php

// Add use
use Maintenance\Middleware\MaintenanceMiddleware;

    public function middleware($middleware)
    {
        $middleware
            // Add Maintenance Plugin
            ->add(MaintenanceMiddleware::class)            
            
            ->add(ErrorHandlerMiddleware::class)
            ->add(AssetMiddleware::class)
            ->add(RoutingMiddleware::class);

        return $middleware;
    }
```

```html
// src/Template/Error/maintenance.ctp

<p>maintenance page. </p>
```

```
// tmp/maintenance

touch tmp/maintenance
```

## Config

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
### useXForwardedFor

If your system is behind a reverse proxy like load balancer that adds X-Forwarded-For header, set useXForwardedFor as true.

```php
// src/Application.php
use Maintenance\Middleware\MaintenanceMiddleware;

    public function middleware($middleware)
    {
        $middleware
            ->add(new MaintenanceMiddleware([
                'allowIp' => [
                    '127.0.0.1',
                ],
                'useXForwardedFor' => true,
            ]))
            ->add(ErrorHandlerMiddleware::class)
            ->add(AssetMiddleware::class)
            ->add(RoutingMiddleware::class);
        return $middleware;
    }
```

Bybzmt/Router
==============
A simple and fast PHP Router

Features
-----------
* Static Route Patterns
* Dynamic Route Patterns
* Supports `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`, `PATCH` and `HEAD` request methods
* Supports `X-HTTP-Method-Override` header
* Allowance of `:Class:Method` calls
* Custom 404 handling
* Reverse Router

Prerequisites/Requirements
-------------
* PHP 5.3 or greater
* URL Rewriting

Installation
--------
Installation is possible using Composer
```
composer require bybzmt/router
```


Usage
--------
```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bybzmt\Router\Router();

// Define routes
// ...

// Run it!
$router->run();

```

Routing
----------
using $router->handle(method(s), pattern, function):
```php
$router->handle('GET|POST', 'pattern', function() { … });
//or
$router->handle('GET|POST', 'pattern', ':Class:Method');
```

Shorthands for single request methods are provided:
```php
$router->get('pattern', function() { /* ... */ });
$router->post('pattern', function() { /* ... */ });
$router->put('pattern', function() { /* ... */ });
$router->delete('pattern', function() { /* ... */ });
$router->options('pattern', function() { /* ... */ });
$router->patch('pattern', function() { /* ... */ });
//all methods
$router->all('pattern', function() { /* ... */ });
```

Route Patterns
------
* _Static Route Patterns_ are essentially URIs, e.g. `/about`
* _Dynamic Route Patterns_ are Perl-compatible regular expressions (PCRE) that resemble URIs, e.g. `/movies/(\d+)`

The subpatterns defined in Dynamic Route Patterns are converted to parameters which are passed into the route handling function. Prerequisite is that these subpatterns need to be defined as parenthesized subpatterns, which means that they should be wrapped between parens:
```php
// Bad
$router->get('/hello/\w+', function($name) {
    echo 'Hello ' . htmlentities($name);
});

// Good
$router->get('/hello/(\w+)', function($name) {
    echo 'Hello ' . htmlentities($name);
});
```

:Class:Method calls
----------------------------
We can route to the class action like so:
```php
$router->get('/(\d+)', ':User:showProfile:id');
```

Custom 404
------------------------
Override the default 404 handler using $router->set404(function);
```php
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    // ... do something special here
});
```

Reverse Router
---------------------------
```php
$router = new \Bybzmt\Router\Router();
$router->get('/news/(\d+)', ':news:show:id');

$tool = new \Bybzmt\Router\Tool($router->getRoutes());
$data = $tool->convertReverse();

$reverse = new \Bybzmt\Router\Reverse($data);

//echo /news/1234
echo $reverse->buildUri('news:show', ['id'=>1234]);
```

Cache Data
-------------------------
Storage Data (file1.php)
```php
$router = new \Bybzmt\Router\Router();
$router->get('/a1', ':example:test');
$router->get('/a2/(\d+)', ':example:test:k1');
$router->get('/a3/(\d+)/(\d+)', ':example:test:k1:k2');

$tool = new \Bybzmt\Router\Tool($router->getRoutes());

//Cache Router Data
$code = $tool->exportRoutes();
file_put_contents('routes_cache.php', $code);

//Cache Reverse Router Data
$code = $tool->exportReverse();
file_put_contents('reverse_cache.php', $code);
```

Recovery Data (file2.php)
```php
//Recovery Router Data
$router = new \Bybzmt\Router\Router(require 'routes_cache.php');

//Recovery Reverse Router Data
$reverse = new \Bybzmt\Router\Reverse(require 'reverse_cache.php');
```

Thanks
-----------------
* [bramus/router](https://github.com/bramus/router)
* [bephp/router](https://github.com/bephp/router)

Licence
-------------------
Apache-2.0



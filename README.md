BaseKit Site Construction Kit
=============================

The [BaseKit] Site Construction Kit (SiCK) is a set of PHP classes that let you 
build BaseKit sites.

The purpose of the SiCK is to take care of the implementation details of building
sites, pages and widgets, leaving you with a simple syntax.

Installation
------------

The recommended way of including this package in your project is by using
Composer. Add it to the `require` section of your project's `composer.json`.

    "basekit/site-construction-kit": "dev-master"

Usage
-----

Here's a quick example of constructing a site, page and adding some widgets.

```php
use BaseKit\Builder\SiteBuilder;

$site = new SiteBuilder;
$site->setBrandRef(123);
$site->setAccountHolderRef(456);
$site->mapDomain('example.com');

$page = $site->createPage('home', 'Home Page');

$page->addText('<h1>Hello World</h1>');
$page->addImage('http://placehold.it/200x200');
```

You can then instantiate a Writer class that is responsible for actually building
the site that you have constructed.

You will typically use the ApiWriter class. You provide this with a BaseKit API
Client object and it will use this to build the site.

```php
use BaseKit\Api\Client;
use BaseKit\Builder\Writer\ApiWriter;

$apiClient = Client::factory(array(
    'base_url' => 'http://rest.basekit.com',
    'consumer_key' => 'YOUR OAUTH CONSUMER KEY',
    'consumer_secret' => 'YOUR OAUTH CONSUMER SECRET',
    'token' => 'YOUR OAUTH ACCESS TOKEN',
    'token_secret' => 'YOUR OAUTH ACCESS SECRET'
));

$writer = new ApiWriter;
$writer->setApiClient($client);

$writer->writeSite($site);
```

Contributing
------------

This project adheres to the [PSR-2] coding style guide. Checking your
contribution's correctness is easy.

```bash
$ make lint
```

There's a very small unit test suite, using [PHPUnit]. Making sure you haven't
broken any tests is easy too.

```bash
$ make test
```

License
-------

This software is released under the [MIT License].

[BaseKit]: http://basekit.com/
[PHPUnit]: http://phpunit.de/
[PSR-2]: http://www.php-fig.org/psr/psr-2/
[MIT License]: http://www.opensource.org/licenses/MIT

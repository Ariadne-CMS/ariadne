ARC: Ariadne Component Library 
========================= 

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-cache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-cache/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arc/cache/v/stable.svg)](https://packagist.org/packages/arc/cache)
[![Total Downloads](https://poser.pugx.org/arc/cache/downloads.svg)](https://packagist.org/packages/arc/cache)
[![Latest Unstable Version](https://poser.pugx.org/arc/cache/v/unstable.svg)](https://packagist.org/packages/arc/cache)
[![License](https://poser.pugx.org/arc/cache/license.svg)](https://packagist.org/packages/arc/cache)

A flexible component library for PHP 
------------------------------------ 

The Ariadne Component Library is a spinoff from the Ariadne Web 
Application Framework and Content Management System 
[ http://www.ariadne-cms.org/ ]

arc/cache contains
------------------
- cache: a generic cache class and caching proxy object. See [docs/cache.md](docs/cache.md) for more information.

Example code:

```php
    $cachedObject = \arc\cache::proxy( new heavyClass() );
    $result = $cachedObject->aHeavyCall();
```

Or a caching http client, using arc-web:

```php
    $cachedHTTPClient = \arc\cache::proxy( \arc\http::client(), function($params) {
        return ( \arc\http\headers::parseCacheTime( $params['target']->responseHeaders ) );
    });
```

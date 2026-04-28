ARC: Ariadne Component Library 
========================= 

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-config/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arc/config/v/stable.svg)](https://packagist.org/packages/arc/config)
[![Total Downloads](https://poser.pugx.org/arc/config/downloads.svg)](https://packagist.org/packages/arc/config)
[![Latest Unstable Version](https://poser.pugx.org/arc/config/v/unstable.svg)](https://packagist.org/packages/arc/config)
[![License](https://poser.pugx.org/arc/config/license.svg)](https://packagist.org/packages/arc/config)

A flexible component library for PHP
------------------------------------ 

The Ariadne Component Library is a spinoff from the Ariadne Web 
Application Framework and Content Management System 
[ http://www.ariadne-cms.org/ ]

arc/config contains
------------------
- [config](docs/config.md): a generic config class that allows you to override configuration properties by path.

Code example:

```php
	\arc\config::configure('color', 'blue');
	$color = \arc\config::cd('/parent/child/')->acquire('color');
	// => 'blue'
```

And:

```php
	\arc\config::cd('/parent/')->configure('color', 'red');
	$color = \arc\config::cd('/parent/child/')->acquire('color');
	// => 'red'
```

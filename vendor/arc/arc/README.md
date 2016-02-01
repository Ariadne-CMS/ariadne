ARC: Ariadne Component Library 
========================= 

[![Latest Stable Version](https://poser.pugx.org/arc/arc/v/stable.svg)](https://packagist.org/packages/arc/arc)
[![Total Downloads](https://poser.pugx.org/arc/arc/downloads.svg)](https://packagist.org/packages/arc/arc)
[![Latest Unstable Version](https://poser.pugx.org/arc/arc/v/unstable.svg)](https://packagist.org/packages/arc/arc)
[![License](https://poser.pugx.org/arc/arc/license.svg)](https://packagist.org/packages/arc/arc)

A flexible component library for PHP 5.4+ 
----------------------------------------- 

ARC is a set of components, build to be as simple as possible. Each component does just one thing and has a small and 
simple API to learn. ARC uses static factory methods to simplify the API while using Dependency Injection. ARC is not a
framework. It can be used in combination with any framework or indeed without.

The Ariadne Component Library is a spinoff from the Ariadne Web Application Platform and Content Management System 
[http://www.ariadne-cms.org/](http://www.ariadne-cms.org/). Many of the concepts used in ARC have their origin in Ariadne
and have been in use since 2000. 

A unique feature in most components is that they are designed to work in and with a tree structure. URL's
are based on the concept of paths in a filesystem. This same path concept and the underlying filesystem-like tree is
used in most ARC components. 

Installation
------------

Via [Composer](https://getcomposer.org/doc/00-intro.md):

    $ composer require arc/arc

or start a new project with arc

    $ composer create-project arc/arc {$path}
    
This will download and install all arc components. 

But you don't need to do this, you can just download the components
you really need instead. Below is a list of components and what they do:

Components
----------
- [`arc/base`](https://github.com/Ariadne-CMS/arc-base/): Common datatypes and functionality shared by all ARC components.
Is installed automatically if needed.
- [`arc/cache`](https://github.com/Ariadne-CMS/arc-cache/): Cache functionality and a caching proxy class.
- [`arc/events`](https://github.com/Ariadne-CMS/arc-events/): Fire events and listen for them in a tree structure, 
modelled after the browsers DOM events.
- [`arc/config`](https://github.com/Ariadne-CMS/arc-config/): Configuration management, storing and retrieving 
configuration settings in a tree structure.
- [`arc/web`](https://github.com/Ariadne-CMS/arc-web/): Simple and correct manipulation of URL's, HTTP Headers 
and a HTTP client. Also includes a simple intrustion detection component to prevent cross site scripting attacks.
- [`arc/xml`](https://github.com/Ariadne-CMS/arc-xml/): Parsing and writing XML made simple.
- [`arc/html`](https://github.com/Ariadne-CMS/arc-html/): Parsing and writing HTML also made simple.
- [`arc/grants`](https://github.com/Ariadne-CMS/arc-grants/): Access control management in a tree structure, like a 
filesystem.
- [`arc/prototype`](https://github.com/Ariadne-CMS/arc-prototype/): Experimental prototypical inheritance, like javascript.

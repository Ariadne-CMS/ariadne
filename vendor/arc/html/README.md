ARC: Ariadne Component Library 
==============================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-html/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-html/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arc/html/v/stable.svg)](https://packagist.org/packages/arc/html)
[![Total Downloads](https://poser.pugx.org/arc/xml/downloads.svg)](https://packagist.org/packages/arc/html)
[![Latest Unstable Version](https://poser.pugx.org/arc/html/v/unstable.svg)](https://packagist.org/packages/arc/html)
[![License](https://poser.pugx.org/arc/html/license.svg)](https://packagist.org/packages/arc/html)

arc/html
========

This component provides a unified html parser and writer. The writer allows for readable and correct html in code, not using templates. The parser is a wrapper around both DOMDocument and SimpleXML. 

The parser and writer also work on fragments of HTML. The parser also makes sure that the output is identical to the input.
When converting a node to a string, \arc\html will return the full html string, including tags. If you don't want that, you can always access the 'nodeValue' property to get the original SimpleXMLElement.

Finally the parser also adds the ability to use basic CSS selectors to find elements in the HTML.

```php5
<?php
	use \arc\html as h;
	$htmlString = h::doctype()
	 .h::html(
	 	h::head(
	 		h::title('Example site')
	 	),
	 	h::body(
	 		['class' => 'homepage'],
	 		h::h1('An example site')
	 	)
	 );
```

```php5
	$html = \arc\html::parse($htmlString);
	$title = $html->head->title->nodeValue; // SimpleXMLElement 'Example site'
	$titleTag = $html->head->title; // <title>Example site</title>
```

CSS selectors
-------------

```php5
	$title = current($html->find('title'));
```

The find() method always returns an array, which may be empty. By using current() you get the first element found, or null if nothing was found.

The following CSS selectors are supported:

- `tag1 tag2`<br>
  This matches `tag2` which is a descendant of `tag1`.
- `tag1 > tag2`<br>
  This matches `tag2` which is a direct child of `tag1`.
- `tag:first-child`<br>
  This matches `tag` only if its the first child.
- `tag1 + tag2`<br>
  This matches `tag2` only if its immediately preceded by `tag1`.
- `tag1 ~ tag2`<br>
  This matches `tag2` only if it has a previous sibling tag1.
- `tag[attr]`<br>
  This matches `tag` if it has the attribute `attr`.
- `tag[attr="foo"]`<br>
  This matches `tag` if it has the attribute `attr` with the value `foo` in its value list.
- `tag#id`<br>
  This matches any `tag` with id `id`.
- `#id`<br>
  This matches any element with id `id`.
- `tag.class-name`<br>
  Matches any `tag` with a class `class-name`.
- `.class-name`<br>
  Matches any element with a class `class-name`.  

SimpleXML
---------

The parsed HTML behaves almost identical to a SimpleXMLElement, with the exceptions noted above. So you can access attributes just like SimpleXMLElement allows:

```php5
	$class = $html->body['class'];
	$class = $html->body->attributes('version');
```

You can walk through the node tree:

```php5
	$title = $html->head->title;
```

Any method or property available in SimpleXMLElement is included in \arc\html parsed data.


DOMElement
----------

In addition to SimpleXMLElement methods, you can also call any method and most properties available in DOMElement.

```php5
	$class = $html->body->getAttributes('class');
	$title = current($html->getElementsByTagName('title'));
```

Parsing fragments
-----------------

The arc\html parser also accepts partial HTML content. It doesn't require a single root element. 

```php5
    $htmlString = <<< EOF
<li>
	<a href="anitem/">An item</a>
</li>
<li>
	<a href="anotheritem/">Another item</a>
</li>
EOF;
	$html = \arc\html::parse($htmlString);
	$links = $html->find('a');
```

And when you convert the html back to a string, it will still be a partial HTML fragment.

If you parse a single HTML tag, other than `<html>`, you must still reference this element to access it:

```php5
    $htmlString = <<< EOF
<ul>
	<li>
		<a href="anitem/">An item</a>
	</li>
	<li>
		<a href="anotheritem/">Another item</a>
	</li>
</ul>
EOF;
	$html = \arc\html::parse($htmlString);
	$ul = $html->ul;
```


Why use this instead of DOMDocument or SimpleXML?
-------------------------------------------------

arc\html::parse has the following differences:

  - When converted to string, it returns the original HTML, without additions you didn't make.
  - You can use it with partial HTML fragments.
  - No need to remember calling importNode() before appendChild() or insertBefore()
  - No need to switch between SimpleXML and DOMDocument, because you need that one method only available in the other API.
  - When returning a list of elements, you always get a simple Array, not a magic NodeList.

In addition arc\html doubles as a simple way to generate valid and indented HTML, with readable and self-validating code.

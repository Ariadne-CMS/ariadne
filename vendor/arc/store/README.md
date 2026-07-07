# arc\store: fast schema-free JSON store

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-store/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-store/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-store/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-store/)
[![Latest Stable Version](https://poser.pugx.org/arc/store/v/stable.svg)](https://packagist.org/packages/arc/store)
[![Total Downloads](https://poser.pugx.org/arc/store/downloads.svg)](https://packagist.org/packages/arc/store)
[![Latest Unstable Version](https://poser.pugx.org/arc/store/v/unstable.svg)](https://packagist.org/packages/arc/store)
[![License](https://poser.pugx.org/arc/store/license.svg)](https://packagist.org/packages/arc/store)

arc\store is part of [ARC - a component library](http://www.github.com/Ariadne-CMS/arc-arc/). 

ARC is a spinoff from the Ariadne Web Application Platform and Content Management System
[http://www.ariadne-cms.org/](http://www.ariadne-cms.org/).

## Installation

You can install the full set of ARC components using composer:

    composer require arc/arc

Or you can start a new project with arc/arc like this:

    composer create-project arc/arc {$path}

Or just use this package:

    composer require arc/store

## Usage

Example DSN for PostgreSQL:
```php
	$dsn = 'pgsql:host=localhost;dbname=arcstore;user=arcstore;password=arcstore';
```

Example DSN for MySQL:
```php
	$dsn = 'mysql:host=localhost;dbname=arcstore;user=arcstore;password=arcstore';
```

```php
    $store = \arc\store::connect($dsn);
    $store->initialize();
    if ($store->save(\arc\prototype::create(["foo" => "bar"]), "/foo/")) {
        $objects = $store->ls('/');
        var_dump($objects);
    }
```

This will show an array with one object, with parent '/', name 'foo', and a single property 'foo' => 'bar'.


## What is ARC\Store?

ARC\Store is a minimal implementation of the structured object store implemented in Ariadne-CMS. It stores free form object data in a tree structure, similar to a filesystem. It provides seperate query and save and delete methods. The query has its own format and parser. The data is stored in PostgreSQL using JSONB data blobs, which are fully indexed.

This solution gives you flexible and fast storage of any kind of data, while keeping many advantages of using a proven technology like PostgreSQL. Although this implementation doesn't have it, it would be easy to add transactions with commit/rollback to gain atomic updates, even for batch operations.

Because of its tree structure, ARC\Store integrates well with other ARC Components, like ARC\Grants and ARC\Config.

## methods

### \arc\store::connect
	(\arc\store\Store) \arc\store::connect( (string) $dsn, (callable) $resultHandler=null)

This method creates a new PSQLStore instance and connects it to a PostgreSQL database. Optionally you can pass your own resultHandler function. The PSQLStore class contains two static functions predefined for this:
- \arc\store\ResultHandlers::getDBHandler
- \arc\store\ResultHandlers::getDBGeneratorHandler
These functions take 1 argument, the database connection, and return a result handler function. The result handler is called with a compiled SQL query where clause and arguments and must execute this and return the results.

### \arc\store::disconnect
	(void) \arc\store::disconnect()

Removes the last store connection from the context stack (\arc\context).

### \arc\store::cd
	(\arc\store\PSQLStore) \arc\store::cd($path)

Returns a new store istance, with its default path set to $path. This call will always succeed, even if $path doesn't exist in the object store. It does not update the path of the store instance in the context stack (\arc\context). To do that, you must push the new store onto the context stack:

```php
	\arc\context::push([
		'arcStore' => \arc\store::cd('/foo/')
	]);
```

### \arc\store::find
	(mixed) \arc\store::find((string) $query, (string) $path)

Compiles the query to SQL, calls the resultHandler with it and returs the results. The query syntax is read only, it can only read data, never update or delete it.
You can also call this method on a store istance, like this:

```php
	$store = \arc\context::cd('/');
	$objects = $store->find("foo='bar'");
```

The query format supports the following operators:

- `<` less than
- `>` more than
- `=` equals
- `<=` less than or equal
- `>=` more than or equal
- `<>`, `!=` not equal
- `~=` similar to, supports `%` and `?` wildcards
- `!~` not similar to, supports `%` and `?` wildcards
- `?` object contains the key (property)

You can combine multiple query parts using `and` and `or`. You can use parenthesis to group them. And you can negate a part by prefixing it with `not`. Strings must be enclosed in single quotes. A single quote inside the string should be escaped with a `\`.

You can query any part of the object, but there are a few meta data properties you can search for:
- `nodes.path` matches the full path of the object in the tree
- `nodes.parent` matches the full path of the objects parent in the tree
- `nodes.mtime` matches the datetime when the object was last changed
- `nodes.ctime` matches the datetime when the object was created

Example queries:

```php
    $results = $store->find("nodes.path ~= '/foo/%'"); 
    $results = $store->find("foo.bar>3"); 
    $results = $store->find("foo.bar>3 and foo.bar<6");
    $results = $store->find("foo.bar<2 or foo.bar>8");
    $results = $store->find("type='order' and ( total<10 or total>1000 )");
```

### \arc\store::parents
	(mixed) \arc\store::parents((string) $path)

Returns a list of parent objects, starting with the root and ending with the direct parent.

### \arc\store::ls
	(mixed) \arc\store::ls((string) $path)

Returns a list of direct children of the given path.

### \arc\store::get
	(object) \arc\store::get((string) $path)

Returns the object with the give path, or null.

### \arc\store::exists
	(bool) \arc\store::exists((string) $path)

Returns true if an object with the given path exists.

### \arc\store\Store->save
	(bool) $store->save( (object) $data, (string) $path = '')

Saves the object data at the given path. Returns true on success or false on failure.

### \arc\store\Store->delete
	(bool) $store->delete((string) $path = '')

Deletes the object with the given path and all its children. It will never remove the root object. If you don't pass an argument, it will use the current path set in the store instance. Returns true on success or false on failure.

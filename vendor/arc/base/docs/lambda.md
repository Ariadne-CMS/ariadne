arc/lambda
==========

This component makes it easy create ad-hoc objects, similar to lambda functions or closures. These objects also follow a prototypical inheritance scheme similar to smalltalk and javascript.

Simple example:

```php5
<?php
    $page = \arc\lambda::prototype( [
        'title' => 'A page',
        'content' => '<p>Some content</p>',
        'view' => function($args) {
            return '<!doctype html><html><head>' . $this->head($args) 
                 . '</head><body>' . $this->body($args) . '</body></html>';
        },
        'head' => function($args) {
            return '<title>' . $this->title . '</title>';
        },
        'body' => function($args) {
            return '<h1>' . $this->title . '</h1>' . $this->content;
        }
    ] );

    $menuPage = $page->extend( [
        'body' => function($args) {
            return $this->menu($args) . $this->prototype->body($args);
        },
        'menu' => function($args) {
            $result = '';
            if ( is_array($args['menu']) ) {
                foreach( $args['menu'] as $index => $title ) {
                    $result .= $this->menuItem($index, $title);
                }
            }
            if ( $result ) {
                return \arc\html::ul( ['class' => 'news'], $result );
            }
        },
        'menuItem' => function($index, $title) {
            return \arc\html::li( [], \arc\html::a( [ 'href' => $index ], $title ) );
        }
    ] );
```
Lambda prototype as a dependency injection container:

```php5
<?php
    $di = \arc\lambda::prototype([
         'dsn'      => 'mysql:dbname=testdb;host=127.0.0.1';
         'user'     => 'dbuser',
         'password' => 'dbpassword',
         'database' => \arc\lambda::singleton( function() {
             // this generates a single PDO object once and then returns it for each subsequent call
             return new PDO( $this->dsn, $this->user, $this->password );
         } ),
         'session'  => function() {
             // this returns a new mySession object for each call
             return new mySession();
         }
    ] );

    $diCookieSession = $di->extend( [ 
         'session'  => function() {
             return new myCookieSession();
         }
    ] );
```
\arc\lambda::prototype
----------------------
    (object) \arc\lambda::prototype( (array) $properties )

Returns a new \arc\lambda\Prototype object with the given properties. The properties array may contain closures, these will be available as methods on the new Prototype object.

\arc\lambda::singleton
----------------------
    (function) \arc\lambda::singleton( (callable) $f )

Returns a function that will only be run once. After the first run it will then return the value that run returned, unless that value is null. This makes it possible to create lazy loading functions that only run when used. You can also create shared objects in a dependency injection container.

This method doesn't guarantee that the given function is never run more than once - unless you only ever call it indirectly through the resulting singleton function.

\arc\lambda::curry
------------------
    (function) \arc\lambda::curry( (callable) $f, (array) $curriedArgs )

This method returns a copy of the given function $f from which the arguments supplied as $curriedArgs are removed. When called it will suplly the $curriedArgs in addition to the leftover arguments.

```php5
<?php
    $myApi = \arc\lambda::prototype( [
       'htmlentities' => \arc\lambda::curry( 'htmlentities', [ 1 => ENT_HTML5|ENT_NOQUOTES, 3 => false ] )
    ] );
```
The above example will result in an object with a htmlentities method that has two arguments, the string to encode and an optional encoding argument. The curried arguments will be mixed in with the given arguments, based on their key in the $curriedArgs.

```php5
<?php
    echo $myApi->htmlentities( 'Encode < this&tm; >', 'ISO-8859-1' );
```
This is the same as:

```php5
<?php
    echo htmlentities( 'Encode < this&tm; >', ENT_HTML5|ENT_NOQUOTES, 'ISO-8859-1', false );
```

\arc\lambda::pepper
-------------------
    (function) \arc\lambda::pepper( (callable) $callable, (array) $namedArgs=null )

This is an experimental method to convert a normal function or method into a function that accepts an array with named arguments. It uses Reflection to gather information about the given function or method if you don't pass a $namedArgs array.

The format for $namedArgs is [ 'argumentName' => 'defaultValue' ]. The order in $namedArgs is the order in which arguments will be supplied to the original method or function.
    
Given a method that has a large number of arguments, optional or not, pepper allows you to generate a method that is more easily called:

```php5
<?php
    function complexQuery( $query, $database, $user, $password ) { ... }

    $myApi = \arc\lambda::prototype( [
        'query' => \arc\lambda::pepper( 'complexQuery', [
            'query' => null,
            'database' => $this->database,
            'user'     => $this->dbuser,
            'password' => $this->dbpassword
        ] )
    ] );
```
And now you can call the complexQuery function like this:

```php5
<?php
    $myApi->query([ 'query' => 'select * from aTable', 'database' => 'alternateDatabase' ]);
```
\arc\lambda\Prototype::extend
-----------------------------
    (object) \arc\lambda\Prototype::extend( (array) $properties )

This returns a new Prototype object with the given properties, just like \arc\lambda::prototype(). But in addition the new object has a prototype property linking it to the original object from which it was extended.
Any methods or properties on the original object will also be accessible in the new object through its prototype chain.

You can check an objects prototype by getting the prototype property of a \arc\lambda\Prototype object. You cannot change this property - it is readonly. You can only set the prototype property by using the extend method.

\arc\lambda\Prototype::hasOwnProperty
-------------------------------------
    (bool) \arc\lambda\Prototype::hasOwnProperty( (string) $propertyName )

Returns true if the requested property is available on the current Prototype object itself without checking its prototype chain.

\arc\lambda\Prototype::hasPrototype
-----------------------------------
    (bool) \arc\lambda\Prototype::hasPrototype( (string) $prototypeObject )

Returns true if the given object is part of the prototype chain of the current Prototype object.
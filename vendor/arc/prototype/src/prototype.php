<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc;

/**
 * Methods to create, extend and observe prototype objects in PHP. Also adds a memoize function,
 * which is useful when using a prototype object as a Dependency Injection container.
 * @package arc
 */
final class prototype 
{

    /**
     * @var \SplObjectStorage contains a list of frozen objects and the observer
     */
    private static $frozen = null;

    /**
     * @var \SplObjectStorage contains a list of frozen objects and the observer
     */
    private static $sealed = null;

    /**
     * @var \SplObjectStorage contains a list of objects made unextensible and the observer
     */
    private static $notExtensible = null;

    /**
     * @var \SplObjectStorage contains a list of all 'child' instances for each prototype
     */
    private static $instances = null;

    /**
     * @var \SplObjectStorage contains a list of all observers for each prototype
     */
    private static $observers = null;

    /**
     * Returns a new \arc\prototype\Object object with the given properties. The 
     * properties array may contain closures, these will be available as methods on 
     * the new Prototype object.
     * @param array $properties List of properties and methods
     * @return \arc\prototype\Object
     */
    public static function create($properties) 
    {
        return new prototype\Object($properties);
    }

    /**
     * Returns a new \arc\prototype\Object object with the given object as its
     * prototype and the given properties and methods set.
     * @param \arc\prototype\Object $prototype The prototype for this object
     * @param array $properties List of properties and methods
     * @return \arc\prototype\Object
     */
    public static function extend($prototype, $properties) 
    {
        if ( self::isExtensible($prototype) ) {
            if (!isset(self::$instances)) {
                self::$instances = new \SplObjectStorage();
            };
            if (!isset(self::$instances[$prototype])) {
                self::$instances[$prototype] = [];
            }
            $properties['prototype'] = $prototype;
            $instance = new prototype\Object($properties);
            $list = self::$instances[$prototype];
            array_push($list,$instance);
            self::$instances[$prototype] = $list;
            return $instance;
        } else {
            return null;
        }
    }

    /**
     * Helper method to remove cache information when a prototype is no longer needed.
     * @param \arc\prototype\Object $obj The object to be removed
     */
    public static function _destroy($obj) 
    {
        unset(self::$notExtensible[$obj]);
        unset(self::$sealed[$obj]);
        unset(self::$frozen[$obj]);
        unset(self::$observers[$obj]);
        if ( isset($obj->prototype) ) {
            unset(self::$instances[$obj->prototype][$obj]);
        }
    }

    /**
     * Returns a new \arc\prototype\Object with the given prototype set. In addition 
     * all properties on the extra objects passed to this method will be copied to the 
     * new Prototype object. For any property that is set on multiple objects, the value 
     * of the property in the later object overwrites values from other objects.
     * @param \arc\prototype\Object $prototype the prototype for the new object
     * @param \arc\prototype\Object ...$object the objects whose properties will be assigned
     */
    public static function assign($prototype) 
    {
        $objects = func_get_args();
        array_shift($objects);
        $properties = [];
        foreach ($objects as $obj) {
            $properties = $obj->properties + $properties;
        }
        return self::extend($prototype, $properties);
    }

    /**
     * This makes changes to the given Prototype object impossible. 
     * The object becomes immutable. Any attempt to change the object will silently fail.
     * @param \arc\prototype\Object $prototype the object to freeze
     */
    public static function freeze($prototype) 
    {
        if (!isset(self::$frozen)) {
            self::$frozen = new \SplObjectStorage();
        }
        self::seal($prototype);
        self::$frozen[$prototype] = true;
    }

    /**
     * This prevents reconfiguring an object or adding new properties.
     * @param \arc\prototype\Object $prototype the object to freeze
     */
    public static function seal($prototype) 
    {
        if (!isset(self::$sealed)) {
            self::$sealed = new \SplObjectStorage();
        }
        self::preventExtensions($prototype);
        self::$sealed[$prototype] = true;
    }

    /**
     * Returns a list of keys of all the properties in the given prototype
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function keys($prototype) 
    {
        $entries = static::entries($prototype);
        return array_keys($entries);
    }

    /**
     * Returns an array with key:value pairs for all properties in the given prototype
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function entries($prototype) 
    {
        return $prototype->properties;
    }

    /**
     * Returns a list of all the property values in the given prototype
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function values($prototype) 
    {
        $entries = static::entries($prototype);
        return array_values($entries);
    }

    /**
     * Returns true if the the property name is available in this prototype
     * @param \arc\prototype\Object $prototype
     * @param string $property
     * @return bool
     */
    public static function hasProperty($prototype, $property) 
    {
        $entries = static::entries($prototype);
        return array_key_exists($property, $entries);
    }

    /**
     * Returns a list of all the property names defined in this prototype instance
     * without traversing its prototypes.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function ownKeys($prototype) 
    {
        $entries = static::ownEntries($prototype);
        return array_keys($entries);
    }

    /**
     * Returns an array with key:value pairs for all properties in this prototype
     * instance wihtout traversing its prototypes.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function ownEntries($prototype) 
    {
        return \arc\_getOwnEntries($prototype);
    }

    /**
     * Returns a list of all the property values in the given prototype
     * instance wihtout traversing its prototypes.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function ownValues($prototype) 
    {
        $entries = static::ownEntries($prototype);
        return array_values($entries);
    }

    /**
     * Returns true if the the property name is available in this prototype
     * instance wihtout traversing its prototypes.
     * @param \arc\prototype\Object $prototype
     * @param string $property
     * @return bool
     */
    public static function hasOwnProperty($prototype, $property) 
    {
        $entries = static::ownEntries($prototype);
        return array_key_exists($property, $entries);
    }

    /**
     * Returns true if the given prototype is made immutable by freeze()
     * @param \arc\prototype\Object $prototype
     * @return bool
     */
    public static function isFrozen($prototype) 
    {
        return isset(self::$frozen[$prototype]);
    }

    /**
     * Returns true if the given prototype is sealed by seal()
     * @param \arc\prototype\Object $prototype
     * @return bool
     */
    public static function isSealed($prototype) 
    {
        return isset(self::$sealed[$prototype]);
    }

    /**
     * Returns true if the given prototype is made not Extensible
     * @param \arc\prototype\Object $prototype
     * @return bool
     */
    public static function isExtensible($prototype) 
    {
        return !isset(self::$notExtensible[$prototype]);
    }

    /**
     * This calls the $callback function each time a property of $prototype is 
     * changed or unset. The callback is called with the prototype object, the 
     * name of the property and the new value (null if unset).
     * If the closure returns false exactly (no other 'falsy' values will work), 
     * the change will be cancelled
     * @param \arc\prototype\Object $prototype
     * @param \Closure $callback
     */
    public static function observe($prototype, $callback, $acceptList=null) 
    {
        if ( !isset(self::$observers) ) {
            self::$observers = new \SplObjectStorage();
        }
        if ( !isset(self::$observers[$prototype]) ) {
            self::$observers[$prototype] = [];
        }
        if ( !isset($acceptList) ) {
            $acceptList = ['add','update','delete','reconfigure'];
        }
        $observers = self::$observers[$prototype];
        foreach( $acceptList as $acceptType ) {
            if ( !isset($observers[$acceptType]) ) {
                $observers[$acceptType] = new \SplObjectStorage();
            }
            $observers[$acceptType][$callback] = true;
        }
        self::$observers[$prototype] = $observers;
    }

    /**
     * Returns a list of observers for the given prototype.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function getObservers($prototype) 
    {
        return (isset(self::$observers[$prototype]) ? self::$observers[$prototype] : [] );
    }

    /**
     * Makes an object no longer extensible.
     * @param \arc\prototype\Object $prototype
     */
    public static function preventExtensions($prototype) 
    {
        if ( !isset(self::$notExtensible) ) {
            self::$notExtensible = new \SplObjectStorage();
        }
        self::$notExtensible[$prototype] = true;
    }

    /**
     * Removes an observer callback for the given prototype.
     * @param \arc\prototype\Prototyp $prototype
     * @param \Closure $callback the observer callback to be removed
     */
    public static function unobserve($prototype, $callback) 
    {
        if ( isset(self::$observers) && isset(self::$observers[$prototype]) ) {
            unset(self::$observers[$prototype][$callback]);
        }
    }

    /**
     * Returns true if the object as the given prototype somewhere in its
     * prototype chain, including itself.
     */
    public static function hasPrototype($obj, $prototype) 
    {
        if (!$obj->prototype) {
            return false;
        }
        if ($obj === $prototype || $obj->prototype === $prototype) {
            return true;
        }

        return static::hasPrototype($obj->prototype, $prototype );
    }

    /**
     * Returns a list of prototype objects that have this prototype object
     * in their prototype chain.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function getDescendants($prototype) 
    {
        $instances = self::getInstances($prototype);
        $descendants = $instances;
        foreach ($instances as $instance) {
            $descendants += self::getDescendants($instance);
        }
        return $descendants;
    }

    /**
     * Returns a list of prototype objects that have this prototype object
     * as their direct prototype.
     * @param \arc\prototype\Object $prototype
     * @return array
     */
    public static function getInstances($prototype) 
    {
        return (isset(self::$instances[$prototype]) ? self::$instances[$prototype] : [] );
    }

    /**
     * Returns the full prototype chain for the given object.
     * @param \arc\prototype\Object $obj
     * @return array
     */
    public static function getPrototypes($obj) 
    {
        $prototypes = [];
        while ( $prototype = $obj->prototype ) {
            $prototypes[] = $prototype;
            $obj = $prototype;
        }
        return $prototypes;
    }

    /**
     * Returns a new function that calls the given function just once and then simply
     * returns its result on each subsequent call.
     * @param callable function to call just once and then remember the result
     */
    public static function memoize($f) 
    {
        return memoize($f);
    }
}

/**
 * Helper function to make sure that the returned Closure is not defined in a static scope.
 * @param callable function to call just once and then remember the result
 */
function memoize($f) 
{
    return function () use ($f) {
        static $result;
        if (null === $result) {
            if ( $f instanceof \Closure && isset($this) ) {
                $f = \Closure::bind($f, $this);
            }
            $result = $f();
        }
        return $result;
    };
}

/**
 * 'private' function that must be declared outside static scope, so we can bind
 * the closure to an object to peek into its private _ownProperties property
 */
function _getOwnEntries($prototype) {
    // this needs access to the private _ownProperties variable
    // this is one way to do that.
    $f = \Closure::bind(function() {
        return $this->_ownProperties;
    }, $prototype, $prototype);
    return $f();
}
<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\lambda;

/**
 * This class allows you to create throw-away objects with methods and properties. It is meant to be used
 * as a way to create rendering objects for a certain data set. e.g.
 * <code>
 * $view = \arc\lambda::prototype( [
 *		'menu' => function ($children) {
 *			return \arc\html::ul(['class' => 'menu'], array_map( $this->menuitem, (array) $children ) );
 *		},
 *		'menuitem' => function ($input) {
 *			return \arc\html::li( $this->menulink( $input ), ( isset( $input['children'] ) ? $this->menu( $input['children'] ) : null ) );
 *		},
 *		'menulink' => function ($input) {
 *			return \arc\html::a( [ 'href' => $input['url'] ], $input['name'] );
 *		}
 * ] );
 * echo $view->menu( $menulist );
 * </code>
 */
final class Prototype
{
    private static $properties = [];

    /**
    * @var Object prototype Readonly reference to a prototype object. Can only be set in the constructor.
    */
    private $prototype = null;

    /**
     * Returns true if the named property is set in this object, disregarding the prototype chain
     * @param $name
     * @return bool
     */
    public function hasOwnProperty($name)
    {
        $props = $this->getLocalProperties();

        return isset( $props[$name] );
    }

    /**
     * Creates a new prototype object extending this one.
     * @param array $properties
     * @return static
     */
    public function extend($properties = [])
    {
        $properties['prototype'] = $this;
        $descendant = new static($properties);

        return $descendant;
    }

    /**
     * Returns true if the current object has the given object somewhere in its prototype chain.
     * @param $object
     * @return bool
     */
    public function hasPrototype($object)
    {
        if (!$this->prototype) {
            return false;
        }
        if ($this->prototype === $object) {
            return true;
        }

        return $this->prototype->hasPrototype( $object );
    }

    /**
     * @param array $properties
     */
    public function __construct($properties = [])
    {
        foreach ($properties as $property => $value) {
            if ( !is_numeric( $property ) && $property!='properties' ) {
                 if ( $property[0] == ':' ) {
                    $property = substr($property, 1);
                    $this->{$property} = $value;
                } else {
                    $this->{$property} = $this->_bind( $value );
                }
            }
        }
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     * @throws \arc\MethodNotFound
     */
    public function __call($name, $args)
    {
        if (isset( $this->{$name} ) && is_callable( $this->{$name} )) {
            return call_user_func_array( $this->{$name}, $args );
        } elseif (is_object( $this->prototype)) {
            $method = $this->_bind( $this->getPrototypeProperty( $name ) );
            if (is_callable( $method )) {
                return call_user_func_array( $method, $args );
            }
        }
        throw new \arc\MethodNotFound( $name.' is not a method on this Object', \arc\exceptions::OBJECT_NOT_FOUND );
    }

    /**
     * @param $name
     * @return array|null|Object
     */
    public function __get($name)
    {
        switch ($name) {
            case 'prototype':
                return $this->prototype;
            break;
            case 'properties':
                return $this->getPublicProperties();
            break;
            default:
                return $this->getPrototypeProperty( $name );
            break;
        }
    }

    /**
     * Returns a list of publically accessible properties of this object and its prototypes.
     * @return array
     */
    private function getPublicProperties()
    {
        // get public properties only, so use closure to escape local scope.
        // the anonymous function / closure is needed to make sure that get_object_vars
        // only returns public properties.
        return ( is_object( $this->prototype )
            ? array_merge( $this->prototype->properties, $this->getLocalProperties() )
            : $this->getLocalProperties() );
    }

    /**
     * Returns a list of publically accessible properties of this object only, disregarding its prototypes.
     * @return array
     */
    private function getLocalProperties()
    {
        $getLocalProperties = \Closure::bind(function ($o) {
            return get_object_vars($o);
        }, new dummy(), new dummy());
        return [ 'prototype' => $this->prototype ] + $getLocalProperties( $this );
    }

    /**
     * Get a property from the prototype chain and caches it.
     * @param $name
     * @return null
     */
    private function getPrototypeProperty($name)
    {
        if (is_object( $this->prototype )) {
            // cache prototype access per property - allows fast but partial cache purging
            if (!array_key_exists( $name, self::$properties )) {
                self::$properties[ $name ] = new \SplObjectStorage();
            }
            if (!self::$properties[$name]->contains( $this->prototype )) {
                self::$properties[$name][ $this->prototype ] = $this->_bind( $this->prototype->{$name} );
            }
            return self::$properties[$name][ $this->prototype ];
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (!in_array( $name, [ 'prototype', 'properties' ] )) {
            $this->{$name} = $this->_bind( $value );
            // purge prototype cache for this property - this will clear too much but cache will be filled again
            // clearing exactly the right entries from the cache will generally cost more performance than this
            unset( self::$properties[ $name ] );
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $val = $this->getPrototypeProperty( $name );

        return isset( $val );
    }

    /**
     *
     */
    public function __destruct()
    {
        return $this->_tryToCall( $this->__destruct );
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->_tryToCall( $this->__toString );
    }

    /**
     * @return mixed
     * @throws \arc\MethodNotFound
     */
    public function __invoke()
    {
        if (is_callable( $this->__invoke )) {
            return call_user_func_array( $this->__invoke, func_get_args() );
        } else {
            throw new \arc\MethodNotFound( 'No __invoke method found in this Object', \arc\exceptions::OBJECT_NOT_FOUND );
        }
    }

    /**
     *
     */
    public function __clone()
    {
        // make sure all methods are bound to $this - the new clone.
        foreach (get_object_vars( $this ) as $property) {
            $this->{$property} = $this->_bind( $property );
        }
        $this->_tryToCall( $this->__clone );
    }

    /**
     * Binds the property to this object
     * @param $property
     * @return mixed
     */
    private function _bind($property)
    {
        if ($property instanceof \Closure) {
            // make sure any internal $this references point to this object and not the prototype or undefined
            return \Closure::bind( $property, $this );
        }

        return $property;
    }

    /**
     * Only call $f if it is a callable.
     * @param $f
     * @param array $args
     * @return mixed
     */
    private function _tryToCall($f, $args = [])
    {
        if (is_callable( $f )) {
            return call_user_func_array( $f, $args );
        }
    }
}

/**
 * Class dummy
 * This class is needed because in PHP7 you can no longer bind to \stdClass
 * And anonymous classes are syntax errors in PHP5.6, so there.
 * @package arc\lambda
 */
class dummy {
}
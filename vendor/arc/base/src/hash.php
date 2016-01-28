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
 * Class hash
 * Utility methods to work with recursive hashes, setting/getting values and convert hashes to trees.
 * @package arc
 */
class hash
{
    /**
     * Recursively search a hash for a key-path and return its value or the default value if the key-path is not found.
     * @param $path
     * @param $hash
     * @param null $default
     * @return mixed|null
     */
    public static function get($path, $hash, $default = null)
    {
        $result = \arc\path::reduce( $path, function ($result, $item) {
            if (is_array( $result ) && array_key_exists( $item, $result )) {
                return $result[$item];
            }
        }, $hash );
        return isset($result) ? $result : $default;
    }

    /**
     * Check if a key-path is defined in the hash.
     * @param $path
     * @param $hash
     * @return bool
     */
    public static function exists($path, $hash)
    {
        $parent = \arc\path::parent($path);
        $filename = basename( $path );
        $hash = self::get( $parent, $hash );

        return (is_array($hash) && array_key_exists( $filename, $hash ));
    }

    private static function escape($name) {
        return str_replace('/','%2F',$name);
    }

    private static function unescape($name) {
        return str_replace('%2F','/',$name);
    }

    /**
     * Parse a variable name like 'name[index][index2]' to a key-path like '/name/index/index2/'
     * @param $name
     * @return string
     */
    public static function parseName($name)
    {
        $elements = explode( '[', $name );
        $path = array();
        foreach ($elements as $element) {
            if ($element[ strlen($element) -1 ] === ']') {
                $element = substr($element, 0, -1);
            }
            if ($element[0] === "'") {
                $element = substr($element, 1, -1);
            }
            $path[] = self::escape($element);
        }

        return '/'.implode( '/', $path ).'/';
    }

    /**
     * Compile a key-path like '/name/index/index2/' to a variable name like 'name[index][index2]'
     * @param $path
     * @param string $root
     * @return mixed
     */
    public static function compileName($path, $root = '')
    {
        return \arc\path::reduce( $path, function ($result, $item) {
            $item = self::unescape($item);
            return (!$result ? $item : $result . '[' . $item . ']');
        }, $root );
    }

    /**
     * Generate a NamedNode tree from a hash.
     * @param $hash
     * @param null $parent
     * @return tree\NamedNode|null
     */
    public static function tree($hash, $parent = null)
    {
        if (!isset( $parent )) {
            $parent = \arc\tree::expand();
        }
        if (is_array( $hash ) || $hash instanceof \Traversable) {
            foreach ($hash as $index => $value) {
                $child = $parent->appendChild( self::escape($index) );
                if (is_array( $value )) {
                    self::tree( $value, $child );
                } else {
                    $child->nodeValue = $value;
                }
            }
        } else {
            $parent->nodeValue = $hash;
        }

        return $parent;
    }
}

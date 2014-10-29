<?php

namespace arc;

class hash
{
    public static function get($path, $hash, $default = null)
    {
        $result = \arc\path::reduce( $path, function ($result, $item) {
            $item = rawurldecode($item); //FIXME: this may be unexpected
            if (is_array( $result ) && array_key_exists( $item, $result )) {
                return $result[$item];
            }
        }, $hash );
        return isset($result) ? $result : $default;
    }

    public static function exists($path, $hash)
    {
        $parent = \arc\path::parent($path);
        $filename = rawurldecode(basename( $path )); //FIXME: this may be unexpected
        $hash = self::get( $parent, $hash );

        return (is_array($hash) && array_key_exists( $filename, $hash ));
    }

    public static function parseName($name)
    {
        // parse name[index][index2] to /name/index/index2/
        $elements = explode( '[', $name );
        $path = array();
        foreach ($elements as $element) {
            if ($element[ strlen($element)-1 ] === ']') {
                $element = substr($element, 0, -1);
            }
            if ($element[0] === "'") {
                $element = substr($element, 1, -1);
            }
            $path[] = rawurlencode($element);
        }

        return '/'.implode( '/', $path ).'/';
    }

    public static function compileName($path, $root = '')
    {
        // parse /name/index/index2/ to name[index][index2]
        return \arc\path::reduce( $path, function ($result, $item) {
            $item = rawurldecode($item);

            return (!$result ? $item : $result . '[' . $item . ']');
        }, $root );
    }

    public static function tree($hash, $parent = null)
    {
        if (!isset( $parent )) {
            $parent = \arc\tree::expand();
        }
        if (is_array( $hash ) || $hash instanceof \Traversable) {
            foreach ($hash as $index => $value) {
                $child = $parent->appendChild( $index );
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

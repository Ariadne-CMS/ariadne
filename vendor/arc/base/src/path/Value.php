<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\path;

/**
 * This class is a value object for a collapsed path. It behaves as close as possible like a string.
 * But you can't change it. Serializing it to json or php will return a string. You can access characters
 * by position, like a string. You can count() it.
 * \arc\path::collapse() will return an instance of this class. If the collapsed path is identical to
 * another path Value, \arc\path::collapse will return that value. This means that you can use a ===
 * to compare path Value objects, as long as you only use \arc\path::collapse() to create them.
 */
final class Value implements \JsonSerializable, \ArrayAccess, \Countable {

    private $path = '';

	public function __construct($path)
	{
        $path = str_replace('\\', '/', (string) $path);
        $this->path = \arc\path::reduce(
            $path,
            function ($result, $entry) {
                if ($entry == '..' ) {
                    $result = dirname( $result );
                    if (isset($result[1])) { // fast check to see if there is a dirname
                        $result .= '/';
                    } else {
                        $result = '/';
                    }
                } else if ($entry !== '.') {
                    $result .= $entry .'/';
                }
                return $result;
            },
            '/' // initial value, always start paths with a '/'
        );
	}


	public function __toString()
	{
		return $this->path;
	}

	public function jsonSerialize():mixed
	{
		return $this->path;
	}

	public function __serialize()
	{
		return serialize($this->path);
	}

	public function __unserialize($data)
	{
		$this->path = unserialize($data);
	}

	public function count():int
	{
		return count($this->path);
	}

	public function offsetGet($offset):mixed
	{
		return $this->path[$offset];
	}

	public function offsetSet($offset, $char):void
	{
		throw new \LogicException('\arc\path\Value is immutable, cast it to a string to change it');
	}

	public function offsetUnset($offset):void
	{
		throw new \LogicException('\arc\path\Value is immutable, cast it to a string to change it');
	}

	public function offsetExists($offset):bool
	{
		return isset($this->path[$offset]);
	}
}
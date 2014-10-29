<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\cache;

class Proxy
{
    use \arc\traits\Proxy {
        \arc\traits\Proxy::__construct as private ProxyConstruct;
    }

    // TODO: allow more control on retrieval:
    // - get contents from cache even though cache may be stale
    //   perhaps through an extra option in __construct?
    protected $cacheStore   = null;
    protected $cacheControl = null;
    protected $targetObject = null;

    public function __construct($targetObject, $cacheStore, $cacheControl = 7200)
    {
        $this->ProxyConstruct( $targetObject );
        $this->targetObject = $targetObject;
        $this->cacheStore   = $cacheStore;
        $this->cacheControl = $cacheControl;
    }

    protected function __callCatch($method, $args)
    {
        // catch all output and return value, return it
        ob_start();
        $result = call_user_func_array( array( $this->targetObject, $method ), $args );
        $output = ob_get_contents();
        ob_end_clean();

        return array(
            'output' => $output,
            'result' => $result
        );
    }

    protected function __callCached($method, $args, $path)
    {
        // check the cache, if fresh, use the cached version
        $cacheData = $this->cacheStore->getIfFresh( $path );
        if (!isset( $cacheData )) {
            if ($this->cacheStore->lock( $path )) {
                // try to get a lock to calculate the value
                $cacheData = $this->__callCatch( $method, $args );
                if (is_callable( $this->cacheControl )) {
                    $cacheTimeout = call_user_func(
                        $this->cacheControl,
                        array(
                            'target'    => $this->targetObject,
                            'method'    => $method,
                            'arguments' => $args,
                            'result'    => $cacheData
                        )
                    );
                } else {
                    $cacheTimeout = $this->cacheControl;
                }
                $this->cacheStore->set( $path, $cacheData, $cacheTimeout );
            } else if ($this->cacheStore->wait( $path )) {
                // couldn't get a lock, so there is another proces writing a cache, wait for that
                // stampede protection
                $cacheData = $this->cacheStore->get( $path );
            } else {
                // wait failed, so just do the work without caching
                // FIXME: this should probably be configurable somewhere
                $cacheData = $this->__callCatch( $method, $args );
            }
        }

        return $cacheData;
    }

    public function __call($method, $args)
    {
        // create a usable but unique filename based on the arguments and method name
        //FIXME: md5 isn't collision resistant, so this might get abused to retrieve an incorrect result
        $path = $method . '(' . md5( serialize($args) ) . ')';

        $cacheData = $this->__callCached( $method, $args, $path );
        echo $cacheData['output'];
        $result = $cacheData['result'];
        if (is_object( $result )) { // for fluent interface we want to cache the returned object as well
            $result = new static( $result, $this->cacheStore->cd( $path ), $this->cacheControl );
        }

        return $result;
    }

    public function __get($name)
    {
        $result = $this->targetObject->{$name};
        if (is_object( $result )) {
            $result = new static( $result, $this->cacheStore->cd( $name ), $this->cacheControl );
        }

        return $result;
    }
}

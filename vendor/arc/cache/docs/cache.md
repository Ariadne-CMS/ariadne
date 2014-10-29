\arc\cache
=========

This component contains utility methods to ease working with PHP hashes. 

\arc\cache::proxy
-----------------
    (\arc\cache\Proxy) \arc\cache::proxy( (mixed) $target, (mixed) $cacheControl )

This method creates a \arc\cache\Proxy object for a target object or callable function. The cacheControl argument is optional. If set and it is an integer, the cache images the proxy generates will be valid for that amount of seconds after creation. If cacheControl is a callable function, for every cache request the cacheControl function will be called with the target object, method name, arguments and the captured result. The cacheControl function must then return an integer specifying the amount of seconds the cache image will be valid.

\arc\cache::get
---------------------
    (mixed) \arc\cache::get( (string) $name )

\arc\cache::set
---------------------
    (mixed) \arc\cache::set( (string) $name, (mixed) $value )

\arc\cache::remove
---------------------
    (mixed) \arc\cache::remove( (string) $name )

\arc\cache::create
------------------
    (\arc\cache\Store) \arc\cache::create( (string) $name )

\arc\cache::getCacheStore
-------------------------
    (\arc\cache\Store) \arc\cache::getCacheStore()





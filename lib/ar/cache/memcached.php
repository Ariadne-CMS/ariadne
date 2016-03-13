<?php

ar_pinp::allow('ar_cache_memcachedStore');

class ar_cache_memcachedStore implements ar_cacheStoreInterface, arKeyValueStoreInterface {
	private $timeout = 7200;
	private $mc = null;
	public function __construct( $servers, $timeout = 7200,  $prefix = 'AR_') {

		if ( is_string($timeout) ) {
			$timeout = strtotime( $timeout, 0);
		}

		$this->timeout = $timeout;
		$this->mc = $mc = new memCached();
		$mc->addServers($servers);

	}

	// key
	public function get( $path ) {
		$res = $this->mc->get($path);;
		$code = $this->mc->getResultCode();
		if($code !== MEMCACHED_SUCCESS ){
			return $res;
		}
		return null;
	}

	public function getIfFresh( $path, $timeout = 0 ) {
		// we do not know if it is fresh
		return $this->get( $path );
	}

	// key value expire
	public function set( $path, $value, $timeout = 7200 ) {
		$res = $this->mc->set($path, $value, $timeout);
		return $res;
	}

	// meta info
	public function info( $path ) {
		$res = [];
		// FIXME: implement this
		return $res;
	}

	// remove key
	public function clear( $path = null ) {
		return $this->mc->delete($path);
	}

	public function subStore( $path ) {
		// geen idee eigenlijk
	}

	// hoeveel tijd hebben we nog
	public function isFresh( $path ) {
		$res = $this->get( $path );
		return ($res !== null);
	}

	public function putvar( $name, $value ) {
		return $this->set( $name, $value );
	}

	public function getvar( $name ) {
		return $this->get( $name );
	}
}


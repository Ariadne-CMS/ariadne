<?php

ar_pinp::allow('ar_cache_redisStore');

class ar_cache_redisStore implements ar_cacheStoreInterface, arKeyValueStoreInterface {
	private $timeout = 7200;
	private $redis   = null;

	public function __construct( $options, $timeout = 7200,  $prefix = 'AR_') {

		if ( is_string($timeout) ) {
			$timeout = strtotime( $timeout, 0);
		}

		$this->timeout = $timeout;
		$this->prefix  = $prefix;
		$this->redis   = $redis = new Redis();

		if (isset($options['port'])) {
			$redis->pconnect($options['server'],$options['port']);
		} else {
			$redis->pconnect($options['server']);
		}

		if (isset($options['auth'])) {
			$redis->auth($options['auth']);
		}

		if (isset($options['db'])) {
			$redis->select($options['db']);
		}
		$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
		$redis->setOption(Redis::OPT_PREFIX, $prefix);
	}

	// key
	public function get( $path ) {
		$res = $this->redis->exists($path);
		if($res === true ) {
			return $this->redis->get($path);
		}
		return null;
	}

	public function getIfFresh( $path, $timeout = 0 ) {
		$ttl = $this->redis->ttl($path);
		if($ttl > 0 || $ttl === -1 ){ 
			return $this->redis->get($path);
		}
		return null;
	}

	// key value expire
	public function set( $path, $value, $timeout = 7200 ) {
		$res = $this->redis->setEx($path, $timeout, $value);
		return $res;
	}

	// meta info
	public function info( $path ) {
		$res =  [
			'size'    => null,
			'timeout' => $this->redis->ttl( $path ),
			'ctime'   => null,
		];
		$res['fresh'] = (bool)$res['timeout'];
		return $res;
	}

	// remove key
	public function clear( $path = null ) {
		return $this->redis->delete($path);
	}

	public function subStore( $path ) {
		// geen idee eigenlijk
	}

	// hoeveel tijd hebben we nog
	public function isFresh( $path ) {
		return (bool)$this->redis->ttl($path);
	}

	// purge, we do not have the option to partial purge, so purge all
	public function purge($name=null){
		return $this->redis->delete( $this->redis->keys($name .'*'));
	}

	public function putvar( $name, $value ) {
		return $this->set( $name, $value );
	}

	public function getvar( $name ) {
		return $this->get( $name );
	}
}


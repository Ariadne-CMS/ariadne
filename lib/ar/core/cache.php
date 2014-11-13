<?php

	interface ar_core_cacheInterface {
		public function __construct($cacheOptions);
		public function isFresh($cacheImage);
		public function isFresher($cacheImage, $minFresh);
		public function isYounger($cacheImage, $maxAge);
		public function getCreatedTime($cacheImage);
		public function getStaleTime($cacheImage);
		public function headers($cacheImage);
		public function passThru($cacheImage);
		public function start();
		public function stop();
		public function save( $cacheImage, $freshTime );
	}

	// FIXME: should this class have an implementation?
	abstract class ar_core_cacheDisk implements ar_core_cacheInterface {

	}

<?php

	class ar_core_store {

		private static $store = null;

		private static function getStore() {
			if ( !self::$store ) {
				self::$store = new ar_core_store_nodes(
					ar_core_registry::get('dbConnector'),
					ar_core_registry::get('objectStore'),
					ar_core_registry::get('propertyStore')
				);
			}
			return self::$store;
		}

		public static function get( $path ) {
			$store = self::getStore();
			return $store->get( $path );
		}

		public static function exists( $path ) {
			$store = self::getStore();
			return $store->exists( $path );
		}

		public static function ls( $path ) {
			$store = self::getStore();
			return $store->ls( $path );
		}

		public static function find( $path, $query ) {
			$store = self::getStore();
			return $store->find( $path, $query );
		}

		public static function parents( $path ) {
			$store = self::getStore();
			return $store->parents( $path );
		}
	}

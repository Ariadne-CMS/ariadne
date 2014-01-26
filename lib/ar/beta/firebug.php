<?php
	global $AR;
	ar_pinp::allow( 'ar_beta_firebug' );

	class ar_beta_firebug extends arBase {

		public static function send (){
			$instance = FirePHP::getInstance(true);
			$args = func_get_args();
			try {
				return call_user_func_array(array($instance,'fb'),$args);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		}

		public static function log($Object, $Label=null)
		{
			try { 
				return self::send($Object, $Label, FirePHP::LOG);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 

		public static function info($Object, $Label=null)
		{
			try {
				return self::send($Object, $Label, FirePHP::INFO);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 
		
		public static function warn($Object, $Label=null)
		{
			try {
				return self::send($Object, $Label, FirePHP::WARN);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 

		public static function error($Object, $Label=null)
		{
			try {
				return self::send($Object, $Label, FirePHP::ERROR);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 

		public static function dump($Key, $Variable)
		{
			try {
				return self::send($Variable, $Key, FirePHP::DUMP);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 
		public static function trace($Label)
		{
			try {
				return self::send($Label, FirePHP::TRACE);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 

		public static function table($Label, $Table)
		{
			try {
				return self::send($Table, $Label, FirePHP::TABLE);
			} catch (Exception $e) {
				return new ar_error($e->getMessage(), $e->getCode() );
			}
		} 

	}

	/* todo:
		* add grouping
		* add object filtering
	*/

	
?>
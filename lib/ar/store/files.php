<?php

	ar_pinp::allow( 'ar_store_files', array( 'ls', 'get', 'save', 'delete', 'touch', 'exists' ) );

	/*
	 * prevent mess detector from warning for the private static fields
	 * @SuppressWarnings(PHPMD.UnusedPrivateField)
	 */
	class ar_store_files extends arBase {

		private static function parseName( $fname ) {
			list( $nls, $name ) = explode('_', $fname, 2);
			return array(
				'nls' => $nls,
				'name' => $name
			);
		}

		private static function compileName( $name, $nls ) {
			if ( !$nls ) {
				$ob = ar::context()->getObject();
				$nls = $ob->nls;
			}
			return $nls.'_'.$name;
		}

		private static function getStore() {
			$ob = ar::context()->getObject();
			return array( $ob, $ob->store->get_filestore("files") );
		}

		public static function ls( $nls=null ) {
			list($ob, $fstore) = static::getStore();
			$files = $fstore->ls($ob->id);
			if ( !$files ) {
				$files = array();
			}
			$files = array_map( array('self','parseName'), $files );
			if ( isset($nls) ) {
				$files = array_filter( $files, function($f) use($nls) {
					return ( $f['nls'] == $nls );
				} );
			}
			return $files;
		}

		public static function get( $name, $nls=null ) {
			$info = static::exists($name, $nls);
			if ( !$info ) {
				return ar_error::raiseError('File not found: '.$name.' - '.$nls, 404);
			}
			list( $ob, $fstore ) = static::getStore();
			$fname = static::compileName($info['name'], $info['nls']);
			$stream = $fstore->get_stream($ob->id, $fname);
			return new ar_content_filesFile( $stream );
		}

		public static function save( $name, $contents, $nls=null ) {
			list( $ob, $fstore ) = static::getStore();
			$fname = static::compileName($name, $nls);
			if( $contents instanceof ar_content_filesFile ){
				// FIXME: this should be more efficient then coping the whole contents of the file in memory
				// should be fixed with a copyFrom/copyTo function call on ar_content_filesFile
				$contents = $contents->getContents();
			}
			if ( is_resource($contents) && get_resource_type($contents)==='stream' ) {
				return $fstore->copy_stream_to_store( $contents, $ob->id, $fname );
			} else {
				return $fstore->write( (string) $contents, $ob->id, $fname );
			}
		}

		public static function delete( $name, $nls=null ) {
			list( $ob, $fstore ) = static::getStore();
			$fname = static::compileName($name, $nls);
			if ( $fstore->exists( $ob->id, $fname ) ) {
				return $fstore->remove( $ob->id, $fname );
			}
			return false;
		}

		public static function touch( $name, $time=null, $nls=null ) {
			list( $ob, $fstore ) = static::getStore();
			$fname = static::compileName($name, $nls);
			return $fstore->exists( $ob->id, $fname, $time );
		}

		public static function temp( $contents=null ) {
			list( $ob, $fstore ) = static::getStore();
			$tmpsrc = tempnam($ob->store->get_config("files")."temp", "tmp");
			if ( !$tmpsrc ) {
				// FIXME: nlsstrings
				return ar_error::raiseError( 'Could not create temp file', 501 );
			}
			$fp = fopen( $tmpsrc, 'w+' );
			if (is_resource($fp)) {
				$res = new ar_content_filesFile($fp);
				if( $contents instanceof ar_content_filesFile ){
					// FIXME: create an more efficient way for copying with a stream_copy_to_stream call or wrapping t in an CopyFrom/CopyTo api
					// or even an temp function on a ar_content_filesFile object which creates an temp file from the ar_content_filesFile
					$contents = $contents->getContents();
					fwrite($fp, $contents);
					rewind($fp);
				} else if ( is_resource($contents) && get_resource_type($contents)==='stream' ) {
					stream_copy_to_stream( $contents, $fp );
					rewind($fp);
				} else if (isset($contents)) {
					fwrite($fp, $contents);
					rewind($fp);
				}
				return $res;
			} else {
				// FIXME: nlsstrings
				return ar_error::raiseError('Could not create temp file', 502);
			}
		}

		public static function exists( $name, $nls=null ) {
			list( $ob, $fstore ) = static::getStore();
			$fname = static::compileName($name, $nls);
			if ( !$fstore->exists($ob->id, $fname) && !isset($nls) ) {
				$nls = $ob->data->nls->default;
				$fname = static::compileName($name, $nls);
			}
			if ( !$fstore->exists($ob->id, $fname ) ) {
				return false;
			}
			return static::parseName($fname);
		}

	}

?>
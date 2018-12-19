<?php

	use arc\path as path;

	class ar_template_filesystem extends arBase {
		private $path;
		private $config;

		public function __construct($path, $config ) {
			$this->path   = path::collapse($path);
			$this->config = $config;
			$this->config['path'] = path::collapse($config['path']);
		}

		public function get($path, $name){
			$arpath = path::collapse($path);

			if ( strpos($arpath, $this->path, 0) !== 0) {
				return ar('error')->raiseError('invalide path for loading template',500);
			}

			$realpath = $this->config['path'] . substr($arpath,strlen($this->path));
			$realpath = realpath($realpath) .'/';

			$cachepath = sha1($path . $name);
			$tempOb    = ar::context()->getObject();
			$cacheroot = $tempOb->store->get_config('files').'temp/';

			if ( 
				! file_exists( $cacheroot . $cachepath )  ||
				( filemtime($cacheroot . $cachepath ) < filemtime ( $realpath  . $name ) )
			) {
				$compiled = $this->compile($path, $name);
				file_put_contents($cacheroot . $cachepath, $compiled);
			}
			include (  $cacheroot . $cachepath );
			return $arTemplateFunction;
		}

		public function save($path, $name, $template, $local=null, $private=null) {
			return false;
		}

		public function load($path, $name) {
			$arpath = path::collapse($path);

			if ( strpos($arpath, $this->path, 0) !== 0) {
				return ar('error')->raiseError('invalide path for loading template',500);
			}

			$realpath = $this->config['path'] . substr($arpath,strlen($this->path));
			$realpath = realpath($realpath) .'/';

			return file_get_contents($realpath . $name);
		}

		public function ls($path) {
			$arpath = path::collapse($path);

			if ( strpos($arpath, $this->path, 0) !== 0) {
				return [];
			}
			$realpath = $this->config['path'] . substr($arpath,strlen($this->path));
			$realpath = realpath($realpath) .'/';
			$traverse = 'src/';
			if (!file_exists($realpath . 'library.json')) {
				$realparent = path::parent($realpath);
				$node = basename($realpath);
				if ($node === 'tests' && file_exists($realparent . 'library.json') ) {
					// special case
					// system/lib/libname/tests/ is a sibling of src instead of a child library
					$traverse = 'tests/';
					$arpath   = path::parent($path);
					$realpath = $realparent;
				} else {
					return [];
				}
			}

			$config = json_decode(file_get_contents($realpath . 'library.json'),true);

			if(!isset($config['exports']) ) {
				$config['exports'] = [];
			}
			if(!isset($config['local']) ) {
				$config['local'] = [];
			}

			$result = [];

			$traverseDir = function ($path, $type = 'pobject', $nls = 'any') use ($arpath, &$result, $config, &$traverseDir, $realpath) {
				$path = path::collapse($path);
				if (is_dir($path) ) {
					$index = scandir($path, SCANDIR_SORT_NONE);
					if($index !== false) {
						list($maintype, $subtype) = explode('.', $type,2);
						foreach($index as $filename) {
							if($filename[0] === "." ) {
								continue;
							}
							$filepath = $path . $filename;
							if ( is_dir($filepath) ) {
								if (strlen($filename) == 2) {
									$traverseDir(path::collapse($filename, $path), $type, $filename);
								} else {
									$traverseDir(path::collapse($filename, $path), $filename);
								}
							} else if ( is_file($filepath) ) {
								$tempname = sprintf("%s.%s.%s",$type,$filename,$nls);
								$confname = sprintf("%s::%s",$type,$filename);
								$private = true;
								if (isset( $config['exports'] ) ) {
									if ($config['exports'][0] === '*') {
										$private = false;
									} else {
										$private = ! (
											in_array($filename, $config['exports']) ||
											in_array($confname, $config['exports'])
										);
									}
								}
								$local = false;
								if (isset( $config['local'] ) ) {
									$local = in_array($confname, $config['local']);
								}
								$result[$filename][] = [
									'id'       => PHP_INT_MAX,
									'path'     => $arpath,
									'type'     => $maintype,
									'subtype'  => $subtype,
									'name'     => $tempname,
									'filename' => substr($filepath,strlen($realpath)),
									'language' => $nls,
									'private'  => $private,
									'local'    => $local,
								];
							}
						}
					}
				}

			};
			$traverseDir($realpath . $traverse );
			return $result;
		}

		public function rm($path, $name){
		}

		public function exists($path, $name) {
			$arpath = path::collapse($path);

			if ( strpos($arpath, $this->path, 0) !== 0) {
				return ar('error')->raiseError('invalide path for loading template',500);
			}

			$realpath = $this->config['path'] . substr($arpath,strlen($this->path));
			$realpath = realpath($realpath) .'/';
			return file_exists($realpath . $name);
		}

		public function compile($path, $name) {
			global $AR;
			$arpath = path::collapse($path);

			if ( strpos($arpath, $this->path, 0) !== 0) {
				return ar('error')->raiseError('invalide path for loading template',500);
			}

			$realpath = $this->config['path'] . substr($arpath,strlen($this->path));
			$realpath = realpath($realpath) .'/';

			$template = file_get_contents($realpath . $name);

			require_once(AriadneBasePath."/modules/mod_pinp.phtml");

			$pinp = new pinp($AR->PINP_Functions, "local->", "\$AR_this->_");

			// FIXME error checking
			$compiled = $pinp->compile(strtr($template,"\r",""));
			$compiled = sprintf($AR->PINPtemplate, $compiled);
			return $compiled;

		}
	}

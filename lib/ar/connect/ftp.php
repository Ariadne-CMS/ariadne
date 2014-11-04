<?php

	ar_pinp::allow( 'ar_connect_ftp');
	ar_pinp::allow( 'ar_connect_ftpClient' );

	class ar_connect_ftp extends arBase {

		public static $timeout      = 90;
		public static $pasv         = false;
		public static $transferMode = FTP_BINARY;

		public static function get( $url, $options = array() ) {
			$path = parse_url( $url, PHP_URL_PATH );
			$fileName = basename($path);
			$client = new ar_connect_ftpClient( $url, $options );
			if ( !ar_error::isError( $client) ) {
				return $client->get( $fileName );
			} else {
				return $client;
			}
		}

		public static function put( $url, $contents, $options = array() ) {
			$path = parse_url( $url, PHP_URL_PATH );
			$fileName = basename($path);
			$client = new ar_connect_ftpClient($url, $options );
			if ( !ar_error::isError( $client ) ) {
				return $client->put( $contents, $fileName );
			} else {
				return $client;
			}
		}

		public static function client( $url = null, $options = array() ) {
			return new ar_connect_ftpClient( $url, $options );
		}

		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'timeout' :
					self::$timeout = $value;
				break;
				case 'pasv' :
					self::$pasv = $value;
				break;
				case 'transferMode' :
					self::$transferMode = $value;
				break;
			}
		}

		public function __set( $name, $value ) {
			ar_connect_ftp::configure( $name, $value );
		}

		public function __get( $name ) {
			if ( isset( ar_connect_ftp::${$name} ) ) {
				return ar_connect_ftp::${$name};
			}
		}

	}

	interface ar_connect_ftpClientInterface {

		public function get( $file, $options = array() );

		public function put( $contents, $file, $options = array() );

		public function login( $username, $password = null);

		public function connect( $host, $port = 21);

		public function disconnect();

		public function delete( $file, $options = array() );

		public function cd( $dir );

		public function ls();

		public function mkdir( $dirname );

		public function rename( $name, $newname );

		public function chmod( $mode, $filename );

		public function size( $filename );

		public function mdtm( $filename );

		public function pwd();

		public function mode( $mode );

		public function pasv( $pasv );

	}

	class ar_connect_ftpClient extends arBase implements ar_connect_ftpClientInterface {
		//FIXME: change error codes to the ar_exception constants
		public $options = array();
		public $host = null;
		public $port = null;
		public $user = null;
		protected $pass = null;
		public $path = null;
		protected $connection = null;

		public function __construct( $url = null, $options = array() ) {
			$this->options = $options + array(
				'mode' => ar_connect_ftp::$transferMode,
				'pasv' => ar_connect_ftp::$pasv
			);
			$parsed = parse_url( $url );
			if ($parsed) {
				$this->host = $parsed['host'];
				$this->port = $parsed['port'] ? $parsed['port'] : 21;
				$this->user = $parsed['user'] ? $parsed['user'] : 'anonymous';
				$this->pass = $parsed['pass'] ? $parsed['pass'] : 'guest';
				$this->path = $parsed['path'];
				if ($this->path[strlen($this->path)-1] != '/' ) {
					$this->path = substr(dirname($this->path), 1); // relative path for cd
				}
				if ($this->host) {
					$this->connect( $this->host, $this->port );
					$this->login( $this->user, $this->pass );
					if ($this->path) {
						$this->cd( $this->path );
					}
				}
			}
		}

		public function get( $file, $options = array() ) {
			$this->options = array_merge( $this->options, (array) $options );
			$fp = fopen("php://temp/maxmemory:10485760", "w");
			ftp_fget( $this->connection, $fp, $file, $this->options['mode'] );
			fseek( $fp, 0 );
			$result = stream_get_contents( $fp );
			fclose( $fp );
			return $result;
		}

		public function put( $contents, $file, $options = array() ) {
			$this->options = array_merge( $this->options, (array) $options );
			if ($contents instanceof pfile ) {
				global $store;
				$files = $store->get_filestore('files');
				$path = $files->make_path($contents->id, 'file');
				$fp = fopen($path, 'r');
			} else {
				$fp = fopen("php://temp/maxmemory:10485760", "w+");
				fwrite( $fp, (string) $contents );
				fseek( $fp, 0);
			}
			$result = ftp_fput( $this->connection, $file, $fp, $this->options['mode'] );
			fclose($fp);
			if ( !$result ) {
				return ar::error( "Could not save file $file.", 10 );
			}
			return $this;
		}

		public function login( $username, $password = null) {
			if (!@ftp_login($this->connection, $username, $password)) {
				return ar::error( "Could not connect as $username", 1);
			}
			return $this;
		}

		public function connect( $host, $port = 21) {
			if ( ! $this->connection = ftp_connect( $host, $port ) ) { // FIXME: add timeout?
				return ar::error( "Could not connect to $host on port $port", 2);
			} else if (ar_connect_ftp::$timeout) {
				ftp_set_option( $this->connection, FTP_TIMEOUT_SEC, ar_connect_ftp::$timeout );
			}
			return $this;
		}

		public function cd( $dir ) {
			$result = ftp_chdir( $this->connection, $dir );
			if ( !$result ) {
				return ar::error( "Could not change to directory $dir.", 9);
			}
			return $this;
		}

		public function disconnect() {
			ftp_close( $this->connection );
			return $this;
		}

		public function delete( $file, $options = array() ) {
			$result = ftp_delete( $this->connection, $file );
			if ( !$result ) {
				return ar::error( "Could not delete file $file.", 7 );
			}
			return $this;
		}

		public function ls($path='.', $verbose=false) {
			if (!$verbose) {
				$result = ftp_nlist($this->connection, $path);
				if ( !$result ) {
					return ar::error( "Could not list the current directory.", 8);
				}
			} else {
				if (!$this->connection) {
					return ar::error("Connection is not active", 42);
				}
				$list = ftp_rawlist($this->connection, $path);
				if ( !$list ) {
					return ar::error( "Could not rawlist the current directory.", 9);
				}
				$result = array();
				foreach($list as $info) {
					$info = preg_split("/[\s]+/", $info, 9);
					$entry = array(
						"permissions" => $info[0],
						"linkcount" => $info[1],
						"userid" => $info[2],
						"groupid" => $info[3],
						"size" => (int)$info[4],
						"mtime" => strtotime($info[5] . " " . $info[6] . " " . $info[7]),
						"name"=> $info[8]
					);

					if (substr($info[0], 0, 1) == "d") {
						$entry['type'] = "dir";
					} elseif (substr($info[0], 0, 1) == "l") {
						$entry['type'] = "shortcut";
						$nameinfo = explode(" -> ", $info[8]);
						$entry['name'] = $nameinfo[0];
						$entry['target'] = $nameinfo[1];
					} else {
						$entry['type'] = "file";
					}
					$result[$entry['name']] = $entry;
				}
			}
			return $result;
		}

		public function mkdir( $dirname ) {
			$result = ftp_mkdir( $this->connection, $dirname );
			if (!$result) {
				return ar::error( "Could not make directory $dirname.", 3);
			}
			return $this;
		}

		public function rename( $name, $newname ) {
			if (!ftp_rename( $this->connection, $name, $newname ) ) {
				return ar::error( "Could not rename $name to $newname.", 4);
			}
			return $this;
		}

		public function chmod( $mode, $filename ) {
			if (!ftp_chmod( $this->connection, $mode, $filename) ) {
				return ar::error( "Could not chmod $filename.", 5);
			}
			return $this;
		}

		public function size( $filename ) {
			$result = ftp_size($this->connection, $filename);
			if ( $result == -1 ) {
				return null;
			} else {
				return $result;
			}
		}

		public function mdtm( $filename ) {
			$result = ftp_mdtm( $this->connection, $filename );
			if ($result == -1 ) {
				return null;
			} else {
				return $result;
			}
		}

		public function pwd() {
			return ftp_pwd( $this->connection );
		}

		public function mode( $mode ) {
			$this->options['mode'] = $mode;
			return $this;
		}

		public function pasv( $pasv = true ) {
			$this->options['pasv'] = $pasv;
			if ( !ftp_pasv( $this->connection, $pasv) ) {
				return ar::error( "Could not switch passive mode.", 6);
			}
			return $this;
		}

	}

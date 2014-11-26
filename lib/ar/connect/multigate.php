<?php

	ar_pinp::allow( 'ar_connect_multigate' );
	ar_pinp::allow( 'ar_connect_multigateClient' );

	class ar_connect_multigate extends arBase {

		public static function client( $host, $port ) {
			return new ar_connect_multigateClient( $host, $port );
		}

	}

	class ar_connect_multigateClient extends arBase {
		private $host, $port;
		public $errorNr, $errorStr;

		public function __construct( $host, $port ) {
			$this->host = $host;
			$this->port = $port;
			$this->errorNr = false;
			$this->errorStr = false;
		}

		public function send( $protocol, $target, $message ) {
			// Open socket
			$socket = @fsockopen( $this->host, $this->port, $this->errorNr, $this->errorStr );
			if ( $socket ) {
				if ( $this->isSupported( $protocol ) ) {
					$matches = array("\n", "\r");
					$replaces = array("\xb6", ""); // newlines are \xb6 in the internal multigate protocol. \r is replaced to make sure the socket is not abused.

					$result = fwrite( $socket, str_replace( $matches, $replaces, "TOPROTOCOL $protocol $target $message" ) );

					if ( $result !== false ) {
						return true;
					} else {
						$this->errorNr = 42;
						$this->errorStr = "Couldn't write to Multigate socket.";
					}
				}
				fclose( $socket );
			}

			return ar::error( $this->errorstr, $this->errornr );
		}

		private function isSupported( $protocol ) {
			// FIXME: Fetch the actual list from multigate to check against.
			if ( $protocol == "irc" ) {
				return true;
			}
			return false;
		}
	}

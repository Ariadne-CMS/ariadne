<?php
	class pinp_multigate extends multigate {
		function _create($host, $port) {
			return new pinp_multigate($host, $port);
		}
		function _send($protocol, $target, $message) {
			return $this->send($protocol, $target, $message);
		}
	}

	class multigate {
		function multigate($host, $port) {
			$this->host = $host;
			$this->port = $port;
			$this->errornr = false;
			$this->errorstr = false;
		}
		
		function send($protocol, $target, $message) {
			// Open socket
			$socket = @fsockopen($this->host, $this->port, $this->errornr, $this->errorstr);
			if ($socket) {
				if ($this->is_supported($protocol)) {
					$matches = array("\n", "\r");
					$replaces = array("\xb6", ""); // newlines are \xb6 in the internal multigate protocol. \r is replaced to make sure the socket is not abused.

					$result = fwrite($socket, str_replace($matches, $replaces, "TOPROTOCOL $protocol $target $message"));

					if ($result !== false) {
						return true;
					} else {
						$this->errornr = 42;
						$this->errorstr = "Couldn't write to Multigate socket.";
					}
				}
				fclose($socket);
			}
			
			return false;
		}
		
		function is_supported($protocol) {
			// FIXME: Fetch the actual list from multigate to check against.
			if ($protocol == "irc") {
				return true;
			}
			return false;
		}
	}
?>

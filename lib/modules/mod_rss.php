?php
	/*
	
	*/

	class pinp_rss {

		function _loadFromUrl($url, $username='', $password='') {
		/* Loads an rss feed from a url */
			return rss::loadFromUrl($url, $username, $password);
		}

		function _loadFromString($rss) {
			return rss::loadFromString($rss);
		}

	}

	class rss {

		function loadFromUrl($url, $username='', $password='') {
		/* Loads an rss feed from a url */
		}

		function loadFromString($rss) {
		/* parse rss feed and initialize and return an rssFeed object */
		}

	}

	class rssFeed {

		var $feed;

		function rssFeed($feed) {
			$this->feed=$feed;
		}

		function _reset() {
			return $this->reset();
		}

		function _next() {
			return $this->next();
		}

		function _count() {
			return $this->count();
		}

		function _current() {
			return $this->current();
		}

		function _ls($template, $args='') {
			return $this->ls($template, $args);
		}
		
		function reset() {
		}

		function next() {
		}

		function current() {
		}

		function call($template, $args='') {
			return $this->current()->call($template, $args);
		}

		function count() {
		}

		function ls($template, $args='', $limit=100, $offset=0) {
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			do {
				$this->call($template, $args);
				$limit--;
			} while ($this->next() && $limit);
		}

		function getArray($limit=100, $offset=0) {
			var $result=Array();
			$this->reset();
			if ($offset) {
				while ($offset) {
					$this->next();
					$offset--;
				}
			}
			do {
				$result[]=$this->current();
				$limit--;
			} while ($this->next() && $limit);
			return $result;
		}

	}

?>
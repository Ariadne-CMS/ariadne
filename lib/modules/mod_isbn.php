<?php
	require_once("mod_isbn/ISBN.php");


	class ISBN_For_PINP extends ISBN {

		function __construct($groups_csv, $isbn, $ver) {
			ISBN::ISBN($isbn, $ver);
			$this->groups_csv = $groups_csv;
		}

		function _convert($isbnin, $verfrom = ISBN_VERSION_ISBN_10, $verto = ISBN_VERSION_ISBN_13) {
			return $this->convert($isbnin, $verfrom, $verto);
		}

		function _getCheckdigit() {
			return $this->getCheckdigit();
		}

		function _getEAN() {
			return $this->getEAN();
		}

		function _getGroup() {
			return $this->getGroup();
		}

		function _getISBN() {
			return $this->getISBN();
		}

		function _getISBNDisplayable($format = '') {
			return $this->getISBNDisplayable($format);
		}

		function _setISBN($isbn) {
			return $this->setISBN($isbn);
		}

		function _getPublisher() {
			return $this->getPublisher();
		}

		function _getTitle() {
			return $this->getTitle();
		}

		function _isValid() {
			return $this->isValid();
		}

		function _validate($isbn, $ver = ISBN_DEFAULT_INPUTVERSION) {
			return $this->validate($isbn, $ver);
		}

		function _getVersion() {
			return $this->getVersion();
		}

		function _guessVersion($isbn) {
			return $this->guessVersion($isbn);
		}

	}

	class pinp_ISBN  {

		function _create($isbn = '', $ver = ISBN_DEFAULT_INPUTVERSION) {
			$groups_csv = $this->store->get_config("code")."modules/mod_isbn/data/groups.csv";
			return new ISBN_For_PINP($groups_csv, $isbn, $ver);
		}

	}

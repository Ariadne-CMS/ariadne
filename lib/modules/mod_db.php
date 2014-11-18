<?php

require_once('DB.php'); // PEAR DB class

class pinp_DB {
	protected $key;

	public function __construct($key) {
		$this->key=$key;
	}

	public function _connect($dsn, $options = false) {
		global $AR;

		$key=@count($AR->DB_list);
		$AR->DB_list[$key]=DB::connect($dsn, $options);
		if (DB::isError($AR->DB_list[$key])) {
			$result=$AR->DB_list[$key];
			array_pop($AR->DB_list);
		} else {
			$result=new pinp_DB($key);
		}
		return $result;
	}

	public function _quoteString($string) {
		global $AR;
		return $AR->DB_list[$this->key]->quoteString($string);
	}

	public function _quote($string) {
		global $AR;
		return $AR->DB_list[$this->key]->quote($string);
	}

	public function _provides($feature) {
		global $AR;
		return $AR->DB_list[$this->key]->provides($feature);
	}

	public function _setFetchMode($fetchmode, $object_class = null) {
		global $AR;
		return $AR->DB_list[$this->key]->setFetchMode($fetchmode, $object_class);
	}

	public function _setOption($option, $value) {
		global $AR;
		return $AR->DB_list[$this->key]->setOption($option, $value);
	}

	public function _getOption($option) {
		global $AR;
		return $AR->DB_list[$this->key]->getOption($option);
	}

	public function _prepare($query) {
		global $AR;
		return $AR->DB_list[$this->key]->prepare($query);
	}

	public function _execute($stmt, $data = false) {
		global $AR;
		$result=$AR->DB_list[$this->key]->execute($stmt, $data);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	public function _executeMultiple( $stmt, &$data ) {
		global $AR;
		return $AR->DB_list[$this->key]->executeMultiple( $stmt, $data );
	}

	public function _query($query, $params = array()) {
		global $AR;
		$result = $AR->DB_list[$this->key]->query($query, $params);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	public function _limitQuery($query, $from, $count) {
		global $AR;
		$result = $AR->DB_list[$this->key]->limitQuery($query, $from, $count);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	public function _getOne($query, $params = array()) {
		global $AR;
		return $AR->DB_list[$this->key]->getOne($query, $params);
	}

	public function _getRow($query, $params = null, $fetchmode = DB_FETCHMODE_DEFAULT) {
		global $AR;
		return $AR->DB_list[$this->key]->getRow($query, $params, $fetchmode);
	}

	public function _getCol($query, $col = 0, $params = array()) {
		global $AR;
		return $AR->DB_list[$this->key]->getCol($query, $col, $params);
	}

	public function _getAssoc($query, $force_array = false, $params = array(),
						$fetchmode = DB_FETCHMODE_ORDERED, $group = false) {
		global $AR;
		return $AR->DB_list[$this->key]->getAssoc($query, $force_array, $params, $fetchmode, $group);
	}

	public function _getAll($query, $params = null, $fetchmode = DB_FETCHMODE_DEFAULT) {
		global $AR;
		return $AR->DB_list[$this->key]->getAll($query, $params, $fetchmode);
	}

	public function _autoCommit($onoff=false) {
		global $AR;
		return $AR->DB_list[$this->key]->autoCommit($onoff);
	}

	public function _commit() {
		global $AR;
		return $AR->DB_list[$this->key]->commit();
	}

	public function _rollback() {
		global $AR;
		return $AR->DB_list[$this->key]->rollback();
	}

	public function _numRows($result) {
		global $AR;
		return $AR->DB_list[$this->key]->numRows($result);
	}

	public function _affectedRows() {
		global $AR;
		return $AR->DB_list[$this->key]->affectedRows();
	}

	public function _errorNative() {
		global $AR;
		return $AR->DB_list[$this->key]->errorNative();
	}

	public function _nextId($seq_name, $ondemand = true) {
		global $AR;
		return $AR->DB_list[$this->key]->nextId($seq_name, $ondemand);
	}

	public function _createSequence($seq_name) {
		global $AR;
		return $AR->DB_list[$this->key]->createSequence($seq_name);
	}

	public function _dropSequence($seq_name) {
		global $AR;
		return $AR->DB_list[$this->key]->dropSequence($seq_name);
	}

	public function _tableInfo($result, $mode = null) {
		global $AR;
		return $AR->DB_list[$this->key]->tableInfo($result, $mode);
	}

	public function _getListOf($type) {
		global $AR;
		return $AR->DB_list[$this->key]->getListOf($type);
	}

	public function _getSequenceName($sqn) {
		global $AR;
		return $AR->DB_list[$this->key]->getSequenceName($sqn);
	}

	public function _disconnect() {
		global $AR;
		return $AR->DB_list[$this->key]->disconnect();
	}

	public function _errorMessage($dbcode) {
		return DB::errorMessage($dbcode);
	}

	public function _isError($value) {
		return DB::isError($value);
	}

	public function _isWarning($value) {
		return DB::isWarning($value);
	}

	public function _isManip($query) {
		return DB::isManip($query);
	}

}

class pinp_DB_result {
	protected $key;

	public function __construct($key) {
		$this->key=$key;
	}

	public function _fetchRow($fetchmode = DB_FETCHMODE_DEFAULT, $rownum=null) {
		global $AR;
		return $AR->DB_result_list[$this->key]->fetchRow($fetchmode, $rownum);
	}

	public function _fetchInto(&$arr, $fetchmode = DB_FETCHMODE_DEFAULT, $rownum=null) {
		global $AR;
		return $AR->DB_result_list[$this->key]->fetchInto($arr, $fetchmode, $rownum);
	}

	public function _numCols() {
		global $AR;
		return $AR->DB_result_list[$this->key]->numCols();
	}

	public function _numRows() {
		global $AR;
		return $AR->DB_result_list[$this->key]->numRows();
	}

	public function _nextResult() {
		global $AR;
		return $AR->DB_result_list[$this->key]->nextResult();
	}

	public function _free() {
		global $AR;
		return $AR->DB_result_list[$this->key]->free();
	}

	public function _getRowCounter() {
		global $AR;
		return $AR->DB_result_list[$this->key]->getRowCounter();
	}
}

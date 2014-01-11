<?php

require_once('DB.php'); // PEAR DB class

class pinp_DB {

	function pinp_DB($key) {
		$this->key=$key;
	}

	function _connect($dsn, $options = false) {
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

	function _quoteString($string) {
		global $AR;
		return $AR->DB_list[$this->key]->quoteString($string);
	}

	function _quote($string) {
		global $AR;
		return $AR->DB_list[$this->key]->quote($string);
	}

	function _provides($feature) {
		global $AR;
		return $AR->DB_list[$this->key]->provides($feature);
	}

	function _setFetchMode($fetchmode, $object_class = null) {
		global $AR;
		return $AR->DB_list[$this->key]->setFetchMode($fetchmode, $object_class);
	}

	function _setOption($option, $value) {
		global $AR;
		return $AR->DB_list[$this->key]->setOption($option, $value);
	}

	function _getOption($option) {
		global $AR;
		return $AR->DB_list[$this->key]->getOption($option);
	}

	function _prepare($query) {
		global $AR;
		return $AR->DB_list[$this->key]->prepare($query);
	}

	function _execute($stmt, $data = false) {
		global $AR;
		$result=$AR->DB_list[$this->key]->execute($stmt, $data);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	function _executeMultiple( $stmt, &$data ) {
		global $AR;
		return $AR->DB_list[$this->key]->executeMultiple( $stmt, $data );
	}

	function &_query($query, $params = array()) {
		global $AR;
		$result = $AR->DB_list[$this->key]->query($query, $params);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	function _limitQuery($query, $from, $count) {
		global $AR;
		$result = $AR->DB_list[$this->key]->limitQuery($query, $from, $count);
		if (is_object($result) && (get_class($result)=="db_result" || is_subclass_of($result, "db_result")) ) {
			$key=@count($AR->DB_result_list);
			$AR->DB_result_list[$key]=$result;
			$result=new pinp_DB_result($key);
		}
		return $result;
	}

	function &_getOne($query, $params = array()) {
		global $AR;
		return $AR->DB_list[$this->key]->getOne($query, $params);
	}

	function &_getRow($query, $params = null, $fetchmode = DB_FETCHMODE_DEFAULT) {
		global $AR;
		return $AR->DB_list[$this->key]->getRow($query, $params, $fetchmode);
	}

	function &_getCol($query, $col = 0, $params = array()) {
		global $AR;
		return $AR->DB_list[$this->key]->getCol($query, $col, $params);
	}

	function &_getAssoc($query, $force_array = false, $params = array(), 
						$fetchmode = DB_FETCHMODE_ORDERED, $group = false) {
		global $AR;
		return $AR->DB_list[$this->key]->getAssoc($query, $force_array, $params, $fetchmode, $group);
	}

	function &_getAll($query, $params = null, $fetchmode = DB_FETCHMODE_DEFAULT) {
		global $AR;
		return $AR->DB_list[$this->key]->getAll($query, $params, $fetchmode);
	}

	function _autoCommit($onoff=false) {
		global $AR;
		return $AR->DB_list[$this->key]->autoCommit($onoff);
	}

	function _commit() {
		global $AR;
		return $AR->DB_list[$this->key]->commit();
	}

	function _rollback() {
		global $AR;
		return $AR->DB_list[$this->key]->rollback();
	}

	function _numRows($result) {
		global $AR;
		return $AR->DB_list[$this->key]->numRows($result);
	}

	function _affectedRows() {
		global $AR;
		return $AR->DB_list[$this->key]->affectedRows();
	}

	function _errorNative() {
		global $AR;
		return $AR->DB_list[$this->key]->errorNative();
	}

	function _nextId($seq_name, $ondemand = true) {
		global $AR;
		return $AR->DB_list[$this->key]->nextId($seq_name, $ondemand);
	}

	function _createSequence($seq_name) {
		global $AR;
		return $AR->DB_list[$this->key]->createSequence($seq_name);
	}

	function _dropSequence($seq_name) {
		global $AR;
		return $AR->DB_list[$this->key]->dropSequence($seq_name);
	}

	function _tableInfo($result, $mode = null) {
		global $AR;
		return $AR->DB_list[$this->key]->tableInfo($result, $mode);
	}

	function _getListOf($type) {
		global $AR;
		return $AR->DB_list[$this->key]->getListOf($type);
	}

	function _getSequenceName($sqn) {
		global $AR;
		return $AR->DB_list[$this->key]->getSequenceName($sqn);
	}

	function _disconnect() {
		global $AR;
		return $AR->DB_list[$this->key]->disconnect();
	}

	function _errorMessage($dbcode) {
		return DB::errorMessage($dbcode);
	}

	function _isError($value) {
		return DB::isError($value);
	}

	function _isWarning($value) {
		return DB::isWarning($value);
	}

	function _isManip($query) {
		return DB::isManip($query);
	}

}

class pinp_DB_result {
	var $key;

	function pinp_DB_result($key) {
		$this->key=$key;
	}

	function _fetchRow($fetchmode = DB_FETCHMODE_DEFAULT, $rownum=null) {
		global $AR;
		return $AR->DB_result_list[$this->key]->fetchRow($fetchmode, $rownum);
	}

	function _fetchInto(&$arr, $fetchmode = DB_FETCHMODE_DEFAULT, $rownum=null) {
		global $AR;
		return $AR->DB_result_list[$this->key]->fetchInto($arr, $fetchmode, $rownum);
	}

	function _numCols() {
		global $AR;
		return $AR->DB_result_list[$this->key]->numCols();
	}

	function _numRows() {
		global $AR;
		return $AR->DB_result_list[$this->key]->numRows();
	}

	function _nextResult() {
		global $AR;
		return $AR->DB_result_list[$this->key]->nextResult();
	}

	function _free() {
		global $AR;
		return $AR->DB_result_list[$this->key]->free();
	}

	function _getRowCounter() {
		global $AR;
		return $AR->DB_result_list[$this->key]->getRowCounter();
	}
}

?>
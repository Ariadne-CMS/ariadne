<?php
/******************************************************************************
  Generic Store 1.0						Ariadne

  Copyright (C) 1998-2005  Muze

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

  --------------------------------------------------------------------------

  This Is a Generic implementation of the store, all generic functions are defined here

  the implemented functions are

	function get_config($field)
	function is_supported($feature)
	function newobject($path, $parent, $type, $data, $id=0, $lastchanged=0, $vtype="", $size=0, $priority=0)
	function close()
	function make_path($curr_dir, $path)
	function save_properties($properties, $id)
	function get_filestore($name)

*******************************************************************************/


abstract class store {

	public $error;
	public $root;
	public $rootoptions;
	public $mod_lock;
	public $total;
	protected $code;
	protected $files;
	protected $_filestores;
	protected $config;



	public function __construct($path, $config) {
		echo "You have not configured the store properly. Please check your configuration files.";
		exit();
	}

	/*  abstract functions
		 need implementation in your implementation of this store
	*/

	public abstract function call($template, $args, $objects);

	public abstract function count($objects);

	public abstract function info($objects);

	public abstract function get($path);
	/**********************************************************************************
	 This function takes as argument a path to an object in the store and will retrieve
	 all the necessary data and return this in the objectlist type needed for
	 store->call(). If the requested path does not exist, it will retrieve the object
	 with the longest matching path.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function touch($id, $timestamp = -1);
	/**********************************************************************************
	 This function takes as argument a path to an object (or id of an object)
     in the store and will set the timestamp to $timestamp.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function ls($path);
	/**********************************************************************************
	 This function takes as argument a path to an object in the store and will retrieve
	 all the objects and their data which have this object as their parent. It will
	 then return this in the objectlist type needed for store->call(). If the requested
	 path does not exist, it will retrieve the object with the longest matching path.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function parents($path, $top="/");
	/**********************************************************************************
	 This function takes as argument a path to an object in the store. It will return
	 all objects with a path which is a substring of the given path. The resulsts are
	 ordered by path (length), shortest paths first.
	 In effect all parents of an object in the tree are called, in order, starting at
	 the root.

	 $path should always start and end with a '/'.
	 **********************************************************************************/

	public abstract function find($path, $criteria, $limit=100, $offset=0);
	/**********************************************************************************
	 This function takes as arguments a path to an object in the store and some search
	 criteria. It will search for all matching objects under the given path. If the
	 given path is not in this store but in a substore it will not automatically search
	 that substore.

	 $criteria is of the form

	 $criteria ::= ({ $property_name => ({ $valuename => ({ $compare_function, $value }) }) })

	 e.g.: $criteria["status"]["value"][">"]="published";

	 $path should always start and end with a '/'.

	 **********************************************************************************/


	public abstract function save($path, $type, $data, $properties="", $vtype="", $priority=false);
	/***************************************************************
		This function takes as argument a path, type, objectdata and
		possibly a properties list and vtype (virtual type).
		If there exists no object with the given path, a new object is
		saved with the given type, data, properties and vtype, and a
		new path is saved pointing to it.
		If there does exist an object with the given path, it's object
		data is overwritten with the given data and if vtype is set the
		current vtype is overwritten with the new one.

		$path must be an absolute path (containing no '..' and starting
			with '/')
		$type must be a valid type
		$data can be any string (usually a serialized object.)
		$properties is a multidimensional hash of the following form:
			$properties[{property_name}][][{value_name}]={value}
			{property_name} must be a valid property name
			{value_name} must be a valid value name for this property
			{value} can be a number, boolean or string.
		example:
			$properties["name"][0]["value"]="A name";
			$properties["name"][1]["value"]="A second name!";
		if $properties["name"]=1 then all properties for property name
			will be removed.

		$vtype must be a valid type.

		if $properties or $vtype are not set or empty ("",0 or false)
		they will be ignored. $vtype defaults to $type.
		Only those properties listed in $properties will be updated.
		Any other property set will remain as it was.
	***************************************************************/


	protected abstract function purge($path);
	/**********************************************************************
		This function will delete the object pointed to by $path and all
	other paths pointing to that object. It will then remove any property
	for this object from all property tables.
		The function returns the number of paths found and removed or 1 if
	there was no path found (meaning that the object doesn't exist and
	therefor purge succeeded while doing nothing.)

	 $path should always start and end with a '/'.
	**********************************************************************/

	public abstract function delete($path);
	/**********************************************************************
		This function deletes the path given. If this is the last path pointing
	to an object, the object will be purged instead.

	$path should always start and end with a '/'.
	**********************************************************************/

	abstract function exists($path);
	/**********************************************************************
		This function checks the given path to see if it exists. If it does
	it returns the id of the object to which it points. Otherwise it returns
	0.

	$path should always start and end with a '/'.
	**********************************************************************/


	public abstract function link($source, $destination);
	/**********************************************************************
		Link adds an extra path to an already existing object. It has two
	arguments: $source and $destination. $source is an existing path of
	an object, $destination is the new path. $destination must not already
	exist.

	$destination should always start and end with a '/'.
	**********************************************************************/

	public abstract function move($source, $destination);
	/**********************************************************************
	$destination should always start and end with a '/'.
	**********************************************************************/


	public abstract function list_paths($path);
	/**********************************************************************
		This function returns an array of all paths pointing to the same object
	as $path does.
	**********************************************************************/

	public abstract function AR_implements($type, $implements);
	/**********************************************************************
		This function returns 1 if the $type implements the type or
	interface in $implements. Otherwise it returns 0.
	**********************************************************************/

	public abstract function load_properties($object, $values="");

	public abstract function load_property($object, $property, $values="");

	public abstract function add_property($object, $property, $values);

	public abstract function del_property($object, $property="", $values="");

	protected abstract function get_nextid($path, $mask="{5:id}");
	/**********************************************************************
		'private' function of mysql store. This will return the next
		'autoid' for $path.
	**********************************************************************/



	/*
		Implemented functions
	*/

	public function get_config($field) {
		switch ($field) {
			case 'code':
			case 'files':
			case 'root':
			case 'rootoptions':
				$result = $this->$field;
				break;
			default:
				$result =  null;
				debug("store::get_config: undefined field $field requested","store");
				break;
		}
		return $result;
	}

	public function is_supported($feature) {
	/**********************************************************************************
		This function takes as argument a feature description and returns
		true if this feature is supported and false otherwise
	**********************************************************************************/
		$result = false;
		switch($feature) {
			// features depending on config values
			case 'fulltext_boolean':
			case 'fulltext':
				if ($this->config[$feature]) {
					$result = true;
				} else {
					$result = false;
				}
			break;
			// features depending store implementation, if stores don't implements this, they have to override this function
			case 'grants':
			case 'regexp':
				$result = true;
			break;
		}
		return $result;
	}

	/**********************************************************************************
		This functions creates a new ariadne object
	**********************************************************************************/
	public function newobject($path, $parent, $type, $data, $id=0, $lastchanged=0, $vtype="", $size=0, $priority=0) {
		global $ARnls;
		$class = $type;
		if ($subcpos = strpos($type, '.')) {
			$class = substr($type, 0, $subcpos);
			$vtype = $class;
		}
		if (!class_exists($class, false)) {
			include_once($this->code."objects/".$class.".phtml");
		}
		$object=new $class;
		$object->type=$type;
		$object->parent=$parent;
		$object->id=(int)$id;
		$object->lastchanged=(int)$lastchanged;
		$object->vtype=$vtype;
		$object->size=(int)$size;
		$object->priority=(int)$priority;
		$object->ARnls = $ARnls;
		$object->init($this, $path, $data);
		return $object;
	}

	public function close() {
		// This is the destructor function, nothing much to see :)
		if (is_array($this->_filestores)) {
			foreach ($this->_filestores as $filestore) {
				$filestore->close();
			}
		}
	}

	public function __destruct() {
		$this->close();
	}

	public function make_path($curr_dir, $path) {
		return \arc\path::collapse($path, $curr_dir);
	}

	public function save_properties($properties, $id) {
	/********************************************************************
		'private' function of mysql.phtml. It updates all property tables
		defined in $properties and sets the values to the values in
		$properties.
	********************************************************************/

		if ($properties && (is_array($properties)) && (is_numeric($id))) {
			foreach ( $properties as $property => $property_set ) {
				$this->del_property((int)$id, $property);
				if (is_array($property_set)) {
					$property_set = array_unique($property_set,SORT_REGULAR);
					foreach ( $property_set as $values ) {
						$this->add_property((int)$id, $property, $values);
					}
				}
			}
		}
	}


	public function get_filestore($name) {
		global $AR;
		if (!$this->_filestores[$name]) {
			if ($AR->SVN->enabled && ($name == "templates")) {
				require_once($this->code."modules/mod_filestore_svn.phtml");
				$this->_filestores[$name]=new filestore_svn($name, $this->files, $this);
			} else {
				require_once($this->code."modules/mod_filestore.phtml");
				$this->_filestores[$name]=new filestore($name, $this->files, $this);
			}
		}
		return $this->_filestores[$name];
	}

	public function get_filestore_svn($name) {
		require_once($this->code."modules/mod_filestore_svn.phtml");
		if (!$this->_filestores["svn_" . $name]) {
			$this->_filestores["svn_" . $name] = new filestore_svn($name, $this->files, $this);
		}
		return $this->_filestores["svn_" . $name];
	}

	protected function compilerFactory(){
		switch($this->config["dbms"]){
			case 'axstore':
				return false;
			default:
				$compiler = $this->config["dbms"].'_compiler';
				return new $compiler($this,$this->tbl_prefix);
		}
	}

	protected function serialize($value, $path) {
		if ($value->failedDecrypt && $value->originalData) {
			$value = $value->originalData;
			return $value;
		}

		// Notice: When upgrading to a new crypto format, prepend the object data with a key of the crypto. This way you can be backwards compatible but new objects will be saved in the new crypto.
		if ($this->config['crypto'] instanceof \Closure) {
			$crypto = $this->config['crypto']();
			// use the last crypto configured;
			$cryptoConfig = end($crypto);
			if (is_array($cryptoConfig['paths'])) {
				foreach ($cryptoConfig['paths'] as $cryptoPath) {
					if (strpos($path, $cryptoPath) === 0) {
						$value->ARcrypted = true;
						switch ($cryptoConfig['method']) {
							case 'ar_crypt':
								$key = base64_decode($cryptoConfig['key']);
								$crypto = new ar_crypt($key,$cryptoConfig['crypto'],1);
								$cryptedValue = $crypto->crypt(serialize($value));
								if($cryptedValue !== false ) {
									return $cryptoConfig['token'] . ":" . $cryptedValue;
								}
							break;
							default:
							break;
						}

					}
				}
			}
		}
		unset($value->ARcrypted);
		return serialize($value);
	}

	private function fixObjectClass($value) {
		return str_replace('O:6:"object"', 'O:8:"stdClass"', $value);
	}

	protected function unserialize($value, $path) {
		if ($value[0] === "O" && $value[1] === ":") {
			return unserialize(self::fixObjectClass($value));
		} else if ($this->config['crypto'] instanceof \Closure) {
			$crypto = $this->config['crypto']();
			list($token,$datavalue) = explode(':', $value, 2);
			foreach ($crypto as $cryptoConfig) {
				$cryptoToken = $cryptoConfig['token'];
				if ($token === $cryptoToken ) {
					$value = $datavalue;
					switch ($cryptoConfig['method']) {
						case 'ar_crypt':
							$key = base64_decode($cryptoConfig['key']);
							$crypto = new ar_crypt($key,$cryptoConfig['crypto'],1);
							$decryptedValue =  $crypto->decrypt($value);
						break;
						default:
						break;
					}
				}
			}

			if ($decryptedValue[0] === "O" && $decryptedValue[1] === ":") {
				return unserialize(self::fixObjectClass($decryptedValue));
			} else {
				$dummy = unserialize('O:8:"stdClass":7:{s:5:"value";s:0:"";s:3:"nls";O:8:"stdClass":2:{s:7:"default";s:2:"nl";s:4:"list";a:1:{s:2:"nl";s:10:"Nederlands";}}s:2:"nl";O:8:"stdClass":1:{s:4:"name";s:14:"Crypted object";}s:6:"config";O:8:"stdClass":2:{s:10:"owner_name";s:6:"Nobody";s:5:"owner";s:6:"nobody";}s:5:"mtime";i:0;s:5:"ctime";i:0;s:5:"muser";s:6:"nobody";}');
				$dummy->failedDecrypt = true;
				$dummy->originalData = $value;
				return $dummy;
			}
		}
	}

} 

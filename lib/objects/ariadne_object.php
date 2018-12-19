<?php
    /******************************************************************
     pobject.phtml                                         Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 31 october 2002

     Copyright 2002 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published
     by the Free Software Foundation; either version 2 of the License,
     or (at your option) any later version.

     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
     02111-1307  USA

    -------------------------------------------------------------------

     Class inheritance: 	pobject
     Description:

       This is the class definition file of the pobject class.

    ******************************************************************/

debug("pobject: Load","object");

abstract class ariadne_object extends baseObject { // ariadne_object class definition

	public $store;
	public $path;
	public $data;

	public function init($store, $path, $data) {
		$this->store=$store;
		$this->path=$path;
		$this->data=$data;
		if ( !isset($this->data->config) ) {
			$this->data->config = new baseObject();
		}
	}

	public function call($arCallFunction="view.html", $arCallArgs=array()) {
	/***********************************************************************
	  call tries to find the template ($arCallFunction) for the current
	  object. If it is not defined there, call will search the superclasses
	  until either it or the template 'default.phtml' is found.

	  $arCallFunction must be the name of a class or object template.
	    it can be prepended with "{classname}::". classname must be either
	    a valid ariadne class (derived from pobject). call()
	    will then try to find the template starting at the given classname.
		e.g.:
		call("pobject::view.html") will show the view.html template of
	      pobject, with the data of the current object.

	  variables available to templates:
	  local: arCallFunction, arCallArgs, arCallTemplate, data
	  global: AR, ARConfig, ARCurrent, ARBeenHere, ARnls
	***********************************************************************/
	global $AR, $ARConfig, $ARCurrent, $ARBeenHere, $ARnls;

		if ( $arCallFunction instanceof \Closure ) {
			$arCallFunctionName = 'Closure';
		} else {
			$arCallFunctionName = (string) $arCallFunction;
		}
		debug("pobject: ".$this->path.": call($arCallFunctionName, ".debug_serialize($arCallArgs).")","object","all","IN");

		// default to view.html
		if (!$arCallFunction) {
			$arCallFunction="view.html";
		}
		// clear previous results
		unset($ARCurrent->arResult);

		// callstack is needed for getvar()
		$ARCurrent->arCallStack[]=&$arCallArgs;
		// keep track of the context (php or pinp) in which the called template runs. call always sets it php, CheckConfig sets it to pinp if necessary.
		$this->pushContext( array(
			"arSuperContext" => array(),
			"arCurrentObject" => $this,
			"scope" => "php",
			"arCallFunction" => $arCallFunction
		) );

		// convert the deprecated urlencoded arguments to an array
		if (isset($arCallArgs) && is_string($arCallArgs)) {
			$ARCurrent->arTemp=$arCallArgs;
			$arCallArgs=array();
			parse_str($ARCurrent->arTemp, $arCallArgs);
		}
		// import the arguments in the current scope, but don't overwrite existing
		// variables.
		if (isset($arCallArgs) && is_array($arCallArgs)) {
			extract($arCallArgs,EXTR_SKIP);
		}
		// now find the initial nls selection (CheckConfig is needed for per
		// tree selected defaults)
		if ($ARCurrent->nls) {
			$this->reqnls=$ARCurrent->nls;
		} else if (isset($ARConfig->cache[$this->path]) && $ARConfig->cache[$this->path]->nls->default) {
			$this->reqnls = $ARConfig->cache[$this->path]->nls->default;
		} else {
			$this->reqnls=$AR->nls->default;
		}
		if (isset($this->data->nls->list[$this->reqnls]) || !isset($this->data->nls)) {
			// the requested language is available
			$this->nls=$this->reqnls;
			$nls=&$this->nls;
		} else {
			// the requested language is not available, use default of the
			// current object instead.
			$this->nls=$this->data->nls->default;
			$nls=&$this->nls;
		}
		if ($nls && isset($this->data->$nls)) {
			// now set the data and nlsdata pointers
			$this->nlsdata=$this->data->$nls;
			$nlsdata=&$this->nlsdata;
			$data=&$this->data;
		} else {
			// this object doesn't support nls data
			$this->nlsdata=$this->data;
			$nlsdata=&$this->data;
			$data=&$this->data;
		}
		if (isset($this->data->custom['none'])) {
			$customdata=$this->data->custom['none'];
		}
		if (isset($this->data->custom[$nls])) {
			$customnlsdata=$this->data->custom[$nls];
		}

		$arCallFunctionOrig = $arCallFunction;
		if (strpos($arCallFunctionName,"::")!==false) {
			// template of a specific class defined via call("class::template");
			list($arType, $arCallFunction)=explode("::",$arCallFunctionName);
			$temp = explode(":", $arType );
			if( count($temp) > 1 ) {
				$libname = $temp[0];
				$arType = $temp[1];
				$arCallFunction = $libname.":".$arCallFunction;
			}
		} else {
			$arType=$this->type;
		}

		if ( $arCallFunction instanceof \Closure ) {
			$context = $this->getContext(ARCALLINGCONTEXT);
			if ( $context["scope"] != "pinp" ) {
				$arResult = $arCallFunction($this );
			} else {
				if ( $this->CheckSilent('read') ) {
					$arResult = $arCallFunction($this);
				}
			}
		} else {
			if ($arCallFunction[0] === "#") {
				$ARCurrent->arCallClassTemplate = true;
				$arCallFunction = substr($arCallFunction, 1);
			} else {
				$ARCurrent->arCallClassTemplate = false;
			}

			if( $arCallFunction == "system.get.phtml" && ( $context = $this->getContext(ARCALLINGCONTEXT) ) && $context["scope"] != "pinp" ) {
				$arResult = $this;
			} else {
				$libtemplate = strpos($arCallFunction,":");
				$codedir = $this->store->get_config("code");

				// if it is a subtype object, disk templates do not exists,
				$subcpos = strpos($arType, '.');
				if ($subcpos !== false ) {
					// subtype, skip looking for templates
					$arSuper = substr($arType, 0, $subcpos);
					if(!isset($AR->superClass[$arType])){
						$AR->superClass[$arType]=$arSuper;
					}
					$arType=$arSuper;
				}

				while ($arType !== "ariadne_object") {

					// search for the template, stop at the root class ('ariadne_object')
					// (this should not happen, as pobject must have a 'default.phtml')
					$arCallTemplate=$codedir."templates/".$arType."/".$arCallFunction;
					if ($libtemplate === false && file_exists($arCallTemplate)) {
						//debug('found '.$arCallTemplate, 'all');
						// template found
						$arCallFunction = $arCallFunctionOrig;
						include($arCallTemplate);
						break;
					} else if (file_exists($codedir."templates/".$arType."/default.phtml")) {
						//debug('found default.phtml', 'all');
						// template not found, but we did find a 'default.phtml'
						include($this->store->get_config("code")."templates/".$arType."/default.phtml");
						break;
					} else {
						if (!($arSuper=$AR->superClass[$arType])) {
							// no template found, no default.phtml found, try superclass.

							if (!class_exists($arType, false)) {
								// the given class was not yet loaded, so do that now
								$this->store->newobject('','',$arType,new baseObject);
							}
							$arSuper=get_parent_class($arType);

							$AR->superClass[$arType]=$arSuper;
						}
						$arType=$arSuper;
					}
				}
			}
		}
		array_pop($ARCurrent->arCallStack);
		$this->popContext();
		debug("pobject: call: end","all","all","OUT");
		if (isset($ARCurrent->arResult)) {
			// pinp templates can return results via putvar("arResult",$result);
			$arResult=$ARCurrent->arResult;
			unset($ARCurrent->arResult);
		}
		if (isset($arResult)) {
			// only do a return if we really have something to return
			return $arResult;
		}
	}

	public function ls($path="", $function="list.html", $args="") {
		$path=$this->store->make_path($this->path, $path);
		return $this->store->call($function, $args, $this->store->ls($path));
	}

	public function get($path, $function="view.html", $args="") {
		$path=$this->store->make_path($this->path, $path);
		return $this->store->call($function, $args, $this->store->get($path));
	}

	public function parents($path, $function="list.html", $args="", $top="") {
		/* FIXME: $this->store->parents is too slow when a lot of objects are in ariadne (2million+) */
		/* but this fix should be done in the store, not here */
		if (!$top) {
			$top = $this->currentsection();
		} else {
			$top = $this->store->make_path($this->path, $top);
		}

		$path=$this->store->make_path($this->path, $path);

		if ($path != $this->path ) {
			$target = current($this->get($path,"system.get.phtml"));
		} else {
			$target = $this;
		}

		$parents = array();
		if (strpos($target->path, $top) === 0) {
			$parents[] = $target;
			while ($target && $target->path != $top) {
				$target = current($target->get($target->parent, "system.get.phtml"));
				$parents[] = $target;
			}
		}
		$parents = array_reverse($parents);
		$result = array();
		foreach ($parents as $parent) {
			if ( $parent ) { // might not have read access to this object
				$result[] = $parent->call($function, $args);
			}
		}

		return $result;
	}

	public function find($path, $criteria, $function="list.html", $args="", $limit=100, $offset=0) {
		$path = $this->store->make_path($this->path, $path);
		$objects = $this->store->find($path, $criteria, $limit, $offset);
		if (!$this->store->error) {
			$result = $this->store->call($function, $args, $objects);
		} else {
			$this->error = ar::error( ''.$this->store->error, 1110, $this->store->error );
			$result = false;
		}
		return $result;
	}

	public function count_find($path='', $query='') {
		$path=$this->store->make_path($this->path, $path);
		if (method_exists($this->store, 'count_find')) {
			$result = $this->store->count_find($path, $query, 0);
		} else {
			$result = $this->store->count($this->store->find($path, $query, 0));
		}
		return $result;
	}

	public function count_ls($path) {
		return $this->store->count($this->store->ls($path));
	}

	private function saveMergeWorkflowResult($properties, $wf_result) {
		foreach ($wf_result as $wf_prop_name => $wf_prop) {
			foreach ($wf_prop as $wf_prop_index => $wf_prop_record) {
				if (!isset($wf_prop_record)) {
					unset($properties[$wf_prop_name][$wf_prop_index]);
				} else {
					$record = array();
					foreach ($wf_prop_record as $wf_prop_field => $wf_prop_value) {
						switch (gettype($wf_prop_value)) {
							case "integer":
							case "boolean":
							case "double":
								$value = $wf_prop_value;
								break;
							default:
								$value = $wf_prop_value;
								// backwards compatibility, store will do the escaping from now on
								// will be removed in the future
								if (substr($wf_prop_value, 0, 1) === "'" && substr($wf_prop_value, -1) === "'"
										&& "'".AddSlashes(StripSlashes(substr($wf_prop_value, 1, -1)))."'" == $wf_prop_value) {
									$value = stripSlashes(substr($wf_prop_value,1,-1));
									// todo add deprecated warning
								}

						}
						$record[$wf_prop_field] = $value;
					}
					$properties[$wf_prop_name][] = $record;
				}
			}
		}

		return $properties;
	}

	/*
		saves custom data
		returns properties for custom data
	*/
	private function saveCustomData($configcache, $properties) {
		$custom = $this->getdata("custom", "none");
		@parse_str($custom);
		if (isset($custom) && is_array($custom)) {
			foreach($custom as $nls=>$entries){
				if (isset($entries) && is_array($entries)) {
					foreach ( $entries as $customkey => $customval ){
						$this->data->custom[$nls][$customkey] = $customval;
					}
				}
			}
		}
		// the above works because either $custom comes from the form entry, and parse_str returns an
		// array with the name $custom, or $custom comes from the object and is an array and as such
		// parse_str fails miserably thus keeping the array $custom intact.

		if (isset($this->data->custom) && is_array($this->data->custom)) {
			foreach($this->data->custom as $nls => $cdata) {
				foreach($cdata as $name => $value){
					// one index, this order (name, value, nls) ?
					if ($configcache->custom[$name]['containsHTML']) {
						$this->_load('mod_url.php');
						$value = URL::RAWtoAR($value, $nls);
						$this->data->custom[$nls][$name] = $value;
					}
					if ($configcache->custom[$name]['property']) {
						if (isset($value) && is_array($value)) {
							foreach($value as $valkey => $valvalue ) {
								$properties["custom"][] = [
									"name"  => $name,
									"value" => $valvalue,
									"nls"   => $nls,
								];
							}
						} else {
							$properties["custom"][] = [
								"name"  => $name,
								"value" => $value,
								"nls"   => $nls,
							];

						}
					}
				}
			}
		}
		return $properties;
	}

	public function save($properties=array(), $vtype="") {
	/***********************************************************************
	  save the current object.
	  if this is a new object ($this->arIsNewObject) the path is checked and
	  the object is saved under the new path.
	***********************************************************************/
	global $AR, $ARnls, $ARCurrent;
		debug("pobject: save([properties], $vtype)","object");
		debug("pobject: save: path=".$this->path,"object");
		$configcache=$this->loadConfig();
		$needsUnlock = false;
		$arIsNewObject = false;
		$result = false;
		$this->error = '';
		if ($this->arIsNewObject) { // save a new object
			debug("pobject: save: new object","all");
			$this->path = $this->make_path();
			$arNewParent=$this->make_path("..");
			$arNewFilename=basename($this->path);
			$arIsNewObject = true;
			if (preg_match("|^[a-z0-9_\{\}\.\:-]+$|i",$arNewFilename)) { // no "/" allowed, these will void the 'add' grant check.
				if (!$this->exists($this->path)) { //arNewFilename)) {
					if ($this->exists($arNewParent)) {
						if (!$config = $this->data->config) {
							$config=new baseObject();
						}
					} else {
						$this->error = ar::error( sprintf($ARnls["err:noparent"],$arNewParent), 1102);
					}
				} else {
					$this->error = ar::error( sprintf($ARnls["err:alreadyexists"],$arNewFilename), 1103);
				}
			} else {
				$this->error = ar::error( sprintf($ARnls["err:fileillegalchars"],$arNewFilename), 1104);
			}
		} else { // existing object
			debug("pobject: save: existing object","all");
			if ($this->exists($this->path)) { // prevent 'funny stuff'
				if (!$this->lock()) {
					$this->error = ar::error( $ARnls["err:objectalreadylocked"], 1105);
				} else {
					$needsUnlock = true;
					$config = $this->data->config;
				}
			} else {
				$this->error = ar::error($ARnls["err:corruptpathnosave"], 1106);
			}
		}
		// pre checks done
		// return now on error
		if ($this->error) {
			return $result;;
		}


		if ($ARCurrent->arCallStack) {
			$arCallArgs = end($ARCurrent->arCallStack);
		} else {
			$arCallArgs = array();
		}

		$context = $this->getContext();

		$wf_object = $this->store->newobject($this->path, $this->parent, $this->type, $this->data, $this->id, $this->lastchanged, $this->vtype, 0, $this->priority);
		if ( $arIsNewObject) {
			$wf_object->arIsNewObject=$arIsNewObject;
		}

		/* save custom data */
		$properties = $this->saveCustomData($configcache, $properties);

		// this makes sure the event handlers are run on $wf_object, so that $this->data changes don't change the data of the object to be saved
		$this->pushContext(array('scope' => 'php', 'arCurrentObject' => $wf_object));

		$eventData = new baseObject();
		$eventData->arCallArgs = $arCallArgs;
		$eventData->arCallFunction	= $context['arCallFunction'];
		$eventData->arIsNewObject = $arIsNewObject;
		$eventData->arProperties = $properties;
		$eventData = ar_events::fire( 'onbeforesave', $eventData );

		// pop the wf_object, not needed later, the extra scope might hinder other code
		$this->popContext();

		if ( !$eventData ) {
			return false; // prevent saving of the object.
		}

		// arguments can be altered by event handlers, only usefull when a workflow template is also defined
		$arCallArgs = $eventData->arCallArgs;

		// the properties from the eventData are the new property list
		// no need to merge them with $properties, just manipulate the properties array directly
		// in the event data. unlike the user.workflow.pre.html template
		if (isset( $eventData->arProperties ) && is_array( $eventData->arProperties ) ) {
			$properties = $eventData->arProperties;
		} else {
			$properties = array();
		}

		// pass the current properties list to the workflow template
		// for backwards compatibility and workflow templates that just
		// returned only their own properties, merge them afterwards
		// don't do this for the eventData arProperties!
		$arCallArgs['properties'] = $properties;
		$wf_result = $wf_object->call("user.workflow.pre.html", $arCallArgs);
		/* merge workflow properties */
		if (isset($wf_result) && is_array($wf_result) ){
			$properties = $this->saveMergeWorkflowResult($properties,$wf_result);
		}

		$this->error = $wf_object->error;
		$this->priority = $wf_object->priority;
		$this->data = $wf_object->data;
		$this->data->config = $config;
		$this->data->mtime=time();
		if($arIsNewObject) {
			$this->data->ctime=$this->data->mtime;
		}

		$this->data->muser=$AR->user->data->login;
		if( !$this->data->config->owner ) {
			if( !$this->data->config->owner_name) {
				$this->data->config->owner_name=$AR->user->data->name;
			}
			$this->data->config->owner=$AR->user->data->login;
			$properties["owner"][0]["value"]=$this->data->config->owner;
		}
		$properties["time"][0]["ctime"]=$this->data->ctime;
		$properties["time"][0]["mtime"]=$this->data->mtime;
		$properties["time"][0]["muser"]=$this->data->muser;


		if (!$this->error) {
			if ($this->path=$this->store->save($this->path, $this->type, $this->data, $properties, $vtype, $this->priority)) {
				unset($this->arIsNewObject);
				$this->id=$this->exists($this->path);
				$result=$this->path;

				$config=$this->data->config; // need to set it again, to copy owner config data

				$wf_object = $this->store->newobject($this->path, $this->parent, $this->type, $this->data, $this->id, $this->lastchanged, $this->vtype, 0, $this->priority);
				$arCallArgs = $eventData->arCallArgs; // returned from onbeforesave event
				$arCallArgs['properties'] = $properties;

				if ($arIsNewObject) {
					$wf_object->arIsNewObject = $arIsNewObject;
				}
				$wf_result = $wf_object->call("user.workflow.post.html", $arCallArgs);
				$this->error = $wf_object->error;
				$this->priority = $wf_object->priority;
				$this->data = $wf_object->data;
				$this->data->config = $config;
				/* merge workflow properties */

				if (isset($wf_result) && is_array($wf_result) ){
					$properties = $this->saveMergeWorkflowResult($properties,$wf_result);

					if (!$this->store->save($this->path, $this->type, $this->data, $properties, $this->vtype, $this->priority)) {
						$this->error = ar::error( ''.$this->store->error, 1108, $this->store->error);
						$result = false;
					}
				}
				// all save actions have been done, fire onsave.
				$this->data->config = $config;

				//$this->ClearCache($this->path, true, false);
				$eventData->arProperties = $properties;
				$this->pushContext(array('scope' => 'php', 'arCurrentObject' => $this));
				ar_events::fire( 'onsave', $eventData ); // nothing to prevent here, so ignore return value
				$this->popContext();
			} else {
				$this->error = ar::error( ''.$this->store->error, 1107, $this->store->error);
				$result = false;
			}
		}
		if( $needsUnlock == true ){
			$this->unlock();
		}

		if ($this->data->nls->list[$this->nls]) {
			$mynlsdata=$this->data->{$this->nls};
		} else if ($this->data->nls->default) {
			$mynlsdata=$this->data->{$this->data->nls->default};
		} else {
			$mynlsdata=$this->data;
		}

		unset($this->nlsdata);
		$this->nlsdata=$mynlsdata;

		debug("pobject: save: end","all");
		return $result;
	}

	public function link($to) {
		return $this->store->link($this->path, $this->make_path($to));
	}

	public function delete() {
	global $ARCurrent;
		$result	= false;
		$this->error = '';
		if ($ARCurrent->arCallStack) {
			$arCallArgs = end($ARCurrent->arCallStack);
		} else {
			$arCallArgs = array();
		}
		$context = $this->getContext();

		$eventData = new baseObject();
		$eventData->arCallArgs = $arCallArgs;
		$eventData->arCallFunction = $context['arCallFunction'];
		$eventData = ar_events::fire( 'onbeforedelete', $eventData );
		if ( !$eventData ) {
			return false;
		}
		$this->call("user.workflow.delete.pre.html", $eventData->arCallArgs);
		if (!$this->error) {
			if ($this->store->delete($this->path)) {
				$result = true;
				$this->call("user.workflow.delete.post.html", $eventData->arCallArgs);
				ar_events::fire( 'ondelete', $eventData );
			} else {
				$this->error = ar::error( ''.$this->store->error, 1107, $this->store->error);
			}
		}
		return $result;
	}

	public function exists($path) {
		$path=$this->make_path($path);
		return $this->store->exists($path);
	}

	public function make_path($path="") {
		switch($path){
			case '':
			case '.':
			case $this->path:
				return $this->path;
				break;
			case '..':
				return $this->parent;
				break;
			default:
				return self::sanitizePath($this->store->make_path($this->path, $path));
		}
	}

	public function make_ariadne_url($path="") {
		global $AR;
		$path = $this->make_path($path);
		return self::sanitizeUrl($AR->host . $AR->root . $this->store->get_config('rootoptions') . $path);
	}


	public function make_url($path="", $nls=false, $session=true, $https=null, $keephost=null) {
		global $ARConfig, $AR, $ARCurrent;

		$rootoptions=$this->store->get_config('rootoptions');
		if (!$session || ($nls !== false)) {
			$rootoptions = "";
			if ($session && isset($ARCurrent->session->id) && !$AR->hideSessionIDfromURL) {
				$rootoptions .= "/-".$ARCurrent->session->id."-";
			}
			if ($nls) {
				$rootoptions_nonls = $rootoptions;
				$rootoptions .= '/'.$nls;
			}
		}
		$path=$this->make_path($path);

		// now run CheckConfig and get the parentsite of the path found
		if (!$temp_config=$ARConfig->cache[$path]) {
			$temp_path = $path;
			while (!($temp_site = $this->currentsite($temp_path)) && $temp_path!='/') {
				$temp_path = $this->make_path($temp_path.'../');
			}
			$temp_config=$ARConfig->cache[$temp_site];
		}

		if ( !isset($keephost) && (
			(!$nls && $this->compare_hosts($AR->host, $temp_config->root["value"])) ||
			($nls && ($this->compare_hosts($AR->host, $temp_config->root['list']['nls'][$nls])))
		)) {
			$keephost = false;
		}

		if (!$keephost) {
			if ($nls) {
				$url=$temp_config->root["list"]["nls"][$nls];
				if (isset($url) && is_array($url)) {
					$url = current( $url );
				}
				if ($url) {
					if (substr($url, -1)=='/') {
						$url=substr($url, 0, -1);
					}
					$url .= $rootoptions_nonls;
				}
			}
			if (!$url) {
				$checkNLS = $nls;
				if (!$checkNLS) {
					$checkNLS = $this->nls;
				}
				$urlList = $temp_config->root['list']['nls'][$checkNLS];
				if (isset($urlList) && is_array($urlList)) {
					$url = reset($urlList) . $rootoptions;
				} else {
					$url = $temp_config->root["value"].$rootoptions;
				}
			}
			$url.=substr($path, strlen($temp_config->root["path"])-1);

			if (is_bool($https)) {
				if ($https) {
					if ($AR->https) {
						$url = preg_replace('/^http:/', 'https:', $url);
					}
				} else {
					$url = preg_replace('/^https:/', 'http:', $url);
				}
			}
		} else {
			$checkNLS = $nls;
			if (!$checkNLS) {
				$checkNLS = $this->nls;
			}
			$urlCheck = $temp_config->root['list']['nls'][$checkNLS];
			if (!is_array($urlCheck)) {
				$urlCheck = $temp_config->root["value"];
			}
			$requestedHost = ldGetRequestedHost();
			if ($this->compare_hosts($requestedHost, $urlCheck)) {
				$url = $requestedHost . $rootoptions;
				$url .= substr($path, strlen($temp_config->root["path"])-1);
			} else {
				//$url=$AR->host.$AR->root.$rootoptions.$path;
				$url = $protocol . $requestedHost . $AR->root . $rootoptions . $path;
			}
		}
		return self::sanitizeUrl($url);
	}

	protected function compare_hosts($url1, $url2) {
		// Check if hosts are equal, so that http://www.muze.nl and //www.muze.nl also match.
		// using preg_replace instead of parse_url() because the latter doesn't parse '//www.muze.nl' correctly.
		if (isset($url2) ) {
			if ( !is_array($url2) ){
				$url2 = array($url2);
			}
		} else {
			$url2 = array();
		}

		$prepurl1 = preg_replace('|^[a-z:]*//|i', '', $url1);

		foreach($url2 as $url) {
			if (
					$url == $url1 ||
					$prepurl1 == preg_replace('|^[a-z:]*//|i', '', $url2)
				) {
				return true;
			}
		}
		return false;
	}

	public function make_local_url($path="", $nls=false, $session=true, $https=null) {
		global $ARCurrent, $ARConfig;
		$site = false;
		$path = $this->make_path($path);
		$checkpath = $path;

		$redirects = $ARCurrent->shortcut_redirect;
		if (isset($redirects) && is_array($redirects)) {
			$newpath = $checkpath;
			$c_redirects = count($redirects);
			$c_redirects_done = 0;
			while (count($redirects) && ($redir = array_pop($redirects)) && $redir['keepurl'] && substr($newpath, 0, strlen($redir['dest'])) == $redir['dest']) {
				$c_redirects_done++;
				$newpath = $redir['src'].substr($newpath, strlen($redir['dest']));
			}

			if ($c_redirects_done == $c_redirects) {
				$checkpath = $redir['src'];
			}
		}

		do {
			if (!$config=$ARConfig->cache[$checkpath]) {
				$config=($ARConfig->cache[$checkpath]) ? $ARConfig->cache[$checkpath] : $this->loadConfig($checkpath);
			}
			if ($config) {
				$checkNLS = $nls;
				if (!$checkNLS) {
					$checkNLS = $this->nls;
				}
				$urlCheck = $config->root['list']['nls'][$checkNLS];
				if (!is_array($urlCheck)) {
					$urlCheck = $config->root["value"];
				}
				$requestedHost = ldGetRequestedHost();

				if ($this->compare_hosts($requestedHost, $urlCheck)) {
					$site=$config->site;
				}
			}
			$prevpath=$checkpath;
			$checkpath=$this->make_path($checkpath."../");
		} while ($prevpath!=$checkpath && !$site);
		if (!$site) {
			$site='/';
		}
		$site_url=$this->make_url($site, $nls, $session, $https, true);
		if ($newpath) { // $newpath is the destination of a shortcut redirection, with keepurl on
			$rest=substr($newpath, strlen($site));
		} else {
			$rest=substr($path, strlen($site));
		}
		return self::sanitizeUrl($site_url.$rest);
	}
	public function sanitizeUrl($url) {
		// remove unexpected chars from the url;
		return preg_replace("/[^\/{}:.A-Za-z0-9_-]/", "", $url);
	}
	public function sanitizePath($path) {
		// remove unexpected chars from the path; same as url for now;
		return self::sanitizeUrl($path);
	}

	public function AR_implements($implements) {
		$type = current(explode(".",$this->type));
		return $this->store->AR_implements($type, $implements);
	}

	public function getlocks() {
		global $AR;
		if ($this->store->mod_lock) {
			$result=$this->store->mod_lock->getlocks($AR->user->data->login);
		} else {
			$result="";
		}
		return $result;
	}

	public function lock($mode="O", $time=0) {
	global $AR;
		if ($this->store->mod_lock) {
			$result=$this->store->mod_lock->lock($AR->user->data->login,$this->path,$mode,$time);
		} else {
			$result=true; // no lock module, so lock is 'set'
		}
		return $result;
	}

	public function unlock() {
	global $AR;
		if ($this->store->mod_lock) {
			$result=$this->store->mod_lock->unlock($AR->user->data->login,$this->path);
		} else {
			$result=true;
		}
		return $result;
	}

	public function touch($id=0, $timestamp=-1) {
		if (!$id) {
			$id = $this->id;
		}
		$result = $this->store->touch($id, $timestamp);
		if ($this->store->error) {
			$this->error = ar::error( ''.$this->store->error, 1107, $this->store->error);
		}
		return $result;
	}

	public function mogrify($id=0, $type, $vtype=null) {
		if (!$id) {
			$id = $this->id;
		}
		if (!$vtype) {
			$vtype = $type;
		}
		if (strpos($vtype, '.')!==false) {
			$vtype = substr($vtype, 0, strpos($vtype, '.'));
		}
		$result = $this->store->mogrify($id, $type, $vtype);
		if ($this->store->error) {
			$this->error = ar::error( ''.$this->store->error, 1107, $this->store->error);
		}
		return $result;
	}

	public function can_mogrify() {
		if ($this->path == "/system/users/admin/") {
			return false;
		}
		return true;
	}

	public function load_properties($scope='') {
		return $this->store->load_properties($this->id,'',$scope);
	}

	public function _load_properties($scope='') {
		return $this->store->load_properties($this->id,'',$scope);
	}

	public function load_property($property, $scope='') {
		return $this->store->load_property($this->id,$property,$scope);
	}

	public function _load_property($property, $scope='') {
		return $this->store->load_property($this->id,$property,$scope);
	}

	public function GetValidGrants($path="") {
	/********************************************************************

	  This function finds all grants in effect on this object for the
	  logged in user! $AR->user must already be set.

	  Grants are checked in the following way:
	  1) First all parents of this object are checked for grants for this
	     specific user. The 'nearest' grants are valid, and the path of
	     parent that set these grants will be the upper limit for the
	     checking of group grants.
	  2) Now all groups of which the user is a member are checked for
	     grants. Likewise, all parents are checked for group grants, upto
	     but not including the upperlimit as set in 1. All group grants
	     found are merged into one grants list.
	  3) If there are gropup grants, this means that there are group
	     grants set in a parent nearer to this object than the user grants
	     and therefore the groupgrants must be merged with the
	     usergrants.

	  this results in:
	  1	/		user: read edit		group: none
	  2	/dir/					group: read
	  3	/dir2/		user: none		group: read
	  4	/dir/dir3/				group2: edit
	  case 1: the user takes precedence over the group, grants are 'read edit'
	  case 2: groupgrants are merged with usergrants, as its grants are set
	          in a 'nearer' parent (itself). grants are 'read edit'.
	  case 3: user takes precedence again. grants are 'none'.
	  case 4: All group grants are merged with the usergrants.
	          Therefore the grants are 'none read edit'.
	********************************************************************/

	global $AR;

		if ($AR->user) { 	// login and retrieval of user object
			if (!$path) {
				$path=$this->path;
			}
			if (!$AR->user->grants[$path]) {
				$grants=array();
				$userpath=$AR->user->FindGrants($path, $grants);
				// if not already done, find all groups of which the user is a member
				if (!is_array($AR->user->externalgroupmemberships) || count($AR->user->externalgroupmemberships)==0) {
					$criteria["members"]["login"]["="]=$AR->user->data->login;
				} else {
					// Use the group memberships of external databases (e.g. LDAP)
					$criteria="members.login='".AddSlashes($AR->user->data->login)."'";
					foreach (array_keys($AR->user->externalgroupmemberships) as $group) {
						$criteria.=" or login.value='".AddSlashes($group)."'";
					}
				}
				if (!$AR->user->groups) {
					$groups=$this->find("/system/groups/",$criteria, "system.get.phtml");
					if (isset($groups) && is_array($groups)) {
						foreach($groups as $group ){
							if (is_object($group)) {
								$AR->user->groups[$group->path] = $group;
							}
						}
					}
					if (isset($AR->user->data->config->groups) && is_array($AR->user->data->config->groups)) {
						foreach ($AR->user->data->config->groups as $groupPath => $groupId) {
							if (!$AR->user->groups[$groupPath]) {
								$AR->user->groups[$groupPath] = current($this->get($groupPath, "system.get.phtml"));
							}
						}
					}
					if (!$AR->user->groups["/system/groups/public/"]) {
						if ($public=current($this->get("/system/groups/public/", "system.get.phtml"))) {
							$AR->user->groups[$public->path] = $public;
						}
					}
				}
				if ($AR->user->groups) {
					/* check for owner grants (set by system.get.config.phtml) */
					if (isset($AR->user->ownergrants) && is_array($AR->user->ownergrants)) {
						if (!$AR->user->groups["owner"]) {
							$AR->user->groups["owner"] = @current($this->get("/system/groups/owner/", "system.get.phtml"));
						}
						$AR->user->groups["owner"]->data->config->usergrants = $AR->user->ownergrants;
					}
					foreach($AR->user->groups as $group){
						$groupgrants=array();
						if (is_object($group)) {
							$group->FindGrants($path, $groupgrants, $userpath);
							if (isset($grants) && is_array($grants)) {
								foreach($groupgrants as $gkey => $gval ){
									if (isset($grants[$gkey]) && is_array($grants[$gkey]) && is_array($gval)) {
										$grants[$gkey]=array_merge($gval, $grants[$gkey]);
									} else
									if ($gval && !is_array($gval)) {
										$grants[$gkey] = $gval;
									} else
									if ($gval && !$grants[$gkey]) {
										$grants[$gkey] = $gval;
									}
								}
							} else {
								$grants = $groupgrants;
							}
						}
					}
				}
				if(isset($AR->sgGrants) && is_array($AR->sgGrants) ) {
					ksort($AR->sgGrants);
					$ppath = $this->make_path($path);
					foreach( $AR->sgGrants as $sgpath => $sggrants) {
						$sgpath = $this->make_path($sgpath);
						if( substr($ppath, 0, strlen($sgpath)) == $sgpath ) { // sgpath is parent of ppath or equal to ppath
							if (isset($grants) && is_array($grants)) {
								foreach($sggrants as $gkey => $gval ){
									if (isset($grants[$gkey]) && is_array($grants[$gkey]) && is_array($gval)) {
										$grants[$gkey]=array_merge($gval, $grants[$gkey]);
									} else
									if ($gval && !is_array($gval)) {
										$grants[$gkey] = $gval;
									} else
									if ($gval && !$grants[$gkey]) {
										$grants[$gkey] = $gval;
									}
								}
							} else {
								$grants = $sggrants;
							}
						}
					}
				}
				$AR->user->grants[$path]=$grants;
			}
			$grants=$AR->user->grants[$path];

		}
		debug("pobject: GetValidGrants(user:".$AR->user->data->login."): end ( ".debug_serialize($grants)." )","all");
		return $grants;
	}


	public function pushContext($context) {
	global $AR;
		if(!empty($AR->context)) {
			$context = array_merge(end($AR->context), $context);
		}
		array_push($AR->context, $context);
	}

	public function setContext($context, $level=0) {
	global $AR;
		$AR->context[count($AR->context)-(1+$level)]=$context;
	}

	public function popContext() {
	global $AR;
		return array_pop($AR->context);
	}

	public static function getContext($level=0) {
	global $AR;
		return $AR->context[count($AR->context)-(1+$level)];
	}

	public function CheckAdmin($user) {
	if ($user->data->login == "admin") {
			return true;
		}
		if ($user->data->groups['/system/groups/admin/']) {
			return true;
		}
		return false;
	}

	public function CheckLogin($grant, $modifier=ARTHISTYPE) {
	global $AR,$ARnls,$ARConfig,$ARCurrent,$ARConfigChecked;
		if (!$this->store->is_supported("grants")) {
			debug("pobject: store doesn't support grants");
			return true;
		}
		if ($modifier==ARTHISTYPE) {
			$modifier=$this->type;
		}

		/* load config cache */
		if (!isset($ARConfig->cache[$this->path])) {
			// since this is usually run before CheckConfig, make sure
			// it doesn't set cache time
			$realConfigChecked = $ARConfigChecked;
			$ARConfigChecked = true;
			$this->loadConfig();
			$ARConfigChecked = $realConfigChecked;
		}

		$isadmin = $this->CheckAdmin($AR->user);

		if (!$isadmin && !$AR->user->grants[$this->path]) {
			$grants = $this->GetValidGrants();
		} else {
			$grants = $AR->user->grants[$this->path];
		}

		if ($AR->user->data->login!="public") {
			// Don't remove this or MSIE users won't get uptodate pages...
			ldSetClientCache(false);
		}

		if ( 	( !$grants[$grant]
					|| ( $modifier && is_array($grants[$grant]) && !$grants[$grant][$modifier] )
				) && !$isadmin ) {
			// do login
			$arLoginMessage = $ARnls["accessdenied"];
			ldAccessDenied($this->path, $arLoginMessage);
			$result=false;
		} else {
			$result=($grants || $isadmin);
		}

		$ARCurrent->arLoginSilent=1;
		return $result;
	}


	public function CheckPublic($grant, $modifier=ARTHISTYPE) {
	global $AR;

		$result=false;
		if (!$AR->public) {
			$this->pushContext(array('scope' => 'php', 'arCurrentObject' => $this));
				$AR->public=current($this->get("/system/users/public/", "system.get.phtml"));
			$this->popContext();
		}
		if ($AR->public) {
			$AR->private=$AR->user;
			$AR->user=$AR->public;
			$result=$this->CheckSilent($grant, $modifier);
			$AR->user=$AR->private;
		}
		return $result;
	}

	public function CheckSilent($grant, $modifier=ARTHISTYPE, $path=".") {
	global $AR, $ARConfig;
		$path = $this->make_path($path);
		if ($modifier==ARTHISTYPE) {
			$modifier=$this->type;
		}

		/* load config cache */
		if (!$ARConfig->cache[$path]) {
			$this->loadConfig($path);
		}
		if ($this->CheckAdmin($AR->user)) {
			$result=1;
		} else if ($grants=$AR->user->grants[$path]) {
			$result=$grants[$grant];
		} else {
			$grants=$this->GetValidGrants();
			$result=$grants[$grant];
		}
		if ($modifier && is_array($result)) {
			$result=$result[$modifier];
		}
		return $result;
	}

	public function CheckNewFile($newfilename) {
	global $ARnls;
	/**********************************************************************

	  This function performs all the necessary checks on a path to see
	whether it's a valid path for a new object. This consists of:
	1) checking for invalid characters, valid chars are "a-zA-Z0-9./_-"
	2) checking whether the path starts and ends with a "/".
	3) checking whether the path doesn't exist already.
	4) checking whether the parent exists.

	if all this checks out, it returns 1. If not, $this->error is set to
	the correct error message.

	**********************************************************************/

		$this->error="";
		if (preg_match("|^/[a-z0-9\./_-]*/$|i",$newfilename)) {
			if (!$this->store->exists($newfilename)) {
				$parent=$this->store->make_path($newfilename, "..");
				if ($this->store->exists($parent)) {
					$result=1;
				} else {
					$this->error = ar::error( sprintf($ARnls["err:filenameinvalidnoparent"],$newfilename,$parent), 1102);
				}
			} else {
				$this->error = ar::error( sprintf($ARnls["err:chooseotherfilename"],$newfilename), 1103);
			}
		} else {
			$this->error = ar::error( sprintf($ARnls["err:fileillegalchars"],$newfilename)." ".$ARnls["err:startendslash"], 1104);
		}
		return $result;
	}

	public function resetConfig($path='') {
	global $ARConfig;
		$path = $this->make_path($path);
		if ($ARConfig->cache[$path]) {
			$path = preg_quote($path,'/');
			$keys = preg_grep('/^'.$path.'/',array_keys($ARConfig->cache));
			foreach ($keys as $cachepath) {
				unset($ARConfig->cache[$cachepath]);
				unset($ARConfig->pinpcache[$cachepath]);
			}
		}
	}

	public function clearChildConfigs($path='') {
	global $ARConfig;
		$path = $this->make_path($path);
		if ($ARConfig->cache[$path]) {
			$path = preg_quote($path,'/');
			$keys = preg_grep('/^'.$path.'./',array_keys($ARConfig->cache));
			foreach($keys as $cachepath) {
				unset($ARConfig->cache[$cachepath]);
				unset($ARConfig->pinpcache[$cachepath]);
				unset($ARConfig->libraries[$cachepath]);
			}
		}
	}

	protected function getConfig() {
	global $ARConfig, $ARCurrent, $ARConfigChecked;
		$this->pushContext(array('scope' => 'php', 'arCurrentObject' => $this));
		// $context=$this->getContext(0);
		// debug("getConfig(".$this->path.") context: ".$context['scope'] );
		// debug(print_r($ARConfig->nls, true));
		if( !$ARConfig->cache[$this->parent] && $this->parent!=".." ) {
			$parent = current($this->get($this->parent, "system.get.phtml"));
			if ($parent) {
				$parent->getConfig();
			}
		}

		$this->getConfigData();

		$ARConfig->pinpcache[$this->path] = $ARConfig->pinpcache[$this->parent];
		// backwards compatibility when calling templates from config.ini
		$prevArConfig = $ARCurrent->arConfig;
		$ARCurrent->arConfig = $ARConfig->pinpcache[$this->path];

		$arCallArgs['arConfig'] = $ARConfig->pinpcache[$this->path];

		/* calling config.ini directly for each system.get.config.phtml call */
		$loginSilent = $ARCurrent->arLoginSilent;
		$ARCurrent->arLoginSilent = true;
		// debug("getConfig:checkconfig start");

		$initialNLS = $ARCurrent->nls;
		$initialConfigChecked = $ARConfigChecked;

		$ARConfig->cache[$this->path]->inConfigIni = true;
		if ($ARConfig->cache[$this->path]->hasConfigIni && !$this->CheckConfig('config.ini', $arCallArgs)) {
			//debug("pobject::getConfig() loaded config.ini @ ".$this->path);
			// debug("getConfig:checkconfig einde");
			$arConfig = $ARCurrent->arResult;
			if (!isset($arConfig)) {
				$arConfig = $ARCurrent->arConfig;
			}
			unset($ARCurrent->arResult);
			if (isset($arConfig['library']) && is_array($arConfig['library'])) {
				if (!$ARConfig->libraries[$this->path]) {
					$ARConfig->libraries[$this->path] = array();
				}
				foreach ($arConfig['library'] as $libName => $libPath) {
					$this->loadLibrary($libName, $libPath);
				}
				unset($arConfig['library']);
			}
			$ARConfig->pinpcache[$this->path] = (array) $arConfig;
		}
		$ARConfig->cache[$this->path]->inConfigIni = false;
		$this->clearChildConfigs( $this->path ); // remove any config data for child objects, since these are set before their parent config was set
		$ARConfigChecked = $initialConfigChecked;
		$ARCurrent->nls = $initialNLS;

		$arConfig = &$ARConfig->pinpcache[$this->path];
		if (!is_array($arConfig['authentication']['userdirs'])) {
			$arConfig['authentication']['userdirs'] = array('/system/users/');
		} else {
			if (reset($arConfig['authentication']['userdirs']) != '/system/users/') {
				array_unshift($arConfig['authentication']['userdirs'], '/system/users/');
			}
		}
		if (!is_array($arConfig['authentication']['groupdirs'])) {
			$arConfig['authentication']['groupdirs'] = array('/system/groups/');
		} else {
			if (reset($arConfig['authentication']['groupdirs']) != '/system/groups/') {
				array_unshift($arConfig['authentication']['groupdirs'], '/system/groups/');
			}
		}

		$ARCurrent->arLoginSilent = $loginSilent;
		$ARCurrent->arConfig = $prevArConfig;
		$this->popContext();
	}

	protected function getConfigData() {
	global $ARConfig, $AR;
		$context = $this->getContext(0);
		if (!$ARConfig->cache[$this->path] && $context["scope"] != "pinp") {
			// first inherit parent configuration data
			$configcache= clone $ARConfig->cache[$this->parent];
			$configcache->localTemplates = [];
			$configcache->pinpTemplates = [];
			$configcache->id = $this->id;

			// cache default templates
			if (isset($this->data->config->templates) && count($this->data->config->templates)) {
				$configcache->pinpTemplates    = $this->data->config->pinp;
				$configcache->privatetemplates = $this->data->config->privatetemplates;
				$configcache->localTemplates   = $this->data->config->templates;

				if( !$configcache->hasDefaultConfigIni ) {
					foreach($configcache->localTemplates as $type => $templates ) {
						if( isset($templates["config.ini"]) ) {
							$configcache->hasDefaultConfigIni = true;
							$configcache->hasConfigIni = true;
							break;
						}
					}
				}
			} else if (isset($this->data->config->pinp) && count($this->data->config->pinp)) {
				$configcache->pinpTemplates    = $this->data->config->pinp;
			}

			if( !$configcache->hasDefaultConfigIni ) {
				$configcache->hasConfigIni = false;
				if(isset($this->data->config->pinp) && is_array($this->data->config->pinp) ) {
					foreach( $this->data->config->pinp as $type => $templates ) {
						if( isset($templates["config.ini"]) ) {
							$configcache->hasConfigIni = true;
							break;
						}
					}
				}
			}

			$localcachesettings = $this->data->config->cacheSettings;
			if (!is_array($localcachesettings) ){
				$localcachesettings = array();
			}

			if (!is_array($configcache->cacheSettings) ) {
				$configcache->cacheSettings = array();
			}

			if ($this->data->config->cacheconfig) { // When removing this part, also fix the setting below.
				$configcache->cache=$this->data->config->cacheconfig;
			}

			if (!isset($localcachesettings['serverCache']) && isset($this->data->config->cacheconfig)) {
				$localcachesettings["serverCache"] = $this->data->config->cacheconfig;
			}

			if ($localcachesettings['serverCache'] != 0 ) {
				$localcachesettings['serverCacheDefault'] = $localcachesettings['serverCache'];
			}

			$configcache->cacheSettings = $localcachesettings + $configcache->cacheSettings;

			// store the current object type
			$configcache->type = $this->type;

			if ($this->data->config->typetree && ($this->data->config->typetree!="inherit")) {
				$configcache->typetree=$this->data->config->typetree;
			}
			if (isset($this->data->config->nlsconfig->list)) {
				$configcache->nls = clone $this->data->config->nlsconfig;
			}

			if ($this->data->config->grants["pgroup"]["owner"]) {
				$configcache->ownergrants = $this->data->config->grants["pgroup"]["owner"];
			}
			if (isset($configcache->ownergrants) && is_array($configcache->ownergrants)) {
				if ($AR->user && $AR->user->data->login != 'public' && $AR->user->data->login === $this->data->config->owner) {
					$ownergrants = $configcache->ownergrants;
					if (isset($ownergrants) && is_array($ownergrants)) {
						foreach( $ownergrants as $grant => $val ) {
							$AR->user->ownergrants[$this->path][$grant] = $val;
						}
					}
				}
			}

			if (isset($this->data->config->customconfig) && is_array($this->data->config->customconfig)) {
				$configcache->custom=array_merge(is_array($configcache->custom)?$configcache->custom:array(), $this->data->config->customconfig);
			}
			$ARConfig->cache[$this->path]=$configcache;

		}
	}

	public function loadConfig($path='') {
	global $ARConfig, $ARConfigChecked, $ARCurrent;
		$path=$this->make_path($path);
		// debug("loadConfig($path)");
		if (!isset($ARConfig->cache[$path]) ) {
			$allnls = $ARCurrent->allnls;
			$ARCurrent->allnls = true;
			$configChecked = $ARConfigChecked;
			if (($this->path == $path && !$this->arIsNewObject) || $this->exists($path)) {
				$this->pushContext(array("scope" => "php"));
				if( $this->path == $path ) {
					// debug("loadConfig: currentpath $path ");
					$this->getConfig();
				} else {
					//debug("loadConfig: get path $path ");
					$cur_obj = current($this->get($path, "system.get.phtml"));
					$cur_obj->getConfig();
				}
				$this->popContext();
				$result=$ARConfig->cache[$path];
			} else if ($path === '/') {
				// special case: / doesn't exists in the store
				$result=$ARConfig->cache['..'];
			} else {
				$parent=$this->make_path($path.'../');
				if (!$ARConfig->cache[$parent]) {
					$this->pushContext(array("scope" => "php"));
					// debug("loadConfig: parent $parent");
					$cur_obj = current($this->get($parent, "system.get.phtml"));
					if( $cur_obj ) {
						$cur_obj->getConfig();
					}
					$this->popContext();
				}
				$result=$ARConfig->cache[$parent];
				$ARConfig->cache[ $path ] = $result;
				$ARConfig->pinpcache[ $path ] = $ARConfig->pinpcache[ $parent ];
			}
			// restore old ARConfigChecked state
			$ARConfigChecked = $configChecked;
			$ARCurrent->allnls = $allnls;
		} else {
			// debug("loadConfig: exists $path ");
			$result=$ARConfig->cache[$path];
		}
		return $result;
	}


	// TODO: look for a way to merge loadConfig and loadUserConfig into one function

	public function loadUserConfig($path='') {
	global $ARConfig;
		$path = $this->make_path($path);
		$parent = $this->make_path($path.'../');

		if (!$ARConfig->cache[$path]) {
			$this->loadConfig($path);
		}
		if (!$ARConfig->pinpcache[$path]) {
			$config = $ARConfig->pinpcache[$parent];
		} else {
			$config = $ARConfig->pinpcache[$path];
		}
		return (array)$config;
	}

	public function loadLibrary($name, $path) {
	global $ARConfig;
		$path=$this->make_path($path);
		debug("pobject::loadLibrary($name, $path);");
		if ($name===ARUNNAMED) {
			if (strstr($path, $this->path)===0) {
				return ar::error('You cannot load an unnamed library from a child object.', 1109);
			} else {
				if (!isset($ARConfig->libraries[$this->path])) {
					$ARConfig->libraries[$this->path] = [ $path ];
				} else {
					array_unshift($ARConfig->libraries[$this->path],$path);
				}
			}
		} else if ($name && is_string($name)) {
			$ARConfig->libraries[$this->path][$name]=$path;
		} else if (is_int($name)) {
			$ARConfig->libraries[$this->path][$name]=$path;
		} else {
			return ar::error('Illegal library name: '.$name, 1110);
		}
	}

	// returns a list of libraries loaded on $path
	public function getLibraries($path = '') {
	global $ARConfig;
		$path = $this->make_path($path);
		return (array)$ARConfig->libraries[$path];
	}

	public function mergeLibraryConfig( $defaultLibraryName, $defaults ) {
		$libraryName = ar::getvar('arLibrary');
		if ( is_numeric($libraryName) || $libraryName == 'current' ) { // library is loaded unnamed
			$libraryName = $defaultLibraryName;
		}
		if ( $libraryName ) {
			$userConfig = ar::acquire('defaults.'.$libraryName);
			if (isset($userConfig) && is_array($userConfig) ) {
				$defaults = array_merge( $defaults, $userConfig );
			}
		}
		return array_merge( $defaults, $this->getvar('arCallArgs') );
	}

	public function _mergeLibraryConfig( $defaultLibraryName, $defaults ) {
		return $this->mergeLibraryConfig( $defaultLibraryName, $defaults );
	}

	protected function findTemplateOnPath($path, $arCallFunction, $arType, $reqnls, &$arSuperContext){

		while ($arType!='ariadne_object' ) {
			list($arMatchType,$arMatchSubType) = explode('.',$arType,2);
			$local = ($path === $this->path);
			$templates = ar('template')->ls($path);
			if(isset($templates[$arCallFunction])) {
				$template = null;
				if (!isset($arSuperContext[$path.":".$arType.":".$arCallFunction])) {
					$template = array_reduce($templates[$arCallFunction] , function($carry, $item) use ($arMatchType,$arMatchSubType, $reqnls, $local) {
						if ( ( $item['local'] == true && $local == true ) || $item['local'] == false ) {
							if ($item['type'] === $arMatchType && ($item['subtype'] == $arMatchSubType) ) {
								if (isset($carry) && $carry['language'] !== 'any') {
									return $carry;
								} else if ($item['language'] === 'any' || $item['language'] === $reqnls ) {
									return $item;
								}
							}
						}
						return $carry;
					}, null);
				}
				if ( isset($template) && !isset($arSuperContext[$path.":".$arType.":".$arCallFunction])) {
					return $template;
				}
			}
			if (!isset($AR->superClass[$arType])) {
				// no template found, no default.phtml found, try superclass.
				if ($subcpos = strpos($arType, '.')) {
					$arSuper = substr($arType, 0, $subcpos);
				} else {
					if (!class_exists($arType, false )) {
						// the given class was not yet loaded, so do that now
						$arTemp=$this->store->newobject('','',$arType,new baseObject);
					} else {
						$arTemp=new $arType();
					}
					$arSuper=get_parent_class($arTemp);
				}
				$AR->superClass[$arType]=$arSuper;
			} else {
				$arSuper=$AR->superClass[$arType];
			}
			$arType=$arSuper;
		}

		return null;
	}

	public function getPinpTemplate($arCallFunction='view.html', $path=".", $top="", $inLibrary = false, $librariesSeen = null, $arSuperContext=array()) {
	global $ARCurrent, $ARConfig, $AR, $ARConfigChecked;
		debug("getPinpTemplate: function: $arCallFunction; path: $path; top: $top; inLib: $inLibrary","class");
		$result = array();
		if (!$top) {
			$top = '/';
		}
		$path = $this->make_path($path);
		if (!is_array($arSuperContext)) {
			$arSuperContext = array();
		}

		$matches = [];
		preg_match('/^
		    ( (?<libname> [^:]+) :  )?
		    ( (?<calltype>[^:]+) :: )?
		      (?<template>[^:]+)
		    $/x', $arCallFunction, $matches);

		$arCallFunction = $matches['template'];

		if($matches['calltype'] != '') {
			$arCallType = $matches['calltype'];
		} else {
			$arCallType = $this->type;
		}

		if ( $matches['libname'] != '' ) {
			$arLibrary      = $matches['libname'];

			if ($arLibrary == 'current') {
				// load the current arLibrary
				$context       = $this->getContext(1);
				$arLibrary     = $context['arLibrary'];
				$arLibraryPath = $context['arLibraryPath'];
			} else {
				$libpath = $path;
				while (!isset($arLibraryPath) && $libpath!=$lastlibpath) {
					$lastlibpath = $libpath;
					if (isset($ARConfig->libraries[$libpath][$arLibrary])) {
						$arLibraryPath = $ARConfig->libraries[$libpath][$arLibrary];
					} else {
						if ($libpath == $top) {
							break;
						}
						$libpath=$this->store->make_path($libpath, "..");
					}
				}
			}
			if ($arLibraryPath) {
				debug("getPinpTemplate: found library '$arLibrary'. Searching for [".$arCallType."] $arCallFunction on '".$arLibraryPath."' up to '$top'");
				$librariesSeen[$arLibraryPath] = true;
				$inLibrary = true;
				$path = $arLibraryPath;
			} else {
				debug("getPinpTemplate: Failed to find library $arLibrary");
			}
			$path = $this->make_path($path);
		}

		$checkpath           = $path;
		$lastcheckedpath     = "";
		$arCallClassTemplate = $ARCurrent->arCallClassTemplate;
		$reqnls              = $this->reqnls;
		$template            = null;
		while (!$arCallClassTemplate && !isset($template) && $checkpath!=$lastcheckedpath) {
			$lastcheckedpath = $checkpath;

			$template = $this->findTemplateOnPath( $checkpath, $arCallFunction, $arCallType, $reqnls, $arSuperContext);

			if (isset($template)) {
				// haal info uit template
				// debug("getPinpTemplate: found ".$arCallFunction." on ".$checkpath);
			} else if ($inLibrary) {

				// faster matching on psection, prefix doesn't have to be a valid type
				$prefix = substr($ARConfig->cache[$checkpath]->type,0,8);

				if ($prefix === 'psection') {
					 // debug("BREAKING; $arTemplateId");
					// break search operation when we have found a
					// psection object
					break;
				}
			} else {
				if (isset($ARConfig->libraries[$checkpath])) {
					// need to check for unnamed libraries
					$libraries = array_filter($ARConfig->libraries[$checkpath],'is_int',ARRAY_FILTER_USE_KEY);
					foreach( $libraries as $key => $libpath ) {
						$arLibraryPath = $libpath;
						$arLibrary     = $key;

						$libprevpath = null;
						while($libpath != $libprevpath ) {
							$libprevpath = $libpath;

							$template = $this->findTemplateOnPath( $libpath, $arCallFunction, $arCallType, $reqnls, $arSuperContext);
							if (isset($template)) {
								break 2;
							}

							$prefix = substr($ARConfig->cache[$libpath]->type,0,8);
							if ($prefix === 'psection' || $top == $libpath) {
								break;
							}

							$libpath = $this->store->make_path($libpath, "..");
						}
					}
					debug("getPinpTemplate: found ".$arCallFunction." on ".$template['path']);
				}

			}

			if ($checkpath == $top) {
				break;
			}

			debug("getPinpTemplate: DONE checking for ".$arCallFunction." on ".$checkpath);
			$checkpath=$this->store->make_path($checkpath, "..");
			
		}
		$result = null;
		if(isset($template)) {
			$result = [];
			//debug("getPinpTemplate END; ".$template['id'] .' '.$template['path']);
			$type = $template['type'];
			if(isset($template['subtype'])) {
				$type .= '.' . $template['subtype'];
			}
			$result["arTemplateId"]       = $template['id'];
			$result["arCallTemplate"]     = $template['filename'];
			$result["arCallType"]         = $arCallType;
			$result["arCallTemplateName"] = $arCallFunction;
			$result["arCallTemplateNLS"]  = $template['language'];
			$result["arCallTemplateType"] = $type;
			$result["arCallTemplatePath"] = $template['path'];
			$result["arLibrary"]          = $arLibrary;
			$result["arLibraryPath"]      = $arLibraryPath;
			$result["arLibrariesSeen"]    = $librariesSeen;
			$result["arPrivateTemplate"]  = $template['private'];
		}
		return $result;
	}

	public function CheckConfig($arCallFunction="", $arCallArgs="") {
	// returns true when cache isn't up to date and no other template is
	// defined for $path/$function. Else it takes care of output to the
	// browser.
	// All these templates must exist under a fixed directory, $AR->dir->templates
	global $nocache, $AR, $ARConfig, $ARCurrent, $ARBeenHere, $ARnls, $ARConfigChecked;
		$MAX_LOOP_COUNT=10;


		// system templates (.phtml) have $arCallFunction=='', so the first check in the next line is to
		// make sure that loopcounts don't apply to those templates.
		if (0 && $arCallFunction && $ARBeenHere[$this->path][$arCallFunction]>$MAX_LOOP_COUNT) { // protect against infinite loops
			error(sprintf($ARnls["err:maxloopexceed"],$this->path,$arCallFunction,$arCallArgs));
			$this->store->close();
			exit();
		} else {
			$ARBeenHere[$this->path][$arCallFunction]+=1;

			// this will prevent the parents from setting the cache time
			$initialConfigChecked = $ARConfigChecked;
			$ARConfigChecked = true;
			$config = ($ARConfig->cache[$this->path]) ? $ARConfig->cache[$this->path] : $this->loadConfig();
			$ARConfigChecked = $initialConfigChecked;
			$ARConfig->nls=$config->nls;


			// if a default language is entered in a parent and no language is
			// explicitly selected in the url, use that default.
			// The root starts with the system default (ariadne.phtml config file)
			if ( !$ARCurrent->nls ) {
				if ( $config->root['nls'] ) {
					$this->reqnls = $config->root['nls'];
					if ( !$ARConfigChecked ) {
						$ARCurrent->nls = $this->reqnls;
					}
				} else if ( $config->nls->default ) {
					$this->reqnls = $config->nls->default;
					$this->nls = $this->reqnls;
					if ( !$ARConfigChecked ) {
						$ARCurrent->nls = $this->nls;
					}
				}
			} else {
				$this->reqnls = $ARCurrent->nls;
			}
			$nls = &$this->nls;
			$reqnls = &$this->reqnls;

			if (!$ARConfigChecked && is_object($ARnls)) {
				$ARnls->setLanguage($ARCurrent->nls);
			}


			if (!$ARCurrent->arContentTypeSent) {
				ldHeader("Content-Type: text/html; charset=UTF-8");
				$ARCurrent->arContentTypeSent = true;
			}

/*			// FIXME: the acceptlang code works a bit too well.. it overrides psite configuration settings.

			if ($ARCurrent->acceptlang && !$ARCurrent->nls) {
				if ($ARCurrent->acceptlang && is_array($this->data->nls->list)) {
					$validlangs = array_intersect(array_keys($ARCurrent->acceptlang), array_keys($this->data->nls->list));
				}
				if ($validlangs) {
					$reqnls=array_shift($validlangs);
					$ARCurrent->nls = $reqnls;
				}
			}
*/
			if (isset($this->data->custom) && is_array($this->data->custom) && $this->data->custom['none']) {
				$this->customdata=$this->data->custom['none'];
			}
			if (isset($this->data->custom) && is_array($this->data->custom) && $this->data->custom[$nls]) {
				$this->customnlsdata=$this->data->custom[$nls];
			}

			if (!$ARConfigChecked) {
				// this template is the first template called in this request.
				$eventData = new baseObject();
				$eventData->arCallArgs = $arCallArgs;
				$eventData->arCallFunction = $arCallFunction;

				$ARConfigChecked = true;
				$result = ar_events::fire( 'onbeforeview', $eventData );
				$ARConfigChecked = $initialConfigChecked;
				if ( !$result ) { //prevent default action: view
					return false;
				}
			}

			if (!$ARConfigChecked) {
				// if this object isn't available in the requested language, show
				// a language select dialog with all available languages for this object.
				if (isset($this->data->nls) && !$this->data->name) {
					if (!$ARCurrent->forcenls && (!isset($this->data->nls->list[$reqnls]) || !$config->nls->list[$reqnls])) {
						if (!$ARCurrent->nolangcheck && $arCallFunction != 'config.ini') {
							$ARCurrent->nolangcheck=1;
							$eventData = new baseObject();
							$eventData->arCallFunction = $arCallFunction;
							$eventData->arCallArgs = $arCallArgs;
							$eventData->arRequestedNLS = $reqnls;
							$result = ar_events::fire( 'onlanguagenotfound', $eventData );
							if ( $result ) { // continue with default action: langaugeselect
								$result->arCallArgs["arOriginalFunction"] = $result->arCallFunction;
								$this->call("user.languageselect.html", $result->arCallArgs);
								return false;
							}
						} else {
							$this->nlsdata=$this->data->$nls;
						}
					} else {
						$this->nlsdata=$this->data->$reqnls;
					}
				}
				$ARCurrent->nolangcheck=1;
			}

			/*
				Set ARConfigChecked to true to indicate that we have been here
				earlier.
			*/
			$ARConfigChecked = true;
			if ($arCallFunction) { // don't search for templates named ''
				// FIXME: Redirect code has to move to getPinpTemplate()
				$redirects	= $ARCurrent->shortcut_redirect;
				if (isset($redirects) && is_array($redirects)) {
					$redirpath = $this->path;
					while (!$template['arTemplateId'] &&
							($redir = array_pop($redirects)) &&
							$redir["keepurl"] &&
							(substr($redirpath, 0, strlen($redir["dest"])) == $redir["dest"])
					) {
						$template = $this->getPinpTemplate($arCallFunction, $redirpath, $redir["dest"]);
						$redirpath = $redir['src'];
					}

					if (!$template["arTemplateId"] && $redirpath) {
						$template = $this->getPinpTemplate($arCallFunction, $redirpath);
					}
				}
				if (!$template["arTemplateId"]) {
					$template = $this->getPinpTemplate($arCallFunction);
				}

				if ($template["arCallTemplate"] && $template["arTemplateId"]) {
					if (!isset($ARCurrent->cacheTemplateChain)) {
						$ARCurrent->cacheTemplateChain = array();
					}
					if (!isset($ARCurrent->cacheTemplateChain[$template["arTemplateId"]])) {
						$ARCurrent->cacheTemplateChain[$template["arTemplateId"]] = array();
					}
					if (!isset($ARCurrent->cacheTemplateChain[$template["arTemplateId"]][$template['arCallTemplate']])) {
						$ARCurrent->cacheTemplateChain[$template["arTemplateId"]][$template['arCallTemplate']] = array();
					}
					if (!isset($ARCurrent->cacheTemplateChain[$template["arTemplateId"]][$template['arCallTemplate']][$template['arCallTemplateType']])) {
						$ARCurrent->cacheTemplateChain[$template["arTemplateId"]][$template['arCallTemplate']][$template['arCallTemplateType']] = 0;
					}
					$ARCurrent->cacheTemplateChain[$template["arTemplateId"]][$template['arCallTemplate']][$template['arCallTemplateType']]++;


					debug("CheckConfig: arCallTemplate=".$template["arCallTemplate"].", arTemplateId=".$template["arTemplateId"],"object");
					// $arCallTemplate=$this->store->get_config("files")."templates".$arCallTemplate;
					// check if template exists, if it doesn't exist, then continue the original template that called CheckConfig
					$arTemplates=$this->store->get_filestore("templates");
					$exists = ar('template')->exists($template['arCallTemplatePath'],$template["arCallTemplate"]);
					if ( $exists ) {
						// check if the requested language exists, if not do not display anything,
						// unless otherwise indicated by $ARCurrent->allnls
						// This triggers only for pinp templates called by other templates,
						// as the first template (in the url) will first trigger the language
						// choice dialogue instead.
						$arLibrary = $template['arLibrary'];
						if (is_int($arLibrary)) {
							// set the library name for unnamed libraries to 'current'
							// so that calls using getvar('arLibrary') will keep on working
							$arLibrary = "current";
						}

						if (!is_string($arCallArgs)) {
							$arCallArgs['arCallFunction'] = $arCallFunction;
							$arCallArgs['arLibrary']      = $arLibrary;
							$arCallArgs['arLibraryPath']  = $template["arLibraryPath"];
						}

						$ARCurrent->arCallStack[]=$arCallArgs;
						// start running a pinp template

						$this->pushContext(
							array(
								"scope"              => "pinp",
								"arLibrary"          => $arLibrary,
								"arLibraryPath"      => $template['arLibraryPath'],
								"arCallFunction"     => $arCallFunction,
								"arCurrentObject"    => $this,
								"arCallType"         => $template['arCallType'],
								"arCallTemplateName" => $template['arCallTemplateName'],
								"arCallTemplateNLS"  => $template['arCallTemplateNLS'],
								"arCallTemplateType" => $template['arCallTemplateType'],
								"arCallTemplatePath" => $template['arCallTemplatePath'],
								"arLibrariesSeen"    => $template['arLibrariesSeen']
							)
						);

						// FIXME: is 2 het correcte getal? Kan dit minder magisch?
						if (count($ARCurrent->arCallStack) == 2 && true === $template['arPrivateTemplate']) {
							// Do not allow private templates to be called first in the stack.
							// echo "Bad request";

							// FIXME: Echte header sturen? Of gewoon niet uitvoeren? Wat is het correcte gedrag?
							// Return true zorgt er voor dat de default 404 handler het oppikt alsof het template niet bestaat.
							$this->popContext();
							array_pop($ARCurrent->arCallStack);
							return true;
						} else if ($ARCurrent->forcenls || isset($this->data->nls->list[$reqnls])) {
							// the requested language is available.
							$this->nlsdata=$this->data->$reqnls;
							$this->nls=$reqnls;
							$continue=true;
						} else if (!isset($this->data->nls)) {
							// the object has no language support
							$this->nlsdata=$this->data;
							$continue=true;
						} else if (($ARCurrent->allnls) || (!$initialConfigChecked && $ARCurrent->nolangcheck)) {
							// all objects must be displayed
							// $this->reqnls=$this->nls; // set requested nls, for checks
							$this->nls = isset($this->data->nls->default) ? $this->data->nls->default : $this->reqnls;
							$this->nlsdata = $this->data->$nls ?: $this->data->{$this->nls} ?: $this->data;
							$continue=true;
						} else {
							debug("CheckConfig: requested language not available, allnls not set","object");
							// -> skip this object (do not run template but do return false)
							$continue=false;
						}
						if ($continue) {
							$eventData = new baseObject();
							if ( !$AR->contextCallHandler ) { /* prevent onbeforecall from re-entering here */
								$AR->contextCallHandler = true;
								$eventData->arCallArgs = $arCallArgs;
								$eventData->arCallFunction = $arCallFunction;
								$eventData->arContext = $this->getContext();
								$eventData = ar_events::fire('onbeforecall', $eventData);
								$ARCurrent->arResult = $eventData->arResult;
								$AR->contextCallHandler = false;
								$continue = ($eventData!=false);
							}
							if ( $continue ) {
								if (!isset($ARCurrent->cacheCallChainSettings)) {
									$ARCurrent->cacheCallChainSettings = array();
								}
								if ($ARConfig->cache[$this->path]->inConfigIni == false) {
									$ARCurrent->cacheCallChainSettings[$this->id] = $config->cacheSettings;
								}

								if ($ARCurrent->ARShowTemplateBorders) {
									echo "<!-- arTemplateStart\nData: ".$this->type." ".$this->path." \nTemplate: ".$template["arCallTemplatePath"]." ".$template["arCallTemplate"]." \nLibrary:".$template["arLibrary"]." -->";
								}
								set_error_handler(array('pobject','pinpErrorHandler'),error_reporting());
								$func = ar('template')->get($template['arCallTemplatePath'],$template['arCallTemplate']);
								if(is_callable($func)){
									$arResult = $func($this);
								}
								restore_error_handler();
								if (isset($arResult)) {
									$ARCurrent->arResult=$arResult;
								}
								if ($ARCurrent->ARShowTemplateBorders) {
									echo "<!-- arTemplateEnd -->";
								}
								if ( !$AR->contextCallHandler ) { /* prevent oncall from re-entering here */
									$AR->contextCallHandler = true;
									$temp = $ARCurrent->arResult; /* event listeners will change ARCurrent->arResult */
									$eventData->arResult = $temp;
									ar_events::fire('oncall', $eventData );
									$ARCurrent->arResult = $temp; /* restore correct result */
									$AR->contextCallHandler = false;
								}
							}
						}
						array_pop($ARCurrent->arCallStack);
						$this->popContext();

						if ( !$initialConfigChecked && $arCallFunction != 'config.ini' ) {
							// this template was the first template called in this request.
							$eventData = new baseObject();
							$eventData->arCallArgs = $arCallArgs;
							$eventData->arCallFunction = $arCallFunction;
							ar_events::fire( 'onview', $eventData ); // no default action to prevent, so ignore return value.
						}
						return false;
					} else {
						debug("pobject: CheckConfig: no such file: ".$template["arTemplateId"].$template["arCallTemplate"]."","all");
					}
				} else {
					debug("CheckConfig: no arCallTemplate ($arCallFunction from '$this->path')","object");
				}

			}
		}
		return true;
	}

	public function ClearCache($path="", $private=true, $recurse=false) {
	global $AR;
		$norealnode = false;
		if (!$path) {
			$path=$this->path;
		} else {
			$realpath = current($this->get($path, "system.get.path.phtml"));
			if($realpath != false) {
				$path = $realpath;
			} else {
				$norealnode = true;
			}
		}

		if($norealnode !== true) {
			/*
				we don't want to recurse to the currentsite, because the path
				doesn't exists in the database, so it doesn't have a currentsite

				the privatecache should be emptied by delete, or by the cleanup
				cronjob. The current path doesn't exists in the database, so a
				object id which is needed to find the node in the cache, isn't
				available
			*/

			if ($private ) {
				// now remove any private cache entries.
				// FIXME: this doesn't scale very well.
				//        only scalable solution is storage in a database
				//        but it will need the original path info to
				//        remove recursively fast enough.
				//        this means a change in the filestore api. -> 2.5

				// Use chunks of max 5000 objects at a time to be more memory-efficient;
				$pcache=$this->store->get_filestore("privatecache");
				if ($recurse) {
					$offset = 0;
					$limit = 5000;
					$ids=$this->store->info($this->store->find($path, "" , $limit, $offset));
					while (is_array($ids) && count($ids)) {
						foreach($ids as $value) {
							$eventData = new baseObject();
							$eventData = ar_events::fire( 'onbeforeclearprivatecache', $eventData, $value['type'], $value['path'] );
							if ( !$eventData ) {
								continue;
							}

							$pcache->purge($value["id"]);
							ar_events::fire( 'onclearprivatecache', $eventData, $value['type'], $value['path'] );
						}

						$offset += $limit;
						$ids = $this->store->info($this->store->find($path, "", $limit, $offset));
					}
				} else {
					$eventData = new baseObject();
					$eventData = ar_events::fire( 'onbeforeclearprivatecache', $eventData, $this->type, $this->path );
					if ( $eventData ) {
						$pcache->purge($this->id);
						ar_events::fire( 'onclearprivatecache', $eventData, $this->type, $this->path );
					}
				}
			}

			// now clear all parents untill the current site
			$site=$this->currentsite($path);
			$project=$this->currentproject($path);
			if ($path!=$site && $path != $project && $path!='/') {
				$parent=$this->make_path($path.'../');
				$this->ClearCache($parent, $private, false);
			}
		}
		$recursed = array();

		// filesystem cache image filenames are always lower case, so
		// use special path for that. Remember 'real' path name for
		// recursion and stuff
		$fs_path=strtolower($path);
		$nlslist=$AR->nls->list;
		$nlslist["."]="default";
		$cache_types[] = "normal";
		$cache_types[] = "compressed";
		$cache_types[] = "session";

		global $cache_config,$store_config;
		$cachestore=new cache($cache_config);


		$filestore = $this->store->get_config("files");
		foreach($cache_types as $type){
			foreach($nlslist as $nls => $language){
				// break away if nls doesn't exists
				// is dir is cached, so it should not cost more that it add's in speed
				if(!is_dir($filestore."cache/$type/$nls")){
					continue;
				}

				$fpath=$filestore."cache/$type/$nls".$fs_path;
				$hpath=$filestore."cacheheaders/$type/$nls".$fs_path;
				if ($dir=@dir($fpath)) {
					while (false !== ($entry = $dir->read())) {
						if ($entry!="." && $entry!="..") {
							if (is_file($fpath.$entry)) {
								@unlink($fpath.$entry);
								@unlink($hpath.$entry);
								$cachestore->delete("/".$type."/".$nls.$fs_path.$entry);
							} else if ( $recurse && !$recursed[$entry]) {
								$this->ClearCache($path.$entry."/", false, true);
								$recursed[$entry]=true;
							}
						}
					}
					$dir->close();
					// remove empty directory entry's, hide errors about directory entry's with content
					@rmdir($fpath);
					@rmdir($hpath);
				} else if (file_exists(substr($fpath,0,-1)."=")) {
					@unlink(substr($fpath,0,-1)."=");
					@unlink(substr($hpath,0,-1)."=");
					$cachestore->delete("/".$type."/".$nls.substr($fs_path,0,-1)."=");
				}
			}
		}
	}

	public function getcache($name, $nls="") {
		global $ARCurrent, $ARnls;
		$result=false;
		$this->error = '';
		if ($name) {
			$result=false;
			if (!$nls) {
				$nls=$this->nls;
			}
			$file=$nls.".".$name;

			$minfresh = time();
			if (isset($ARCurrent->RequestCacheControl["min-fresh"])) {
				$minfresh += $ARCurrent->RequestCacheControl["min-fresh"];
			}

			$pcache=$this->store->get_filestore("privatecache");
			if ( $pcache->exists($this->id, $file) &&
			     ($pcache->mtime($this->id, $file) > ($minfresh) )  ) {

				$result = $pcache->read($this->id, $file);

				$contentType = $ARCurrent->ldHeaders['content-type'];
				if (preg_match('|^content-type:[ ]*([^ /]+)/|i', $contentType, $matches)) {
					$contentType = $matches[1];
				} else {
					$contentType = '';
				}

				if (!$contentType || strtolower($contentType) == 'text') {
					require_once($this->store->get_config('code')."modules/mod_url.php");
					$temp = explode('.', $file);
					$imageNLS = $temp[0];
					$result = URL::ARtoRAW($result, $imageNLS);
				}
			} else {
				$result=false;
				$ARCurrent->cache[]=$file;
				ob_start();
				/* output buffering is recursive, so this won't interfere with
				   normal page caching, unless you forget to call savecache()...
				   so normal pagecache needs to check $ARCurrent->cache, if it's
				   not empty, issue a warning and don't cache the outputbuffer...
				   savecache() must then pop the stack.
				*/
			}
		} else {
			$this->error = ar::error($ARnls["err:nonamecache"], 1111);
			$result = false;
		}
		return $result;
	}

	public function cached($name, $nls="") {
		if ($image=$this->getcache($name, $nls)) {
			echo $image;
			$result=true;
		} else {
			$result=false;
		}
		return $result;
	}

	public function savecache($time="") {
		global $ARCurrent, $ARnls, $DB;
		$result = false;
		$this->error = '';
		if (!$time) {
			$time=2; // 'freshness' in hours.
		}
		if ($file=array_pop($ARCurrent->cache)) {
			$image=ob_get_contents();
			if ($image !== false) {
				$result = true;
				$origimage = $image;

				$contentType = $ARCurrent->ldHeaders['content-type'];
				if (preg_match('|^content-type:[ ]*([^ /]+)/|i', $contentType, $matches)) {
					$contentType = $matches[1];
				} else {
					$contentType = '';
				}

				if (!$contentType || strtolower($contentType) == 'text') {
					require_once($this->store->get_config('code')."modules/mod_url.php");
					$temp = explode('.', $file);
					$imageNLS = $temp[0];
					$image = URL::RAWtoAR($image, $imageNLS);
				}

				if( $time > 0  && $DB["wasUsed"] == 0) {
					$pcache=$this->store->get_filestore("privatecache");
					$pcache->write($image, $this->id, $file);
					$time=time()+($time*3600);
					if (!$pcache->touch($this->id, $file, $time)) {
						$this->error = ar::error("savecache: ERROR: couldn't touch $file", 1113);
						$result = false;
					}
				}
				ob_end_clean();
				echo $origimage;
			} else {
				debug("skipped saving cache - ob_get_contents returned false so output buffering was not active", "all");
				$result = false;
			}
		} else {
			$this->error = ar::error($ARnls["err:savecachenofile"], 1112);
			$result = false;
		}
		return $result;
	}

	public function getdatacache($name) {
		global $ARCurrent, $ARnls;
		$result=false;
		$this->error = '';
		if ($name) {

			$minfresh = time();
			if (isset($ARCurrent->RequestCacheControl["min-fresh"])) {
				$minfresh += $ARCurrent->RequestCacheControl["min-fresh"];
			}

			$pcache=$this->store->get_filestore("privatecache");
			if ( $pcache->exists($this->id, $name) &&
			     ($pcache->mtime($this->id, $name) > $minfresh) ) {
				$result = unserialize($pcache->read($this->id, $name));
			} else {
				debug("getdatacache: $name doesn't exists, returning false.","all");
			}
		} else {
			$this->error = ar::error($ARnls["err:nonamecache"], 1111);
		}
		return $result;
	}

	public function savedatacache($name,$data,$time="") {
		global $DB;
		$this->error = '';
		if (!$time) {
			$time=2; // 'freshness' in hours.
		}
		$pcache=$this->store->get_filestore("privatecache");
		if( $time > 0  && $DB["wasUsed"] == 0) {
			$pcache->write(serialize($data), $this->id, $name);
			$time=time()+($time*3600);
			if (!$pcache->touch($this->id, $name, $time)) {
				$this->error = ar::error('Could not touch '.$name, 1113);
				return false;
			}
		}
		return true;
	}

	public function getdata($varname, $nls="none", $emptyResult=false) {
	// function to retrieve variables from $this->data, with the correct
	// language version.
	global $ARCurrent;

		$result = false;
		if ($nls!="none") {
			if ($ARCurrent->arCallStack) {
				$arCallArgs=end($ARCurrent->arCallStack);
				if (isset($arCallArgs) && is_array($arCallArgs)) {
					extract($arCallArgs);
				} else if (is_string($arCallArgs)) {
					Parse_Str($arCallArgs);
				}
			}
			if (isset(${$nls}[$varname])) {
				$result=${$nls}[$varname];
			} else if (isset($ARCurrent->$nls) && isset($ARCurrent->$nls->$varname)) {
				$result=$ARCurrent->$nls->$varname;
			} else if (($values=$_POST[$nls]) && isset($values[$varname])) {
				$result=$values[$varname];
			} else if (($values=$_GET[$nls]) && isset($values[$varname])) {
				$result=$values[$varname];
			} else if (($arStoreVars=$_POST["arStoreVars"]) && isset($arStoreVars[$nls][$varname])) {
				$result=$arStoreVars[$nls][$varname];
			} else if (($arStoreVars=$_GET["arStoreVars"]) && isset($arStoreVars[$nls][$varname])) {
				$result=$arStoreVars[$nls][$varname];
			}
			if ($result===false) {
				if (isset($this->data->{$nls}) && isset($this->data->{$nls}->{$varname})) {
					$result=$this->data->{$nls}->{$varname};
				}
			}
		} else { // language independant variable.
			if ($ARCurrent->arCallStack) {
				$arCallArgs=end($ARCurrent->arCallStack);
				if (isset($arCallArgs) && is_array($arCallArgs)) {
					extract($arCallArgs);
				} else if (is_string($arCallArgs)) {
					Parse_Str($arCallArgs);
				}
			}
			if (isset($$varname)) {
				$result=$$varname;
			} else if (isset($ARCurrent->$varname)) {
				$result=$ARCurrent->$varname;
			} else if (isset($_POST[$varname])) {
				$result=$_POST[$varname];
			} else if (isset($_GET[$varname])) {
				$result=$_GET[$varname];
			} else if (($arStoreVars=$_POST["arStoreVars"]) && isset($arStoreVars[$varname])) {
				$result=$arStoreVars[$varname];
			} else if (($arStoreVars=$_GET["arStoreVars"]) && isset($arStoreVars[$varname])) {
				$result=$arStoreVars[$varname];
			}
			if ($result===false) {
				if (isset($this->data->$varname)) {
					$result=$this->data->$varname;
				}
			}
		}
		if ( $result === false ) {
			$result = $emptyResult;
		}
		return $result;
	}

	public function showdata($varname, $nls="none", $emptyResult=false) {
		echo htmlspecialchars($this->getdata($varname, $nls, $emptyResult), ENT_QUOTES, 'UTF-8');
	}

	public function setnls($nls) {
		ldSetNls($nls);
	}

	public function getcharset() {
		return "UTF-8";
	}

	public function HTTPRequest($method, $url, $postdata = "", $port=80 ) {
		$maxtries = 5;
		$tries = 0;
		$redirecting = true;

		if(isset($postdata) && is_array($postdata)) {
			foreach($postdata as $key=>$val) {
				if(!is_integer($key)) {
					$data .= "$key=".urlencode($val)."&";
				}
			}
		} else {
			$data = $postdata;
		}

		while ($redirecting && $tries < $maxtries) {
			$tries++;
			// get host name and URI from URL, URI not needed though
			preg_match("/^([htps]*:\/\/)?([^\/]+)(.*)/i", $url, $matches);
			$host = $matches[2];
			$uri = $matches[3];
			if (!$matches[1]) {
				$url="http://".$url;
			}
			$connection = @fsockopen( $host, $port, $errno, $errstr, 120);
			if( $connection ) {
				if( strtoupper($method) == "GET" ) {
					if ($data) {
						$uri .= "?" . $data;
					}
					fputs( $connection, "GET $uri HTTP/1.0\r\n");
				} else if( strtoupper($method) == "POST" ) {
					fputs( $connection, "POST $uri HTTP/1.0\r\n");
				} else {
					fputs( $connection, "$method $uri HTTP/1.0\r\n");
				}

				fputs( $connection, "Host: $host\r\n");
				fputs( $connection, "Accept: */*\r\n");
				fputs( $connection, "Accept: image/gif\r\n");
				fputs( $connection, "Accept: image/x-xbitmap\r\n");
				fputs( $connection, "Accept: image/jpeg\r\n");

				if( strtoupper($method) == "POST" ) {
					$strlength = strlen( $data);
					fputs( $connection, "Content-type: application/x-www-form-urlencoded\r\n" );
					fputs( $connection, "Content-length: ".$strlength."\r\n\r\n");
					fputs( $connection, $data."\r\n");
				}

				fputs( $connection, "\r\n" , 2);

				$headerContents = '';
				$headerStart = 0;
				$headerEnd = 0;
				$redirecting = false;

				while (!feof($connection)) {
					$currentLine = fgets ($connection, 1024);
					if ($headerEnd && $redirecting) {
						break;
					} else if ($headerEnd && !$redirecting) {
						//this is the html from the page
						$contents = $contents . $currentLine;
					} else if ( preg_match("/^HTTP/", $currentLine) ) {
						//came to the start of the header
						$headerStart = 1;
						$headerContents = $currentLine;
					} else if ( $headerStart && preg_match('/^[\n\r\t ]*$/', $currentLine) ) {
						//came to the end of the header
						$headerEnd = 1;
					} else {
						//this is the header, if you want it...
						if (preg_match("/^Location: (.+?)\n/is",$currentLine,$matches) ) {
							$headerContents .= $currentLine;
							//redirects are sometimes relative
							$newurl = $matches[1];
							if (!preg_match("/http:\/\//i", $newurl, $matches) ) {
								$url .= $newurl;
							} else {
								$url = $newurl;
							}
							//extra \r's get picked up sometimes
							//i think only with relative redirects
							//this is a quick fix.
							$url = preg_replace("/\r/s","",$url);
							$redirecting = true;
						} else {
							$headerContents.=$currentLine;
						}
					}
				}
			} else {
				$this->error="$errstr ($errno)";
				$contents=false;
				$url = "";
			}
			@fclose($connection);
		}
		if (($method!="GET") && ($method!="POST")) {
			$contents=$headerContents."\n".$contents;
		}
		return $contents;
	}

	public function make_filesize( $size="" ,$precision=0) {
		$suffixes = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');

		if( $size === "" ) {
			$size = $this->size;
		}
		while ( (count($suffixes) > 1) && ($size > 1024) ){
			$size = $size / 1024;
			array_shift($suffixes);
		}
		$size = round($size,$precision);
		if($precision==0){ // compatible with the old make_filesize
			$size = intval($size);
		}
		$result = $size." ".array_shift($suffixes);
		return $result;
	}

	public function convertToUTF8($data, $charset = "CP1252") {

		include_once($this->store->get_config("code")."modules/mod_unicode.php");

		if (isset($data) && is_array($data)) {
			foreach($data as $key => $val){
				$data[$key] = $this->convertToUTF8($val, $charset);
			}
		} else
		if (is_object($data)) {
			foreach($data as $key => $val){
				$data->$key = $this->convertToUTF8($val, $charset);
			}
		} else {
			$data = unicode::convertToUTF8($charset, $data);
		}
		return $data;
	}

	public function resetloopcheck() {
		global $ARBeenHere;
		$ARBeenHere=array();
	}

/********************************************************************

  "safe" functions.

  The following functions are safe versions of existing functions
  above.
  - They don't change anything in the database.
    This means that to save/delete something, a user will need to call
    "system.save.data.phtml" or "system.delete.phtml" which check grants.
  - All functions except _get and _exists don't take a path as
    argument, they use the current objects path instead.

  These are meant to be used by 'pinp' versions of templates,
  meaning user defined templates. 'pinp' rewrites call to functions
  to the form '$this->_function'.

  All pinp files automatically first call CheckLogin('read').

********************************************************************/

	public function _call($function, $args="") {
		// remove possible path information (greedy match)
		if ( !( $function instanceof \Closure ) ) {
			$function = basename( (string) $function );
		}
		return $this->call($function, $args);
	}

	public function _call_super($arCallArgs="") {
	global $ARCurrent, $AR;
		$context = $this->getContext();
		if (!$arCallArgs) {
			$arCallArgs = end($ARCurrent->arCallStack);
		}
		$arSuperContext  = (array)$context['arSuperContext'];
		$arLibrary       = $context['arLibrary'];
		$arLibraryPath   = $context['arLibraryPath'];
		$arCallType      = $context['arCallTemplateType'];
		$arSuperPath     = $context['arCallTemplatePath'];
		$arLibrariesSeen = $context['arLibrariesSeen'];
		$arCallFunction  = $arSuperFunction = $context['arCallFunction'];
		if ($arLibrary) {
			$arSuperFunction = str_replace($arLibrary.':', '', $arCallFunction);
		}
		if (strpos($arSuperFunction, "::") !== false) {
			// template of a specific class defined via call("class::template");
			list($arBaseType, $arSuperFunction)=explode("::", $arSuperFunction);
		}
		// remove current library path from the arLibrariesSeen array so that
		// Ariadne will be able to re-enter the library and toggle the arSuperContext boolean there.
		unset($arLibrariesSeen[$arLibraryPath]);
		$arSuperContext[$arSuperPath.":".$arCallType.":".$arSuperFunction] = true;

		debug("call_super: searching for the template following (path: $arSuperPath; type: $arCallType; function: $arCallFunction) from $this->path");
		// FIXME: Redirect code has to move to getPinpTemplate()
		$redirects	= $ARCurrent->shortcut_redirect;
		if (isset($redirects) && is_array($redirects)) {
			$redirpath = $this->path;
			while (!$template['arTemplateId'] &&
				($redir = array_pop($redirects)) &&
				$redir["keepurl"] &&
				(substr($redirpath, 0, strlen($redir["dest"])) == $redir["dest"])
			) {
				debug("call_super: following shortcut redirect: $redirpath; to ".$redir["dest"]);
				$template = $this->getPinpTemplate($arCallFunction, $redirpath, $redir["dest"], false, $arLibrariesSeen, $arSuperContext);
				$redirpath = $redir['src'];
			}
			if (!$template["arTemplateId"]) {
				$template = $this->getPinpTemplate($arCallFunction, $redirpath, '', false, $arLibrariesSeen, $arSuperContext);
			}
		}
		if (!$template["arTemplateId"]) {
			$template = $this->getPinpTemplate($arCallFunction, $this->path, '', false, $arLibrariesSeen, $arSuperContext);
		}
		if ($template["arCallTemplate"] && $template["arTemplateId"]) {
			$exists = ar('template')->exists($template['arCallTemplatePath'],$template["arCallTemplate"]);
			if ( $exists ) {
				debug("call_super: found template ".$template["arCallTemplate"]." on object with id ".$template["arTemplateId"]);
				$arLibrary = $template['arLibrary'];
				debug("call_super: found template on ".$template["arTemplateId"]);
				if (is_int($arLibrary)) {
					// set the library name for unnamed libraries to 'current'
					// so that calls using getvar('arLibrary') will keep on working
					$arLibrary = "current";
				}
				if (!is_string($arCallArgs)) {
					$arCallArgs['arCallFunction'] = $arCallFunction;
					$arCallArgs['arLibrary'] = $arLibrary;
					$arCallArgs['arLibraryPath'] = $template["arLibraryPath"];
				}
				$ARCurrent->arCallStack[]=$arCallArgs;
				$this->pushContext(
					array(
						"scope"              => "pinp",
						"arSuperContext"     => $arSuperContext,
						"arLibrary"          => $arLibrary,
						"arLibraryPath"      => $template['arLibraryPath'],
						"arCallFunction"     => $arCallFunction,
						"arCurrentObject"    => $this,
						"arCallType"         => $template['arCallType'],
						"arCallTemplateName" => $template['arCallTemplateName'],
						"arCallTemplateNLS"  => $template['arCallTemplateNLS'],
						"arCallTemplateType" => $template['arCallTemplateType'],
						"arCallTemplatePath" => $template['arCallTemplatePath']
					)
				);
				$continue = true;
				$eventData = new baseObject();
				if ( !$AR->contextCallHandler ) { /* prevent onbeforecall from re-entering here */
					$AR->contextCallHandler = true;
					$eventData->arCallArgs = $arCallArgs;
					$eventData->arCallFunction = $arCallFunction;
					$eventData->arContext = $this->getContext();
					$eventData = ar_events::fire('onbeforecall', $eventData);
					$ARCurrent->arResult = $eventData->arResult;
					$AR->contextCallHandler = false;
					$continue = ($eventData!=false);
				}
				if ( $continue ) {
					set_error_handler(array('pobject','pinpErrorHandler'),error_reporting());
					$func = ar('template')->get($template['arCallTemplatePath'],$template['arCallTemplate']);
					if(is_callable($func)){
						$arResult = $func($this);
					}
					restore_error_handler();

					if ( !$AR->contextCallHandler ) { /* prevent oncall from re-entering here */
						$AR->contextCallHandler = true;
						$temp = $ARCurrent->arResult; /* event listeners will change ARCurrent->arResult */
						$eventData->arResult = $arResult;
						ar_events::fire('oncall', $eventData );
						$ARCurrent->arResult = $temp; /* restore correct result */
						$AR->contextCallHandler = false;
					}
				}
				array_pop($ARCurrent->arCallStack);
				$this->popContext();
			}
		}
		return $arResult;
	}

	public function _get($path, $function="view.html", $args="") {
		// remove possible path information (greedy match)
		if ( !($function instanceof \Closure) ) {
			$function = basename( (string) $function);
		}
		return $this->store->call($function, $args,
			$this->store->get(
				$this->make_path($path)));
	}

	public function _call_object($object, $function, $args="") {
		return $object->call($function, $args);
	}

	public function _ls($function="list.html", $args="") {
		// remove possible path information (greedy match)
		if ( ! ( $function instanceof \Closure ) ) {
			$function = basename( (string) $function );
		}
		return $this->store->call($function, $args,
			$this->store->ls($this->path));
	}

	public function _parents($function="list.html", $args="", $top="") {
		// remove possible path information (greedy match)
		if ( !($function instanceof \Closure ) ) {
			$function = basename( (string) $function);
		}
		return $this->parents($this->path, $function, $args, $top);
	}

	public function _find($criteria, $function="list.html", $args="", $limit=100, $offset=0) {
		$this->error = '';
		// remove possible path information (greedy match)
		if ( !($function instanceof \Closure ) ) {
			$function = basename( (string) $function);
		}
		$result = $this->store->call($function, $args,
			$this->store->find($this->path, $criteria, $limit, $offset));
		if ($this->store->error) {
			$this->error = ar::error( ''.$this->store->error, 1107, $this->store->error );
		}
		return $result;
	}

	public function _exists($path) {
		return $this->store->exists($this->make_path($path));
	}

	public function _implements($implements) {
		return $this->AR_implements($implements);
	}

	public function getvar($var) {
	global $ARCurrent, $ARConfig; // Warning: if you add other variables here, make sure you cannot get at it through $$var.

		if ($ARCurrent->arCallStack) {
			$arCallArgs=end($ARCurrent->arCallStack);
			if (isset($arCallArgs) && is_array($arCallArgs)) {
				extract($arCallArgs);
			} else if (is_string($arCallArgs)) {
				Parse_Str($arCallArgs);
			}
		}
		if (isset($$var) && ($var!='ARConfig')) {
			$result=$$var;
		} else if (isset($ARCurrent->$var)) {
			$result=$ARCurrent->$var;
		} else if (isset($ARConfig->pinpcache[$this->path][$var])) {
			$result=$ARConfig->pinpcache[$this->path][$var];
		} else if (isset($_POST[$var])) {
			$result=$_POST[$var];
		} else if (isset($_GET[$var])) {
			$result=$_GET[$var];
		} else if (($arStoreVars=$_POST["arStoreVars"]) && isset($arStoreVars[$var])) {
			$result=$arStoreVars[$var];
		} else if (($arStoreVars=$_GET["arStoreVars"]) && isset($arStoreVars[$var])) {
			$result=$arStoreVars[$var];
		}
		return $result;
	}

	public function _getvar($var) {
		return $this->getvar($var);
	}

	public function putvar($var, $value) {
		global $ARCurrent;

		$ARCurrent->$var=$value;
	}

	public function _putvar($var, $value) {
		return $this->putvar($var, $value);
	}

	public function _setnls($nls) {
		$this->setnls($nls);
	}

	// not exposed to pinp for obvious reasons
	public function sgKey($grants) {
		global $AR;
		if( !$AR->sgSalt || !$this->CheckSilent("config") ) {
			return false;
		}
		// serialize the grants so the order does not matter, mod_grant takes care of the sorting for us
		$this->_load("mod_grant.php");
		$mg = new mod_grant();
		$grantsarray = array();
		$mg->compile($grants, $grantsarray);
		$grants = serialize($grantsarray);
		return sha1( $AR->sgSalt . $grants . $this->path);
	}

	public function sgBegin($grants, $key = '', $path = '.') {
		global $AR;
		$result = false;
		$context = $this->getContext();
		$path    = $this->make_path($path);

		// serialize the grants so the order does not matter, mod_grant takes care of the sorting for us
		$this->_load("mod_grant.php");
		$mg = new mod_grant();
		$grantsarray = array();
		$mg->compile($grants, $grantsarray);

		if ($context['scope'] == 'pinp') {
			$checkgrants = serialize($grantsarray);
			$check = ( $AR->sgSalt ? sha1( $AR->sgSalt . $checkgrants . $path) : false ); // not using suKey because that checks for config grant
		} else {
			$check = true;
			$key = true;
		}
		if( $check !== false && $check === $key ) {
			$AR->user->grants = array(); // unset all grants for the current user, this makes sure GetValidGrants gets called again for this path and all childs
			$grantsarray = (array)$AR->sgGrants[$path];
			$mg->compile($grants, $grantsarray);
			$AR->sgGrants[$path] = $grantsarray;
			$result = true;
		}
		return $result;
	}

	public function sgEnd($path = '.') {
		global $AR;
		$AR->user->grants = array(); // unset all grants for the current user, this makes sure GetValidGrants gets called again for this path and all childs
		$path = $this->make_path( $path );
		unset($AR->sgGrants[$path]);
		return true; // temp return true;
	}

	public function sgCall($grants, $key, $function="view.html", $args="") {
		$result = false;
		if( $this->sgBegin($grants, $key ) ) {
			$result = $this->call($function, $args);
			$this->sgEnd();
		}
		return $result;
	}

	public function _sgBegin($grants, $key, $path = '.') {
		return $this->sgBegin($grants, $key, $path);
	}

	public function _sgEnd($path = '.') {
		return $this->sgEnd($path);
	}

	public function _sgCall($grants, $key, $function="view.html", $args="") {
		return $this->sgCall($grants, $key, $function, $args);
	}

	public function _widget($arWidgetName, $arWidgetTemplate, $arWidgetArgs="", $arWidgetType="lib") {
	global $AR, $ARConfig, $ARCurrent, $ARnls;

		$arWidgetName=preg_replace("/[^a-zA-Z0-9\/]/","",$arWidgetName);
		$arWidgetTemplate=preg_replace("/[^a-zA-Z0-9\.]/","",$arWidgetTemplate);
		$wgResult=null;
		if ($arWidgetType=="www") {
			$coderoot=$AR->dir->root;
		} else {
			$coderoot=$this->store->get_config("code");
		}
		if (file_exists($coderoot."widgets/$arWidgetName")) {
			if (file_exists($coderoot."widgets/$arWidgetName/$arWidgetTemplate")) {
				if (isset($arWidgetArgs) && is_array($arWidgetArgs)) {
					extract($arWidgetArgs);
				} else if (is_string($arWidgetArgs)) {
					Parse_str($arWidgetArgs);
				}
				include($coderoot."widgets/$arWidgetName/$arWidgetTemplate");
			} else {
				error("Template $arWidgetTemplate for widget $arWidgetName not found.");
			}
		} else {
			error(sprintf($ARnls["err:widgetnotfound"],$arWidgetName));
		}
		if ($wgResult) {
			return $wgResult;
		}
	}

	public function _getdata($varname, $nls="none", $emptyResult=false) {
		return $this->getdata($varname, $nls, $emptyResult);
	}

	public function _showdata($varname, $nls="none", $emptyResult=false) {
		$this->showdata($varname, $nls, $emptyResult);
	}

	public function _gettext($index=false) {
	global $ARnls;
		if (!$index) {
			return $ARnls;
		} else {
			return $ARnls[$index];
		}
	}

	public function _loadtext($nls, $section="") {
	global $ARnls, $ARCurrent;
		if( is_object($ARnls) ) {
			$ARnls->load($section, $nls);
			$ARnls->setLanguage($nls);
			$this->ARnls = $ARnls;
		} else { // older loaders and other shizzle

			$nls = preg_replace('/[^a-z]*/i','',$nls);
			$section = preg_replace('/[^a-z0-9\._:-]*/i','',$section);
			if (!$section) {
				include($this->store->get_config("code")."nls/".$nls);
				$this->ARnls = array_merge((array)$this->ARnls, $ARnls);
			} else {
				$nlsfile = $this->store->get_config("code")."nls/".$section.".".$nls;
				if(strpos($nlsfile, ':') === false && file_exists($nlsfile)) {
					include($nlsfile);
					$this->ARnls = array_merge((array)$this->ARnls, $ARnls);
				} else {
					// current result;
					$arResult = $ARCurrent->arResult;
					$this->pushContext(array());
						$oldnls = $this->reqnls;
						$this->reqnls = $nls;
						$this->CheckConfig($section, array('nls' => $nls));
						$this->reqnls = $oldnls;
					$this->popContext();
					// reset current result (CheckConfig may have changed it when it should not have).
					$ARCurrent->arResult = $arResult;
				}
			}
		}
	}

	public function _startsession() {
	global $ARCurrent;
		ldStartSession(0);
		return $ARCurrent->session->id;
	}

	public function _putsessionvar($varname, $varvalue) {
	global $ARCurrent;

		if ($ARCurrent->session) {
			return $ARCurrent->session->put($varname, $varvalue);
		} else {
			return false;
		}
	}

	public function _getsessionvar($varname) {
	global $ARCurrent;

		if ($ARCurrent->session) {
			return $ARCurrent->session->get($varname);
		} else {
			return false;
		}
	}

	public function _setsessiontimeout($timeout = 0) {
	global $ARCurrent;
		if ($ARCurrent->session) {
			return $ARCurrent->session->setTimeout($timeout);
		} else {
			return false;
		}
	}

	public function _killsession() {
	global $ARCurrent;

		if ($ARCurrent->session) {
			$ARCurrent->session->kill();
			unset($ARCurrent->session);
		}
	}

	public function _sessionid() {
	global $ARCurrent;
		if ($ARCurrent->session) {
			return $ARCurrent->session->id;
		} else {
			return 0;
		}
	}

	public function _resetloopcheck() {
		return $this->resetloopcheck();
	}

	public function _make_path($path="") {
		return $this->make_path($path);
	}

	public function _make_ariadne_url($path="") {
		return $this->make_ariadne_url($path);
	}

	public function _make_url($path="", $nls=false, $session=true, $https=null, $keephost=null) {
		return $this->make_url($path, $nls, $session, $https, $keephost);
	}

	public function _make_local_url($path="", $nls=false, $session=true, $https=null) {
		return $this->make_local_url($path, $nls, $session, $https);
	}

	public function _getcache($name, $nls='') {
		return $this->getcache($name, $nls);
	}

	public function _cached($name, $nls='') {
		return $this->cached($name, $nls);
	}

	public function _savecache($time="") {
		return $this->savecache($time);
	}

	public function _getdatacache($name) {
		return $this->getdatacache($name);
	}

	public function _savedatacache($name,$data,$time="")
	{
		return $this->savedatacache($name,$data,$time);
	}

	public function currentsite($path="", $skipRedirects = false) {
		global $ARCurrent, $ARConfig;
		if (!$path) {
			$path=$this->path;
		}
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		if (!$skipRedirects && @count($ARCurrent->shortcut_redirect)) {
			$redir = end($ARCurrent->shortcut_redirect);
			if ($redir["keepurl"] && substr($path, 0, strlen($redir["dest"])) == $redir["dest"]) {
				if (substr($config->site, 0, strlen($redir["dest"]))!=$redir["dest"]) {
					// search currentsite from the reference
					$config = ($ARConfig->cache[$redir['src']]) ? $ARConfig->cache[$redir['src']] : $this->loadConfig($redir['src']);
				}
			}
		}
		return $config->site;
	}

	public function parentsite($site) {
	global $ARConfig;
		$path=$this->store->make_path($site, "..");
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		return $config->site;
	}

	public function currentsection($path="") {
	global $ARConfig;
		if (!$path) {
			$path=$this->path;
		}
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		return $config->section;
	}

	public function parentsection($path) {
	global $ARConfig;
		$path=$this->store->make_path($path, "..");
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		return $config->section;
	}

	public function currentproject($path="") {
	global $ARConfig;
		if (!$path) {
			$path=$this->path;
		}
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		return $config->project;
	}

	public function parentproject($path) {
	global $ARConfig;
		$path=$this->store->make_path($path, "..");
		$config=($ARConfig->cache[$path]) ? $ARConfig->cache[$path] : $this->loadConfig($path);
		return $config->project;
	}

	public function validateFormSecret() {
		global $ARCurrent;
		if (!$ARCurrent->session) {
			return true;
		}

		if ($ARCurrent->session && $ARCurrent->session->data && $ARCurrent->session->data->formSecret) {
			$formSecret = $this->getvar("formSecret");
			return ($formSecret === $ARCurrent->session->data->formSecret);
		}
		return false;
	}

	public function _validateFormSecret() {
		return $this->validateFormSecret();
	}

	public function getValue($name, $nls=false) {
	global $ARCurrent;
		switch ($nls) {
			case "none":
				$result = $this->data->$name;
			break;
			case false:
				$nls = $ARCurrent->nls;
				if (!isset($this->data->$nls) || !isset($this->data->$nls->$name)) {
					$result = $this->data->$name;
					break;
				}
			default:
				$result = $this->data->$nls->$name;
		}
		return $result;
	}

	public function setValue($name, $value, $nls=false) {

	global $AR, $ARConfig;
		if ($value === null) {
			if ($nls && $nls!="none") {
				unset($this->data->$nls->$name);
				if (!count(get_object_vars($this->data->$nls))) {
					unset($this->data->$nls);
					unset($this->data->nls->list[$nls]);
					if (!count($this->data->nls->list)) {
						unset($this->data->nls->list);
						unset($this->data->nls);
					} else {
						if ($this->data->nls->default == $nls) {
							if ($this->data->nls->list[$ARConfig->nls->default]) {
								$this->data->nls->default = $ARConfig->nls->default;
							} else {
								list($this->data->nls->default) = each($this->data->nls->list);
							}
						}
					}
				}
			} else {
				unset($this->data->$name);
			}
		} else
		if (!$nls) {
			$this->data->$name = $value;
		} else {
			if (!$this->data->$nls) {
				$this->data->$nls = new baseObject;
				if (!$this->data->nls) {
					$this->data->nls = new baseObject;
					$this->data->nls->default = $nls;
				}
				$this->data->nls->list[$nls] = $AR->nls->list[$nls];
			}
			$this->data->$nls->$name = $value;
		}
	}

	public function showValue($name, $nls=false) {
		$result = $this->getValue($name, $nls);
		echo $result;
		return $result;
	}

	public function _getValue($name, $nls=false) {
		return $this->getValue($name, $nls);
	}

	public function _setValue($name, $value, $nls=false) {
		return $this->setValue($name, $value, $nls);
	}

	public function _showValue($name, $nls=false) {
		return $this->showValue($name, $nls);
	}

	public function _currentsite($path="", $skipRedirects = false) {
		return $this->currentsite( $path, $skipRedirects );
	}

	public function _parentsite($site) {
		return $this->parentsite($site);
	}

	public function _currentsection() {
		return $this->currentsection();
	}

	public function _parentsection($section) {
		return $this->parentsection($section);
	}

	public function _currentproject() {
		return $this->currentproject();
	}

	public function _parentproject($path) {
		return $this->parentproject($path);
	}

	public function _checkAdmin($user) {
		return $this->CheckAdmin($user);
	}

	public function _checkgrant($grant, $modifier=ARTHISTYPE, $path=".") {
		// as this is called within a pinp template,
		// all the grants are already loaded, so
		// checksilent will fullfill our needs
		$this->pushContext(array("scope" => "php"));
			$result = $this->CheckSilent($grant, $modifier, $path);
		$this->popContext();
		return $result;
	}

	public function _checkpublic($grant, $modifier=ARTHISTYPE) {

		return $this->CheckPublic($grant, $modifier);
	}

	public function _getcharset() {
		return $this->getcharset();
	}

	public function _count_find($query='') {
		return $this->count_find($this->path, $query);
	}

	public function _count_ls() {
		return $this->count_ls($this->path);
	}

	public function _HTTPRequest($method, $url, $postdata = "", $port=80) {
		return $this->HTTPRequest($method, $url, $postdata, $port);
	}

	public function _make_filesize( $size="" ,$precision=0) {
		return $this->make_filesize( $size ,$precision);
	}

	public function _convertToUTF8($data, $charset = "CP1252") {
		return $this->convertToUTF8($data,$charset);
	}

	public function _getuser() {
	global $AR;
		if ($AR->pinp_user && $AR->pinp_user->data->login == $AR->user->data->login) {
			$user = $AR->pinp_user;
		} else {
			$this->pushContext(array("scope" => "php"));
				if ( $AR->user instanceof ariadne_object ) {
					$user = current($AR->user->get(".", "system.get.phtml"));
				} else {
					$user = $AR->user;
				}
				$AR->pinp_user = $user;
			$this->popContext();
		}
		return $user;
	}

	public function ARinclude($file) {
		include($file);
	}

	public function _load($class) {
		// only allow access to modules in the modules directory.
		$class = preg_replace('/[^a-z0-9\._]/i','',$class);
		include_once($this->store->get_config("code")."modules/".$class);
	}

	public function _import($class) {
		// deprecated
		return $this->_load($class);
	}

	public function html_to_text($text) {
		$trans = array_flip(get_html_translation_table(HTML_ENTITIES));
		$cb  = function($matches) use ($trans) {
			return strtr($matches[1],$trans);
		};
		//strip nonbreaking space, strip script and style blocks, strip html tags, convert html entites, strip extra white space
		$search_clean = array("%&nbsp;%i", "%<(script|style)[^>]*>.*?<\/(script|style)[^>]*>%si", "%<[\/]*[^<>]*>%Usi", "%\s+%");
		$replace_clean = array(" ", " ", " ", " ");

		$text = preg_replace_callback(
			"%(\&[a-zA-Z0-9\#]+;)%s",
			$cb,
			$text
		);
		$text = preg_replace($search_clean, $replace_clean, $text);
		return $text;
	}

	public function _html_to_text($text) {
		return $this->html_to_text($text);
	}

	public function _newobject($filename, $type) {
		$newpath=$this->make_path($filename);
		$newparent=$this->store->make_path($newpath, "..");
		$data=new baseObject;
		$object=$this->store->newobject($newpath, $newparent, $type, $data);
		$object->arIsNewObject=true;
		return $object;
	}

	public function _save($properties=array(), $vtype="") {
		if (isset($properties) && is_array($properties)) {
			// isn't this double work, the save function doesn this again
			foreach ($properties as $prop_name => $prop) {
				foreach ($prop as $prop_index => $prop_record) {
					$record = array();
					foreach ($prop_record as $prop_field => $prop_value) {
						switch (gettype($prop_value)) {
							case "integer":
							case "boolean":
							case "double":
								$value = $prop_value;
							break;
							default:
								$value = $prop_value;
								if (substr($prop_value, 0, 1) === "'" && substr($prop_value, -1) === "'"
										&& "'".AddSlashes(StripSlashes(substr($prop_value, 1, -1)))."'" == $prop_value) {
									$value = stripSlashes(substr($prop_value,1,-1));
									// todo add deprecated warning
								}
						}
						$record[$prop_field] = $value;
					}
					$properties[$prop_name][$prop_index] = $record;
				}
			}
		}

		if ($this->arIsNewObject && $this->CheckSilent('add', $this->type)) {
			unset($this->data->config);
			$result = $this->save($properties, $vtype);
		} else if (!$this->arIsNewObject && $this->CheckSilent('edit', $this->type)) {
			$this->data->config = current($this->get('.', 'system.get.data.config.phtml'));
			$result = $this->save($properties, $vtype);
		}
		return $result;
	}

	public function _is_supported($feature) {
		return $this->store->is_supported($feature);
	}

	/*
		since the preg_replace() function is able to execute normal php code
		we have to intercept all preg_replace() calls and parse the
		php code with the pinp parser.
	*/


	/*	this is a private function used by the _preg_replace wrapper */
	// FIXME: remove this function when the minimal php version for ariadne is raised to php 7.0
	protected function preg_replace_compile($pattern, $replacement) {
	global $AR;
		include_once($this->store->get_config("code")."modules/mod_pinp.phtml");
		preg_match("/^\s*(.)/", $pattern, $regs);
		$delim = $regs[1];
		if (@eregi("\\${delim}[^$delim]*\\${delim}.*e.*".'$', $pattern)) {
			$pinp = new pinp($AR->PINP_Functions, 'local->', '$AR_this->_');
			return substr($pinp->compile("<pinp>$replacement</pinp>"), 5, -2);
		} else {
			return $replacement;
		}
	}

	public function _preg_replace($pattern, $replacement, $text, $limit = -1) {
		if (version_compare(PHP_VERSION, '7.0.0', '<')) {
			if (isset($pattern) && is_array($pattern)) {
				$newrepl = array();
				reset($replacement);
				foreach ($pattern as $i_pattern) {
					list(, $i_replacement) = each($replacement);
					$newrepl[] = $this->preg_replace_compile($i_pattern, $i_replacement);
				}
			} else {
				$newrepl = $this->preg_replace_compile($pattern, $replacement);
			}
		} else {
			// php7 is safe, no more eval
			$newrepl = $replacement;
		}
		return preg_replace($pattern, $newrepl, $text, $limit);
	}

	/* ob_start accepts a callback but we don't want that
	 * this wrapper removes the arguments from the ob_start call
	 */
	public function _ob_start() {
		return ob_start();
	}

	public function _loadConfig($path='') {
		return clone $this->loadConfig($path);
	}

	public function _loadUserConfig($path='') {
		return $this->loadUserConfig($path);
	}

	public function _loadLibrary($name, $path) {
		return $this->loadLibrary($name, $path);
	}

	public function _resetConfig($path='') {
		return $this->resetConfig($path);
	}

	public function _getLibraries($path = '') {
		return $this->getLibraries($path);
	}


	public function _getSetting($setting) {
	global $AR;

		switch ($setting) {
			case 'www':
			case 'dir:www':
				return $AR->dir->www;
			case 'images':
			case 'dir:images':
				return $AR->dir->images;
			case 'ARSessionKeyCheck':
				$result = null;
				if (function_exists('ldGenerateSessionKeyCheck')) {
					$result = ldGenerateSessionKeyCheck();
				}
				return $result;
			break;
			case 'nls:list':
				return $AR->nls->list;
			break;
			case 'nls:default':
				return $AR->nls->default;
			break;
			case 'svn':
				return $AR->SVN->enabled;
			break;
		}
	}

	public function __call($name,$arguments) {
		if ( $name[0] == '_' ) {
			$fname = substr($name, 1);
			if ( isset($this->{$fname}) && $this->{$fname} instanceof \Closure ) {
				\Closure::bind( $this->{$fname}, $this );
				return call_user_func_array( $this->{$fname}, $arguments);
			}
		}
		switch($name) {
			case "implements":
				return $this->AR_implements($arguments[0]);
			break;
			default:
				trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $name), E_USER_ERROR);
				return false;
			break;
		}
	}

	static public function pinpErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		global $nocache;
		if (($errno & error_reporting()) == 0) {
			return true;
		}

		$nocache = true;
		$context = pobject::getContext();
		if ($context["arLibraryPath"]) { //  != null) {
			$msg = "Error on line $errline in ".$context['arCallTemplateType'].'::'.$context['arCallFunction'] ." in library ".$context["arLibraryPath"] ."\n".$errstr."\n";
		} else {
			$msg = "Error on line $errline in ".$context['arCallTemplateType'].'::'.$context['arCallFunction'] ." on object ".$context['arCurrentObject']->path."\n".$errstr."\n";
		}
		$display = ini_get('display_errors');

		if($display) {
			echo $msg;
		}
		error_log($msg);

		return false;
	}

} // end of ariadne_object class definition

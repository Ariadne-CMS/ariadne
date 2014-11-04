<?php


    /******************************************************************
     mod_stats.php                                         Muze Ariadne
     ------------------------------------------------------------------
     Author: Florian Overkamp (info@obsimref.com)
     Date: 4 february 2003

     Copyright 2003 ObSimRef and Muze

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

     Description:

	   This module calls the stats tools with the given
	   options.

    ******************************************************************/

 	$StatsSupportedTools = "phpOpenTracker";

	class stats {

		function stats() {
			// class constructor - nothing to do
		}

		function log() {
			// all info is gathered from the php/webserver environment
			global $AR, $StatsSupportedTools;
			global $path;

			if($AR->Stats->makestats && (stristr($StatsSupportedTools, $AR->Stats->tool)!=false)) {
				// Go Go Go
				$client_id = $_SERVER[$AR->Stats->clientvar];
				$referer =  $_SERVER['HTTP_REFERER'];

				//$cookie=ldGetCredentials();
				//$username = $cookie[$ARCurrent->session->id]['login'];

				// log the entry if there is a CLIENT_ID
				if((isset($client_id)) || (!$AR->Stats->logdefault)) {
					// Take logdefault if there is no CLIENT_ID

					// Mangle the path (adjust to your own taste)
					$logpath = $path;
					$proceed = true;

					// Small tests for stuff we never want to log (i.e. access to /system/)
					// set $proceed to false if you wish to skip this log
					if(is_array($AR->Stats->ignore)) {
						foreach($AR->Stats->ignore as $ignorepath) {
							if(substr($logpath, 0, strlen($ignorepath)) == $ignorepath) $proceed = false;
							// path may contain NLS data
							if(substr($logpath, 3, strlen($ignorepath)) == $ignorepath) $proceed = false;
						}
					}

					if($proceed) {
						// Do the logging
						debug ("mod_stats::log: path = $logpath", "class");
						switch($AR->Stats->tool) {
       	                        			case "phpOpenTracker":	$this->opentrackerlog($logpath, $referer, $client_id);
						}
					} else {
						debug ("mod_stats::log: path NOT logged ($logpath)", "class");
					}
				}
			}
		}

//
// All function below are tool-specific
//

		function opentrackerlog($logpath, $referer, $client_id) {
			// opentracker specific
			global $AR;

			// phpOpenTracker User Tracking
			require_once($AR->Stats->path);
			debug ("mod_stats::opentrackerlog: logging $logpath to phpOpenTracker", "class");
			// log the visitor
			if($client_id != "") {
				debug ("mod_stats::opentrackerlog: client_id = $client_id", "class");
				@phpOpenTracker::log( Array(
					'document' => $logpath,
					'referer' => $referer,
					'client_id' => $client_id)
				);
			} else {
				debug ("mod_stats::opentrackerlog: logging without client_id", "class");
				@phpOpenTracker::log( Array(
					'document' => $logpath,
					'referer' => $referer)
				);
			}
			// ---
		}

		// Class end
	}

<?php
    /******************************************************************
     mod_debug.php                                         Muze Ariadne
     ------------------------------------------------------------------
     Author: Wouter Commandeur (Muze) (info@muze.nl)
     Date: 18 march 2003

     Copyright 2003 Muze

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

	Debug Module

    ******************************************************************/

	$DB["all"]=5;
	$DB["store"]=4;
	$DB["class"]=3;
	$DB["object"]=2;
	$DB["pinp"]=1;
	$DB["off"]=0;
	$DB["level"]=$DB["off"];
	$DB["stream"]="all";
	$DB["file"]=false;
	$DB["method"]["loader"] = true;
	$DB["method"]["firebug"] = false;
	$DB["method"]["syslog"] = false;
	$DB["method"]["file"] = false;
	$DB["wasUsed"] = 0;


	function debugon($level="pinp", $stream="all") {
	global $DB;
		$debugison = "";
		if( $DB["method"]["file"] && $DB["file"] ) {
			$DB["fp"]=@fopen($DB["file"], "a+");
			if ($DB["fp"]) {
				$debugison .= " [file ".$DB["file"]."]";
			}
		}
		/* Check if we're debugging to the loader debug_print function and
		   the loader is capable of debugging. 
		*/
		if( $DB["method"]["loader"] && function_exists("debug_print")) {
			$debugison .= " [loader]";
		}
		if( $DB["method"]["syslog"] ) {
			$debugison .= " [syslog]";
		}
		if( $DB["method"]["firebug"] ) {
			$debugison .= " [firebug]";
		}
		if( $debugison != "" ) {
			$DB["level"] = $DB[$level];
			$DB["stream"] = $stream;
			debug("Debugging ON ".$debugison,$level,$stream);
		}
		$DB["wasUsed"]++;
		return true;
	}

	function debugoff() {
	global $DB;
		$debugisoff = "";
		if( $DB["method"]["file"] && $DB["file"] && $DB["fp"] ) {
			@fclose($DB["fp"]);
			$debugisoff .= " [file ".$DB["file"]."]";
		}
		/* Check if we're debugging to the loader debug_print function and
		   the loader is capable of debugging. 
		*/
		if( $DB["method"]["loader"] && function_exists("debug_print") ) {
			$debugisoff .= " [loader]";
		}
		if( $DB["method"]["syslog"] ) {
			$debugisoff .= " [syslog]";
		}
		if( $debugisoff != "" && $DB["level"] > 0) {
			debug("Debugging OFF ".$debugisoff,$DB["level"],$DB["stream"]);
			if( $DB["fp"] ) {
				@fclose($DB["fp"]);
			}
			$DB["level"]=$DB["off"];
		}
		return true;
	}

	function debuglevel($level) {
		global $DB;
		return ($DB["level"] >= $DB[$level]);
	}

	function debug($text, $level="pinp", $stream="all", $indent="") {
	global $DB, $DB_INDENT;
		if ( ! isset($DB[$level]) ) {
			$level = "pinp";
		}
		if( $DB["level"] >= $DB[$level] && (($DB["stream"]=="all")||($DB["stream"]==$stream) ) ) {
			/* format the message */

			$message = "[".$level."][".$stream."] ".$text;
			$timestamping = date("H:i:s");
			/* handle indentation */
			if( $indent=="OUT" ) {
				$DB_INDENT = substr($DB_INDENT,0,-2);
			}
			if( $indent=="IN" ) {
				$DB_INDENT.="  ";
			}

			if( $DB["method"]["file"] && $DB["fp"] ) {
				@fwrite($DB["fp"], "(".$timestamping.")".$DB_INDENT.$message."\n");
				@fflush($DB["fp"]);
			}
			/* Check if we're debugging to the loader debug_print function and
			   the loader is capable of debugging. 
			*/
			if( $DB["method"]["loader"] && function_exists("debug_print") ) {
				debug_print( "(".$timestamping.")".$DB_INDENT.$message."\n" );
				flush();
			}
			if( $DB["method"]["syslog"] ) {
				syslog(LOG_NOTICE,"(Ariadne) ".$DB_INDENT.$message);
			}
			if ( $DB["method"]["firebug"] ) {
				ar('beta/firebug')->log($text,"[".$level."][".$stream."]");
			}
		}
	}
	
	function debug_serialize( $var, $level="pinp", $stream="all" ) {
		global $DB;
		if( $DB["level"] >= $DB[$level] && (($DB["stream"]=="all")||($DB["stream"]==$stream) ) ) {
			return serialize($var);
		}
		return ""; 
	}

?>
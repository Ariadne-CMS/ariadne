<?php

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
	$DB["method"]["syslog"] = false;
	$DB["method"]["file"] = false;


	function debugon($level="pinp", $stream="all") {
	global $DB;
		$debugison = "";
		if( $DB["method"]["file"] && $DB["file"] ) {
			$DB["fp"]=fopen($DB["file"], "a+");
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
		if( $debugison != "" ) {
			$DB["level"] = $DB[$level];
			$DB["stream"] = $stream;
			debug("Debugging ON ".$debugison,$level,$stream);
		}
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
		if( $debugisoff != "") {
			debug("Debugging OFF ".$debugisoff,$level,$stream);
			if( $DB["fp"] ) {
				@fclose($DB["fp"]);
			}
			$DB["level"]=$DB["off"];
		}
	}




	function debug($text, $level="pinp", $stream="all", $indent="") {
	global $DB, $DB_INDENT;

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
				fwrite($DB["fp"], "(".$timestamping.")".$message."\n");
				fflush($DB["fp"]);
			}
			/* Check if we're debugging to the loader debug_print function and
			   the loader is capable of debugging. 
			*/
			if( $DB["method"]["loader"] && function_exists("debug_print") ) {
				debug_print( "(".$timestamping.")".$message."\n" );
			}
			if( $DB["method"]["syslog"] ) {
				syslog(LOG_NOTICE,"(Ariadne) ".$message);
			}
		}
	}

?>
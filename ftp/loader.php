<?php
	if ($argc > 1) {
		$configfile=$argv[1];
	} else {
		$configfile="default.phtml";
	}

	include("../www/ariadne.inc");
	require($ariadne."/configs/ariadne.phtml");
    require($ariadne."/configs/ftp/$configfile");
	require($ariadne."/configs/store.phtml");
	require($ariadne."/includes/loader.ftp.php");
	require($ariadne."/configs/sessions.phtml");
	require($ariadne."/stores/".$store_config["dbms"]."store.phtml");
	require($ariadne."/nls/en");
	require($ariadne."/modules/mod_mimemagic.php");
	
	require($ariadne."/modules/mod_virusscan.php");

		/* this function has been taken from the php manual		*/
		
		function ftp_ErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) {
			if ($errno!= 2 && $errno!=8 ) {
			    // timestamp for the error entry
			    $dt = date("Y-m-d H:i:s (T)");

			    // define an assoc array of error string
			    // in reality the only entries we should
			    // consider are 2,8,256,512 and 1024
			    $errortype = array (
			                1   =>  "Error",
			                2   =>  "Warning",
			                4   =>  "Parsing Error",
			                8   =>  "Notice",
			                16  =>  "Core Error",
			                32  =>  "Core Warning",
			                64  =>  "Compile Error",
			                128 =>  "Compile Warning",
			                256 =>  "User Error",
			                512 =>  "User Warning",
			                1024=>  "User Notice"
			                );
			    // set of errors for which a var trace will be saved
			    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
			    
			    $err = "<errorentry>\n";
			    $err .= "\t<datetime>".$dt."</datetime>\n";
			    $err .= "\t<errornum>".$errno."</errnumber>\n";
			    $err .= "\t<errortype>".$errortype[$errno]."</errortype>\n";
			    $err .= "\t<errormsg>".$errmsg."</errormsg>\n";
			    $err .= "\t<scriptname>".$filename."</scriptname>\n";
			    $err .= "\t<scriptlinenum>".$linenum."</scriptlinenum>\n";

			    if (in_array($errno, $user_errors))
			        $err .= "\t<vartrace>".wddx_serialize_value($vars,"Variables")."</vartrace>\n";
			    $err .= "</errorentry>\n\n";
			    
				debug($err);
			}
		}


		function ftp_OpenDC() {
		global $FTP, $ftp_config;

			$FTP->DC["transfered"]=0;
			if ($FTP->DC["mode"]==="active") {
				$socket=socket_create(AF_INET, SOCK_STREAM, 0);
				if ($socket>=0) {
					debug("ftp: opened socket");
					@socket_bind($socket, $ftp_config['server_ip']);
					$result=socket_connect($socket, $FTP->DC["address"], $FTP->DC["port"]);
					if ($result < 0) {
						ftp_Tell(425, "Couldn't build data connection (rm: connection error: ".strerror($result).")");
						$result=false;
					} else {
						debug("ftp: connected");
						$FTP->DC["msgsocket"]=$socket;
						$result=true;
					}
				} else {
					ftp_Tell(425, "Couldn't build data connection (rm: socket error: ".strerror($socket).")");
					$result=false;
				}
			} else {
				// do passive mode
				debug("ftp::OpenDC waiting on socket accept"); 
				$msgsocket=socket_accept($FTP->DC["socket"]);
				debug("ftp::OpenDC socket accepted? (".$msgsocket.")");
				if ($msgsocket < 0) {
					ftp_Tell(425, "Couldn't build data connection (rm: socket error: ".strerror($socket).")");
					$result=false;
				} else {
					debug("ftp: accept_connect returned $msgsocket");
					socket_set_blocking($msgsocket, TRUE);
					debug("ftp: connected ($msgsocket)");
					$FTP->DC["msgsocket"]=$msgsocket;
					$result=true;
				}
				socket_close($FTP->DC["socket"]);
			}

			if ($result) {
				if ($FTP->DC["ob_active"]) {
					debug("error: OOPS, dc ob not closed!!");
				} else {
					$FTP->DC["ob_active"]=true;
					if (ob_start("ftp_WriteDC")) {
						debug("ftp_OpenDC:: opening ob");
					} else {
						debug("ftp_OpendDC:: could not open ob");
					}
				}
			}
			return $result;
		}

		function ftp_GetPasv() {
		global $FTP;
			// client issued 'pasv' command
			// so lets try to bind a socket to a port
			if ($FTP->DC["socket_desc"]) {
				// we alread got a socket open.. let's use it
				$result = $FTP->DC["socket_desc"];
			} else {
				$socket=socket_create(AF_INET, SOCK_STREAM, 0);
				if ($socket>=0) {
					debug("ftp: open socket ($socket) (pasv mode)");

					// FIXME: make this configurable!
					$bound=0;
					$port=12000;
					while (!$bound && $port<=12100) {
						$bound=socket_bind($socket, $FTP->server_ip, $port);
						debug("ftp::pasv socket_bind port $port ($bound)");
						if (!$bound) {
							$port++;
						}
					}

					if ($bound) {
						$ret=socket_listen($socket, 1);
						if ($ret < 0) {
							ftp_Tell(425, "Couldn't build data connection (rm: socket error:".strerror($socket).")");
						} else {
							$FTP->DC["mode"]="passive";
							$FTP->DC["socket"]=$socket;
							debug("ftp: listening on port $port");
							$result=str_replace(".", ",", $FTP->server_ip);
							$result.=",".(((int)$port) >> 8);
							$result.=",".($port & 0x00FF);
							//$FTP->DC["socket_desc"]=$result;
						}
					} else {
						ftp_Tell(425, "Couldn't build data connection:  couldn't bind to a socket");
					}

				} else {
					ftp_Tell(425, "Couldn't build data connection (rm: socket error:".strerror($socket).")");
					$result=false;
				}
			}

			return $result;
		}


		function ftp_WriteDC($bdata) {
		global $FTP;
			/*
				make a copy of $data otherwise we will crash php
				(you can't write to data from an output buffer)
			*/
			if ($FTP->resume) {
				debug("ftp::WriteDC() truncating data");
				$data = substr($bdata, $FTP->resume);
			} else {
				$data = $bdata;
			}

			/* free unused data */
			unset($bdata);

			if (strlen($data)) {
				debug("ftp::WriteDC([data]) (".strlen($data).")");
				if ($FTP->DC["type"]==="A") {
					$offset = 0;
					$chunk = substr($data, $offset, 4096);
					while ($chunk!==false) {
						$chunk=str_replace("\n", "\r\n", $chunk);
						$len = strlen($chunk);
						debug("ftp_WriteDC:: writing chunk([chunk], $offset, 4096) (".$len.")");
						if (!socket_write($FTP->DC["msgsocket"], $chunk, $len)) {
							debug("ftp_WriteDC:: chunk ERROR write $len bytes!");
							$chunk = false;
						} else {
							debug("ftp_WriteDC:: chunk success");
							//$offset+=strlen($chunk);
							$offset += 4096;
							$FTP->DC["transfered"]+=strlen($data);
							$chunk = substr($data, $offset, 4096);
						}
					}
					
				} else {
					$len=strlen($data);
					debug("ftp_WriteDC:: writing len (".$len.")");
					if (!socket_write($FTP->DC["msgsocket"], $data, $len)) {
						debug("ftp_WriteDC:: ERROR writing $len bytes!");
					} else {
						debug("ftp_WriteDC:: success");
					}
					$FTP->DC["transfered"]+=strlen($data);
				}
			}

			return "";	// empty string
		}

		function ftp_ReadDC() {
		global $FTP;
			$data = socket_read($FTP->DC["msgsocket"], 3000, PHP_BINARY_READ);
			if (strlen($data) && ($FTP->DC["type"]==="A")) {
				if ($data[strlen($data)-1]==="\r") {
					$postdata = socket_read($FTP->DC["msgsocket"], 1, PHP_BINARY_READ);
					$data.=$postdata;
				}
				$data=str_replace("\r\n", "\n", $data);
			}
			debug("ftp::ReadDC() (".strlen($data).")");
			$FTP->DC["transfered"]+=strlen($data);
			return $data;
		}

		function ftp_CloseDC() {
		global $FTP;
			if ($FTP->DC["ob_active"]) {
				debug("ftp::CloseDC:: closing output buffer");
				ob_end_flush();
				debug("ftp::CLoseDC:: ok, ob closed");
				$FTP->DC["ob_active"]=false;
			}

			debug("ftp: closing connection");
			$con=$FTP->DC["msgsocket"];
			if ($con) {
				debug("ftp::CloseDC:: closing connection");
				socket_close($con);
				debug("ftp::CloseDC:: connection closed");
			}
		}

		function ftp_TranslatePath(&$path, &$listMode, &$template) {
		global $FTP;
			$listMode="";
			$template="";
			$absolute = ($path[0] === '/') ? true : false;
			$path=$FTP->site.$FTP->store->make_path($FTP->cwd, $path);
			while (ereg('/#([^/]*)#/', $path, $regs) && $regs[1]) {
				$listMode=$regs[1];
				$path=str_replace("/#".$listMode."#/", "/", $path);
			}
			if (!$listMode) {				
				if (!$absolute && $FTP->listMode) {
					$listMode=$FTP->listMode;
				} else {
					$listMode=$FTP->defaultListMode;
				}
			}
			debug("ftp: Translate $debug_path:: (FTP->listMode = '$FTP->listMode', listMode = '$listMode', path = '$path', template = '$template')");
		}

		function ftp_TranslateTemplate(&$path, &$template) {
		global $FTP;
			$parent = $FTP->store->make_path($path, "..");
			$template = substr($path, strlen($parent), -1);
			$path = $parent;
		}

		function ftp_Run() {
		global $FTP, $ARCurrent, $ARBeenHere;

			while (ftp_FetchCMD($cmd, $args)) {
				$ARBeenHere=Array();		
				$ARCurrent->arLoginSilent = 0;
				$ARCurrent->ftp_error = "";

				if ($last_cmd != 'REST') {
					$FTP->resume = 0;
				}
				switch ($cmd) {
					case 'REST':
						$FTP->resume = (int)$args;
						ftp_Tell(350, 'Restarting at '.$FTP->resume.'.');
					break;
					case 'QUIT':
						ftp_Tell(221, "Goodbye.");
						/* check if we have to close a 'passive' socket */
						if ($FTP->DC["socket_desc"]) {
							socket_close($FTP->DC["socket"]);
						}
						return 0;
					break;
					case 'PWD':
						$dir=$FTP->cwd;
						if ($FTP->listMode) {
							$dir="/#".$FTP->listMode."#".$dir;
						}
						if (strlen($dir)>1) {
							$dir=substr($dir,0,-1);
						}
						ftp_Tell(257, "\"$dir\" is current directory.");
					break;
					case 'HELP':
						ftp_Tell(214, "not implemented" );
					break;
					case 'PORT':
						$FTP->DC["mode"]="active";
						$host=explode(",",$args);
						$address=$host[0].".".$host[1].".".$host[2].".".$host[3];
						$FTP->DC["address"]=$address;
						$port=((int)$host[4]) << 8;
						$port+=(int)$host[5];
						$FTP->DC["port"]=$port;
						ftp_Tell(200, "ok, connecting to $address $port");
					break;
					case 'PASV':
						$port=ftp_GetPasv();
						if ($port) {
							ftp_Tell(227, "Entering Passive Mode ($port)");
						}
					break;
					case 'CDUP':
						$cwd=$FTP->store->make_path($FTP->cwd, "..");
						if ($FTP->store->exists($FTP->site.$cwd)) {
							$FTP->cwd=$cwd;
							ftp_Tell(250, "CDUP succesfull");
						} else {
							ftp_Tell(550, "CDUP not succesfull");
						}
					break;
					case 'CWD':
						/* if CWD path is absolute then listmode is set to
						the default value */

						$absolute = ($args[0]=="/") ? true : false;
						if ($absolute) {
							$FTP->listMode=$FTP->defaultListMode;
						}

						$path=$FTP->store->make_path($FTP->site.$FTP->cwd, $args);
						debug("ftp: cwd absolute path is ($path)");
						while (ereg('/#([^/]*)#/', $path, $regs) && $regs[1]) {
							$FTP->listMode=$regs[1];
							$path=str_replace("/#".$FTP->listMode."#/", "/", $path);
						}
						$cwd=$FTP->store->make_path($FTP->cwd, $path);
						if ($FTP->store->exists($FTP->site.$cwd)) {
							$result=current($FTP->store->call("system.get.phtml", "",
										$FTP->store->get($FTP->site.$cwd)));
							if ($result->type==="pshortcut") {
								debug("ftp: shortcut: ".$result->data->path);
								$cwd=$FTP->store->make_path($cwd, $result->data->path);
							}

							$FTP->cwd=$cwd;
							debug("ftp: cwd ($cwd) listmode(".$FTP->listMode.")");
							ftp_Tell(250, "CWD succesfull (listmode = ".$FTP->listMode.")");
						} else {
							ftp_Tell(550, "'$cwd' no such file or directory");
						}
					break;

					case 'TYPE':
						if (eregi('a|i', $args)) {
							$FTP->DC["type"]=strtoupper($args);
							ftp_Tell(200, "Type set to ".$args);
						} else {
							ftp_Tell(500, "Type $args not valid");
						}
					break;

					case 'SIZE':
						$path = $args;
						ftp_TranslatePath($path, $listMode);
						switch ($listMode) {
							case 'templates':
								ftp_TranslateTemplate($path, $template);
								$getmode = "templates";
								
								$result = current(
											$FTP->store->call("ftp.template.exists.phtml", 
																Array("arRequestedTemplate" => $template),
																$FTP->store->get($path)));
								$file_size = $result["size"];

								ftp_Tell(213, (int)$file_size);
							break;
							default:
								if ($FTP->store->exists($path)) {
									$size = $FTP->store->call(
											"ftp.$listMode.size.phtml",
											"",
											$FTP->store->get($path));
									ftp_Tell(213, (int)$size[0]);
								} else {
									ftp_Tell(550, "No such file or directory");
								}
							break;
						}
					break;

					case 'RNFR':
						$rename_src_path = $args;
						ftp_TranslatePath($rename_src_path, $rename_src_listMode);
						if ($listMode === "templates") {
							ftp_TranslateTemplate($rename_src_path, $rename_src_template);
							$result = $FTP->store->call(
											"ftp.template.exists.phtml", 
											Array(
												"arRequestedTemplate" => $rename_src_template
											),
											$FTP->store->get($path));

							if (is_array($result) && current($result)) {
								ftp_Tell(350, "template exists, supply destination name.");
							} else {
								ftp_Tell(550, "template [".$rename_src_template."] does not exists.");
								$rename_src_path = "";
							}

						} else 
						if ($FTP->store->exists($rename_src_path)) {
							ftp_Tell(350, "Object exists, supply destination name.");
						} else {
							ftp_Tell(550, "Object [".$rename_src_path."] does not exists.");
							$rename_src_path = "";
						}
					break;

					case 'RNTO':
						if ($rename_src_path) {
							$rename_dest_path = $args;
							ftp_TranslatePath($rename_dest_path, $rename_dest_listMode);
							if ($rename_dest_listMode === $rename_src_listMode) {
								if ($rename_dest_listMode === "templates") {
									$temp = $args;
									if ($temp[strlen($temp)-1] === "/") {
										$rename_dest_template = $rename_src_template;
									} else {
										ftp_TranslateTemplate($rename_dest_path, $rename_dest_template);
									}
									$do_move = $FTP->store->exists($rename_dest_path);
								} else {
									$temp = $args;
									if ($FTP->store->exists($rename_dest_path)) {
										$parent = $FTP->store->make_path($rename_src_path, "..");
										$file = substr($rename_src_path, strlen($parent));
										$rename_dest_path.=$file;
									}
									$do_move = !$FTP->store->exists($rename_dest_path);
								}

								if ($do_move) {
									debug("ftp::RENAME ($rename_src_path, $rename_dest_path, ".$rename_src_listMode.", $rename_src_template, $rename_dest_template)");
									$FTP->store->call("ftp.".$rename_src_listMode.".rename.phtml", 
													Array(
														"source" => $rename_src_path,
														"target" => $rename_dest_path,
														"source_template" => $rename_src_template,
														"target_template" => $rename_dest_template 
													),
													$FTP->store->get($rename_src_path));

									if ($ARCurrent->ftp_error) {
										ftp_Tell(550, $ARCurrent->ftp_error);
										unset($ARCurrent->ftp_error);
									} else {
										ftp_Tell(250, "Rename successfull.");								
									}
									$rename_src_path = "";
								} else {
									ftp_Tell(550, "Object [".$args."] does already exist.");
								}
							} else {
								ftp_Tell(550, "Moving objects between different modeses is not supported (yet).");
							}
						} else {
							ftp_Tell(550, "Expected RNFR");
						}
					break;

					case 'RETR':
						$path=$args;
						ftp_TranslatePath($path, $listMode);
						switch ($listMode) {
							case "templates":
								$reqpath = $path;
								ftp_TranslateTemplate($path, $template);
								$getmode = "templates";
								
								$result = current(
											$FTP->store->call("ftp.template.exists.phtml", 
																Array("arRequestedTemplate" => $template),
																$FTP->store->get($path)));
								$file_size = $result["size"];
							break;
							default:
								$file_size = current(
											$FTP->store->call("ftp.files.size.phtml", "",
																$FTP->store->get($path)));
								$getmode = "files";
							break;
						}

						debug("ftp: opening $path / template $template");

						if (ftp_OpenDC()!==false) {
							if ($FTP->store->exists($path)) {

								$file_size -= $FTP->resume;
								ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ASCII' : 'BINARY')." mode data connection for $args ($file_size bytes)");
								$FTP->store->call("ftp.$getmode.get.phtml", array("arRequestedTemplate" => $template),
											$FTP->store->get($path));
								debug("ftp::get::going to close dc");
								ftp_CloseDC();
								debug("ftp::get::dc closed");
								ftp_Tell(226, "Transfer complete");
							} else {
								ftp_CloseDC();
								ftp_Tell(550, "$file does not exist");
							}
						}
					break;

					case 'NLST':
					case 'LIST':
						$args=ereg_replace('-[^[:space:]]+[[:space:]]*', '', chop($args));
						$path=$args;
						ftp_TranslatePath($path, $listMode);
						debug("ftp: LIST path=$path, mode=$listMode");
						if ($FTP->store->exists($path)) {

							ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ASCII' : 'BINARY')." mode data connection");
							if (ftp_OpenDC()!==false) {
								unset($mode);
								debug("ftp: listing ($path) ($listMode)");

									if ($listMode!=="files") {
										$mode["filename"]="#files#";
										$mode["date"]=time();
										if ($FTP->cwd!=="/") {
											$mode["type"]="shortcut";
											$mode["target"]=$FTP->cwd;
											if ($FTP->defaultListMode!="files") {
												$mode["target"]="/#files#".$mode["target"];
											}
										} else {
											$mode["type"]="dir";
										}
										$mode["size"]=0;
										$mode["grants"]["read"]=true;

										if ($cmd!=="NLST") {
											$data=ftp_GenListEntry($mode);
											echo "$data";
										} else {
											echo $mode["filename"]."\n";
										}
									}

									if ($listMode!=="templates") {
										$mode["filename"]="#templates#";
										$mode["date"]=time();
										if ($FTP->cwd!=="/") {
											$mode["type"]="shortcut";
											$mode["target"]=$FTP->cwd;
											if ($FTP->defaultListMode!="templates") {
												$mode["target"]="/#templates#".$mode["target"];
											}
										} else {
											$mode["type"]="dir";
										}
										$mode["size"]=0;
										$mode["grants"]["read"]=true;
										if ($cmd!=="NLST") {
											$data=ftp_GenListEntry($mode);
											echo "$data";
										} else {
											echo $mode["filename"]."\n";
										}
									}

									if ($listMode!=="objects") {
										$mode["filename"]="#objects#";
										$mode["date"]=time();
										$mode["size"]=0;
										$mode["grants"]["read"]=true;
										if ($FTP->cwd!=="/") {
											$mode["type"]="shortcut";
											$mode["target"]=$FTP->cwd;
											if ($FTP->defaultListMode!="objects") {
												$mode["target"]="/#objects#".$mode["target"];
											}
										} else {
											$mode["type"]="dir";
										}
										if ($cmd!=="NLST") {
											$data=ftp_GenListEntry($mode);
											echo "$data";
										} else {
											echo $mode["filename"]."\n";
										}
									}

								$template="ftp.".$listMode.".list.phtml";
								$result=current($FTP->store->call($template, "",
													$FTP->store->get($path)));

								debug("ftp: results(".sizeof($result).")");
								@reset($result);
								while (list($key, $entry)=@each($result)) {
									debug("ftp: file path = (".$entry["path"].")");
									if ($cmd!=="NLST") {
										$data=ftp_GenListEntry($entry);
										echo "$data";
									} else {
										$parent = $FTP->store->make_path($entry["path"], "..");
										$filename = substr($entry["path"], strlen($parent), -1);
										debug("ftp::nlst	".$filename);
										echo $filename."\n";
									}
								}

								ftp_CloseDC();
								ftp_Tell(226, "Transfer complete");
							} else {
								ftp_Tell(550, "Could not connect to client");
								debug("ftp: could not connect");
							}
						} else {
							ftp_TranslateTemplate($path, $template);
							debug("ftp::list maybe it's a template? ($path, $template)");
							$result = current($FTP->store->call("ftp.template.exists.phtml", 
												Array("arRequestedTemplate" => $template),
												$FTP->store->get($path)));

							if (is_array($result)) {
								ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ASCII' : 'BINARY')." mode data connection");
								if (ftp_OpenDC()!==false) {
									echo ftp_GenListEntry($result);								
									ftp_CloseDC();
									ftp_Tell(226, "Transfer complete");
								} else {
									ftp_Tell(550, "Could not connect to client");
									debug("ftp: could not connect");
								}
							} else {
								ftp_Tell(550, "Directory not found");
							}
						}
					break;

					case 'RMD':
					case 'RMDIR':
					case 'DELE':
						$target = $args;
						ftp_TranslatePath($target, $listMode);

						debug("ftp: removing $target");
						if ($listMode==="templates") {
							$path = $FTP->store->make_path($target, "..");
							$template = substr($target, strlen($path), -1);
							debug("ftp: removing template ($path) ($template)");
							$FTP->store->call("ftp.templates.delete.phtml", Array("template" => $template),
												$FTP->store->get($path));

							ftp_Tell(250, "$template removed");
						} else
						if ($FTP->store->exists($target)) {
								debug("ftp::delete ($target) ftp.$listMode.delete.phtml");
								$FTP->store->call("ftp.$listMode.delete.phtml", "",
									$FTP->store->get($target));

								if ($ARCurrent->ftp_error) {
									ftp_Tell(550, $ARCurrent->ftp_error);
									unset($ARCurrent->ftp_error);
								} else {
									ftp_Tell(250, "$target removed");
								}
						} else {
							ftp_Tell(550, "$target does not exist");
						}
					break;

					case 'STOR':
						$target = $args;
						ftp_TranslatePath($target, $listMode);
						$path = $FTP->store->make_path($target, "..");

						ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ASCII' : 'BINARY')." mode data connection");
						debug("ftp: client wants to store file ($target)");
						eregi('^/(.*/)?[^./]*[.]([^./]+)/$', $target, $regs);
						$ext = $regs[2];
						if (ftp_OpenDC()) {
							$tempfile=tempnam($FTP->store->files."temp/", "upload");
							debug("tempfile: '$tempfile' ext: '$ext'");
							$tempfile.=$ext;
							$fp=fopen($tempfile, "wb");
							if ($fp) {
								$fileinfo["tmp_name"]=$tempfile;
								$fileinfo["type"]=get_mime_type($tempfile);
								if ($listMode === "templates") {
									ftp_TranslateTemplate($target, $template);
									$fileinfo["name"]=eregi_replace('[^.a-z0-9_-]', '_', $template);

									debug("ftp: writing to $tempfile\n");
									if ($FTP->resume) {
										debug("ftp::store resuming file at $FTP->resume");
										ob_start();
											$FTP->store->call("ftp.$listMode.get.phtml", Array("arRequestedTemplate" => $template),
												$FTP->store->get($target));
											$data=ob_get_contents();
											fwrite($fp, substr($data, 0, $FTP->resume));
										ob_end_clean();
									}
									while (($data=ftp_ReadDC())) {
										fwrite($fp, $data);
									}
									fclose($fp);
									ftp_CloseDC();
									$fileinfo["size"]=filesize($tempfile);

									debug("ftp: writing template to  ($target$template)");
									$FTP->store->call("ftp.templates.save.phtml", Array("file" => $fileinfo),
										$FTP->store->get($target));
								} else {
									$file=substr($target, strlen($path), -1);
									$fileinfo["name"]=eregi_replace('[^.a-z0-9_-]', '_', $file);
									if ($FTP->store->exists($target)) {
										debug("ftp::store updating $target");
										debug("ftp: writing to $tempfile\n");
										if ($FTP->resume) {
											debug("ftp::store resuming file at $FTP->resume");
											ob_start();
												$FTP->store->call("ftp.$listMode.get.phtml", "",
													$FTP->store->get($target));
												$data=ob_get_contents();
												debug("ftp::store resume pre-read ".strlen($data));
												fwrite($fp, substr($data, 0, $FTP->resume));
											ob_end_clean();
										}
										while (($data=ftp_ReadDC())) {
											fwrite($fp, $data);
										}
										fclose($fp);
										ftp_CloseDC();
										$fileinfo["size"]=filesize($tempfile);
										debug("ftp::store total size of fileupload is: ".$fileinfo["size"]);
										// if $target already exists
										$FTP->store->call("ftp.$listMode.save.phtml", Array("file" => $fileinfo),
											$FTP->store->get($target));
									} else {
										debug("ftp::store storing $target");
										debug("ftp: writing to $tempfile\n");
										while (($data=ftp_ReadDC())) {
											fwrite($fp, $data);
										}
										fclose($fp);
										ftp_CloseDC();
										$fileinfo["size"]=filesize($tempfile);

										$FTP->store->call("ftp.$listMode.save.new.phtml", Array("file" => $fileinfo),
											$FTP->store->get($path));
									}
								}
								if (file_exists($tempfile)) {
									@unlink($tempfile);
								}

							} else {
								debug("ftp: could not write to $filename\n");
							}

							if ($ARCurrent->ftp_error) {
								ftp_Tell(550, $ARCurrent->ftp_error);
								unset($ARCurrent->ftp_error);
							} else {
								ftp_Tell(226, "Transfer complete (".$fileinfo["name"].")");
							} 
						} else {
							debug("ftp: error connecting to client");
							ftp_Tell(550, "Could not establish a connection");
						}
					break;

					case 'MKD':
						$path_requested = $args;
						$path=ereg_replace('/#[^/]*#/', "/", $args);
						eregi('^(.*[/])?(.*)$', $path, $regs);
						$arNewFilename=eregi_replace('[^.a-z0-9_-]', '_', $regs[2]);

						$path=$FTP->site.$FTP->store->make_path($FTP->cwd, $path);
						$parent=$FTP->store->make_path($path, "..");

						debug("ftp: mkdir: name = '$arNewFilename' path = '$path' parent = '$parent'");

						if ($FTP->store->exists($parent)) {
							if (!$FTP->store->exists($path)) {
								$result=$FTP->store->call("ftp.mkdir.phtml", Array("arNewFilename" => $arNewFilename),
									$FTP->store->get($parent));
							} else {
								$ARCurrent->ftp_error="Directory already exists";
							}
						} else {
							$ARCurrent->ftp_error="Could not find path $parent";
						}

						if ($ARCurrent->ftp_error) {
							ftp_Tell(550, $ARCurrent->ftp_error);
							unset($ARCurrent->ftp_error);
						} else {
							ftp_Tell(257, "\"$path_requested\" - Directory successfully created.");
						} 
					break;

					case 'SYST':
						ftp_Tell(215, "UNIX Type: L8");
					break;

					case 'NOOP':
						ftp_Tell(200, "NOOP command successful");
					break;					

					case 'USER':
					case 'PASS':
						ftp_Tell(530, "User '$this->user' already logged in");
					break;

					default:
						ftp_Tell(500, "Function $cmd not implemented (yet).");
					break;
				}
				$last_cmd = $cmd;
			}
		}

		function ftp_CheckLogin() {
		global $FTP, $AR, $ARConfig, $ARLogin, $ARPassword;

			while (!$AR->user) {
				ftp_FetchCMD($cmd, $args);
				if ($cmd==="USER") {
					$login=$args;
					ftp_Tell(331, "Password required for '$login'");
					ftp_FetchCMD($cmd, $args);
					if ($cmd=="PASS") {
						$password=$args;
						debug("ftp: auth ($login, $password)");
						
						$criteria="object.implements = 'puser'";
						$criteria.=" and login.value = '".AddSlashes($login)."'";
						$user=$FTP->store->call("system.get.phtml", "",
												$FTP->store->find("/system/users/", 
																$criteria));
						$user=$user[0];

						if ($user) {
							debug("ftp: found user");
							$ARLogin=$login;
							$ARPassword=$password;

							if ($user->CheckPassword($password)) {
								$AR->user=$user;
								if ($user->data->login!="admin") {
									$AR->user->grants[$AR->user->path]=$AR->user->GetValidGrants();
								}

								$siteroot = current($FTP->store->call("system.get.phtml", "", $FTP->store->get($FTP->site."/")));

								if ($AR->user->data->login==="admin" || $siteroot->CheckLogin("ftp")) {
									$FTP->cwd="/";
									$this->user=$login;
								} else {
									ftp_Tell(530, "Login incorrect: (site) permission denied");
									unset($user);
									unset($AR->user);
								}

							} else {
								ftp_Tell(530, "Login incorrect: password incorrect");
							}
						} else {
							ftp_Tell(530, "Login incorrect: user '$login' not found ");
						}
					} else {
						ftp_Tell(530, "Please login with USER and PASS.");
					}
				} else {
					ftp_Tell(530, "Please login with USER and PASS.");
				}
			}
			ftp_Tell(230, "User '".$this->user."' logged in at $FTP->site$FTP->cwd ");
		}

		function ftp_FetchCMD(&$cmd, &$args) {
		global $FTP;
			do {
				$data=fgets($FTP->stdin, 2000);
				debug("ftp: client:: '$data'");
				if (eregi('^([a-z]+)([[:space:]]+(.*))?', $data, $regs)) {
					$cmd=strtoupper($regs[1]);
					$args=chop($regs[3]);
					debug("ftp: cmd ($cmd) arg ($args)");
				} else {
					$cmd="";
					exit;
				}
			} while (!$cmd);
			return $cmd;
		}

		function ftp_Tell($code, $msg) {
		global $FTP;
			if (is_array($msg)) {
				fputs($FTP->stdout, "$code-".$msg[0]."\n");
				next($msg);
				while (list(,$line)=each($msg)) {
					fputs($FTP->stdout, $line."\n");
					debug($line);
				}
			} else {
				fputs($FTP->stdout, "$code $msg\n");
				debug("$code $msg\n");
			}
			fflush($FTP->stdout);
		}

		function ftp_GenListEntry($entry) {
		global $AR;

			$user=$AR->user->data->login;
			$grants=$entry["grants"];

			if ($entry["filename"]) {
				$file=$entry["filename"];
			} else {
				$file=substr($entry["path"], strrpos(substr($entry["path"], 0, -1), "/")+1);
			}

			if ($entry["type"]==="dir") {
				$data.="d";
				if ($file[strlen($file)-1]==='/') {
					$file=substr($file, 0, -1);
				}
			} else if ($entry["type"]==="shortcut") {
				$data.="l";
				if ($file[strlen($file)-1]==='/') {
					$file=substr($file, 0, -1);
				}
				$file=$file." -> ";
				$file.=$entry["target"];
			} else if ($entry["type"]==="template") {
				$data.="-";
			} else {
				$data.="-";
				$file=substr($file, 0, -1);
			}

			if ($grants["read"]) {
				$data.="r";
			} else {
				$data.="-";
			}
			if ($grants["write"]) {
				$data.="w";
			} else {
				$data.="-";
			}
			if (($entry["type"]==="dir" || $entry["type"]==="shortcut") && $grants["read"]) {
				$data.="x";
			} else {
				$data.="-";
			}

			// 'group' grants are identical to user grants
			// and we don't give public any grants
			$data.=substr($data, 1);
			$data.="---";

			$data.="   1 "; // we just say this directory contains 1 child

			$user = substr($user, 0, 9);
			$userentry = $user.substr("         ", strlen($user));
			$data.=$userentry.$userentry;			

			$size = substr($entry["size"], 0, 8);
			$sizeentry = substr("        ", strlen($size)).$size;
			$data.=$sizeentry;

			$date=substr(date("M d h:i", $entry["date"]), 0, 12);
			$dateentry = substr("            ", strlen($date)).$date;
			$data.=" ".$dateentry;

			$data.=" ".$file;

			$data.="\n";			
			debug($data);
			return $data;
		}


	sleep(1);
//	debugon("pinp");

	// set PHP error handling
	error_reporting(1);
	set_error_handler("ftp_ErrorHandler");
	error_reporting(1);
	set_time_limit(0);

	$FTP = new object;
	$inst_store = $store_config["dbms"]."store";
	$store=new $inst_store("", $store_config);

	// fill in your own server ip number:
	$FTP->server_ip = $ftp_config["server_ip"];
	$FTP->host = $ftp_config["site"];
	$FTP->store = &$store;
	// default listMode ( files, objects or templates )
	$FTP->defaultListMode = "files";

	// default type is ASCII
	$FTP->DC["type"] = "A";

	ob_start("debug");
	$FTP->stdin=fopen("php://stdin", "r");
	if ($FTP->stdin) {
		$FTP->stdout=fopen("php://stdout", "w");
		if ($FTP->stdout) {

			$FTP->site=substr($ftp_config["root"],0, -1);
			$FTP->cwd="/";
			ftp_Tell(220, $ftp_config["greeting"]);

			ftp_CheckLogin();
			ftp_Run();

		} else {
			fclose($FTP->stdin);
			$FTP->error="Could not open stdout";
		}
	} else {
		$FTP->error="Could not open stdin";
	}
?>

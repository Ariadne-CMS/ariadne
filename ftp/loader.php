<?php
	include("../www/ariadne.inc");
	require($ariadne."/configs/ariadne.phtml");
	require($ariadne."/includes/loader.ftp.php");
	require($ariadne."/configs/store.phtml");
	require($ariadne."/configs/sessions.phtml");
	require($ariadne."/stores/mysqlstore.phtml");
	require($ariadne."/nls/en");
	require($ariadne."/modules/mod_mimemagic.php");

		/* this function has been taken from the php manual		*/
		
		function ftp_ErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) {
			if ($errno!=8 ) {
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
		global $FTP;

			$FTP->DC["transfered"]=0;
			if ($FTP->DC["mode"]==="active") {
				$socket=socket(AF_INET, SOCK_STREAM, 0);
				if ($socket>=0) {
					debug("ftp: opened socket");
					$result=connect($socket, $FTP->DC["address"], $FTP->DC["port"]);
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
				$msgsocket=accept_connect($FTP->DC["socket"]);
				if ($msgsocket < 0) {
					ftp_Tell(425, "Couldn't build data connection (rm: socket error: ".strerror($socket).")");
					$result=false;
				} else {
					debug("ftp: accept_connect returned $msgsocket");
					//socket_set_blocking($msgsocket, TRUE);
					debug("ftp: connected ($msgsocket)");
					$FTP->DC["msgsocket"]=$msgsocket;
					$result=true;
				}
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
			$socket=socket(AF_INET, SOCK_STREAM, 0);
			if ($socket>=0) {
				debug("ftp: open socket (pasv mode)");

				// FIXME: make this configurable!
				$notbound=1;
				$port=12000;
				while ($notbound && $port<=12100) {
					$notbound=bind($socket, $FTP->server_ip, $port);
					if ($notbound) {
						$port++;
					}
				}

				if (!$notbound) {
					$ret=listen($socket, 1);
					if ($ret < 0) {
						ftp_Tell(425, "Couldn't build data connection (rm: socket error:".strerror($socket).")");
					} else {
						$FTP->DC["mode"]="passive";
						$FTP->DC["socket"]=$socket;
						debug("ftp: listening on port $port");
						$result=str_replace(".", ",", $FTP->server_ip);
						$result.=",".(((int)$port) >> 8);
						$result.=",".($port & 0x00FF);
					}
				} else {
					ftp_Tell(425, "Couldn't build data connection:  couldn't bind to a socket");
				}

			} else {
				ftp_Tell(425, "Couldn't build data connection (rm: socket error:".strerror($socket).")");
				$result=false;
			}
			return $result;
		}


		function ftp_WriteDC($data) {
		global $FTP;

			if (strlen($data)) {
				if ($FTP->DC["type"]==="A") {
					$offset = 0;
					$chunk = substr($data, $offset, 4096);
					while ($chunk!==false) {
						$len = strlen($chunk);
						debug("ftp_WriteDC:: writing chunk($offset, 4096) (".$len.")");
						$chunk=str_replace("\n", "\r\n", $chunk);
						if (!write($FTP->DC["msgsocket"], $chunk, $len)) {
							debug("ftp_WriteDC:: chunk ERROR write $len bytes!");
							$chunk = false;
						} else {
							debug("ftp_WriteDC:: chunk success");
							$offset+=strlen($chunk);
							$FTP->DC["transfered"]+=strlen($data);
							$chunk = substr($data, $offset, 4096);
						}
					}
					
				} else {
					$len=strlen($data);
					debug("ftp_WriteDC:: writing len (".$len.")");
					if (!write($FTP->DC["msgsocket"], $data, $len)) {
						debug("ftp_WriteDC:: ERROR write $len bytes!");
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
			$data="";
			read($FTP->DC["msgsocket"], $data, 3000, PHP_BINARY_READ);
			if (strlen($data) && ($FTP->DC["type"]==="A")) {
				if ($data[strlen($data)-1]==="\r") {
					read($FTP->DC["msgsocket"], $postdata, 1, PHP_BINARY_READ);
					$data.=$postdata;
				}
				$data=str_replace("\r\n", "\n", $data);
			}
			$FTP->DC["transfered"]+=strlen($data);
			return $data;
		}

		function ftp_CloseDC() {
		global $FTP;
			if ($FTP->DC["ob_active"]) {
				debug("ftp::CloseDC:: closing output buffer");
				ob_end_clean();
				debug("ftp::CLoseDC:: ok, ob closed");
				$FTP->DC["ob_active"]=false;
			}

			debug("ftp: closing connection");
			$con=$FTP->DC["msgsocket"];
			if ($con) {
				debug("ftp::CloseDC:: closing connection");
				close($con);
				debug("ftp::CloseDC:: connection closed");
			}
		}

		function ftp_TranslatePath(&$path, &$listMode) {
		global $FTP;
			$listMode="";
			$path=$FTP->site.$FTP->store->make_path($FTP->cwd, $path);
			while (ereg('/-([^/]*)-/', $path, $regs) && $regs[1]) {
				$listMode=$regs[1];
				$path=str_replace("/-".$listMode."-/", "/", $path);
			}
			if (!$listMode) {
				$listMode=$FTP->listMode;
			}
			//debug("ftp: Translate $debug_path:: (FTP->listMode = '$FTP->listMode', listMode = '$listMode')");
		}

		function ftp_Run() {
		global $FTP, $ARCurrent, $ARBeenHere;

			while (ftp_FetchCMD($cmd, $args)) {
				$ARBeenHere=Array();		
				switch ($cmd) {
					case 'QUIT':
						ftp_Tell(221, "Goodbye.");
						return 0;
					break;
					case 'PWD':
						$dir="/-".$FTP->listMode."-".$FTP->cwd;
						ftp_Tell(257, "\"$dir\" is current directory.");
					break;
					case 'HELP':
						ftp_Tell(214, "not implemented" );
					break;
					case 'PORT':
						$FTP->DC["mode"]="active";
						$host=explode(",",$args[0]);
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
						$path=$FTP->store->make_path($FTP->site.$FTP->cwd, $args[0]);
						while (ereg('/-([^/]*)-/', $path, $regs) && $regs[1]) {
							$FTP->listMode=$regs[1];
							$path=str_replace("/-".$FTP->listMode."-/", "/", $path);
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
						if (eregi('a|i', $args[0])) {
							$FTP->DC["type"]=strtoupper($args[0]);
							ftp_Tell(200, "Type set to ".$args[0]);
						} else {
							ftp_Tell(500, "Type $args[0] not valid");
						}
					break;

					case 'RETR':
						$path=$args[0];
						ftp_TranslatePath($path, $listMode);
						switch ($listMode) {
							case "templates":
								$reqpath = $path;
								$path = $FTP->store->make_path($path, "..");
								$template = substr($reqpath, strlen($path), -1);
								$getmode = "templates";
							break;
							default:
								$getmode = "files";
							break;
						}

						debug("ftp: opening $path / template $template");

						if (ftp_OpenDC()!==false) {
							if ($FTP->store->exists($path)) {

								ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ascii' : 'binary')." mode data connection for $args[0] (".strlen($file_data)." bytes)");
								$FTP->store->call("ftp.$getmode.get.phtml", array("arRequestedTemplate" => $template),
											$FTP->store->get($path));
								//$file_data=ob_get_contents();
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
						$args[0]=ereg_replace('-[^[:space:]]+[[:space:]]*', '', chop($args[0]));
						$path=$args[0];
						ftp_TranslatePath($path, $listMode);

						debug("ftp: LIST path=$path, mode=$listMode");
						if ($FTP->store->exists($path)) {
							ftp_Tell(150, "Opening ascii mode data connection");
							if (ftp_OpenDC()!==false) {

								debug("ftp: listing ($path) ($listMode)");

								if ($listMode!=="files") {
									$mode["filename"]="-files-";
									$mode["date"]=time();
									$mode["type"]="shortcut";
									$mode["target"]="/-files-$FTP->cwd";
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
									$mode["filename"]="-templates-";
									$mode["date"]=time();
									$mode["type"]="shortcut";
									$mode["target"]="/-templates-$FTP->cwd";
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
									$mode["filename"]="-objects-";
									$mode["date"]=time();
									$mode["type"]="shortcut";
									$mode["target"]="/-objects-$FTP->cwd";
									$mode["size"]=0;
									$mode["grants"]["read"]=true;
									$data=ftp_GenListEntry($mode);
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
							ftp_Tell(550, "Directory not found");
						}
					break;

					case 'DELE':
						$target = $args[0];
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
								$FTP->store->call("ftp.delete.phtml", "",
									$FTP->store->get($file));

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
						$target = $args[0];
						ftp_TranslatePath($target, $listMode);
						$path = $FTP->store->make_path($target, "..");

						ftp_Tell(150, "Opening ".(($FTP->DC["type"]==="A") ? 'ascii' : 'binary')." mode data connection");
						debug("ftp: client wants to store file ($target)");
						if (ftp_OpenDC()) {
							$tempfile=tempnam($FTP->store->files."temp/", "upload");
							$fp=fopen($tempfile, "wb");
							if ($fp) {
								debug("ftp: writing to $tempfile\n");
								while (($data=ftp_ReadDC())) {
									fwrite($fp, $data);
								}
								fclose($fp);
								ftp_CloseDC();
								$fileinfo["tmp_name"]=$tempfile;
								$fileinfo["type"]=get_mime_type($tempfile);
								$fileinfo["size"]=filesize($tempfile);

								$file=substr($target, strlen($path), -1);
								$fileinfo["name"]=eregi_replace('[^.a-z0-9_-]', '_', $file);
								debug("ftp: fileinfo: name = '".$fileinfo["name"]."'");

								if ($listMode === "templates") {
										debug("ftp: writing template to  ($path)");
										$FTP->store->call("ftp.$listMode.save.phtml", Array("file" => $fileinfo),
											$FTP->store->get($path));
								} else {
									if ($FTP->store->exists($target)) {
										$result=$FTP->store->call("ftp.$listMode.save.phtml", Array("file" => $fileinfo),
											$FTP->store->get($target));
									} else {
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
						$path=ereg_replace('/-[^/]*-/', "/", $args[0]);
						eregi('^([/][^/]+[/])?(.*)$', $path, $regs);
						$arNewFilename=$regs[2];

						$path=$FTP->store->make_path($FTP->site.$FTP->cwd, $path);
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
							ftp_Tell(226, "Directory created (".$arNewFilename.")");
						} 
					break;

					case 'SYST':
						ftp_Tell(215, "Test php-ftpd");
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
			}
		}

		function ftp_CheckLogin() {
		global $FTP, $AR, $ARConfig, $ARLogin, $ARPassword;

			while (!$AR->user) {
				ftp_FetchCMD($cmd, $args);
				if ($cmd==="USER") {
					$login=$args[0];
					ftp_Tell(331, "Password required for '$login'");
					ftp_FetchCMD($cmd, $args);
					if ($cmd=="PASS") {
						$password=$args[0];
						debug("ftp: auth ($login, $password)");
						// do authentication
						// ftp_Tell(230, "User '$this->user' logged in.");
						
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

								if ($site) {
									if ($AR->user->data->login==="admin" || $site->CheckLogin("ftp")) {
										$FTP->site=substr($site->path,0,-1);
										$FTP->cwd="/";
										$this->user=$login;
									} else {
										ftp_Tell(530, "Login incorrect: permission denied");
										unset($user);
										unset($AR->user);
									}
								} else {
									if ($AR->user->data->login==="admin" || $AR->user->CheckLogin("ftp")) {
										$FTP->site="";
										$FTP->cwd=$user->path;
										$this->user=$login;
									} else {
										ftp_Tell(530, "Login incorrect: permission denied");
										unset($user);
										unset($AR->user);
									}
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
				if (eregi('^([a-z]+)([[:space:]]+([^[:space:]]+))?([[:space:]]+([^[:space:]]+))?[[:space:]]*', $data, $regs)) {
					$cmd=strtoupper($regs[1]);
					$args[0]=$regs[3];
					$args[1]=$regs[5];
					debug("ftp: cmd ($cmd) arg0 ($args[0]) arg1 ($args[1])");
				} else {
					$cmd="";
					exit;
				}
			} while (!$cmd);
			return $cmd;
		}

		function ftp_Tell($code, $msg) {
		global $FTP;
			debug($code."=".$msg);
			if (is_array($msg)) {
				fputs($FTP->stdout, "$code-".$msg[0]."\n");
				next($msg);
				while (list(,$line)=each($msg)) {
					fputs($FTP->stdout, $line."\n");
				}
			} else {
				fputs($FTP->stdout, "$code $msg\n");
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
				$file=substr($file, 0, -1);
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

			$data.="    1 "; // we just say this directory contains 1 child

			$user = substr($user, 0, 9);
			$userentry = substr("         ", strlen($user)).$user;
			$data.=$userentry.$userentry;			

			$size = substr($entry["size"], 0, 9);
			$sizeentry = substr("         ", strlen($size)).$size;
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
	debugon("pinp");

	// set PHP error handling
	error_reporting(1);
	set_error_handler("ftp_ErrorHandler");
	error_reporting(1);


	$FTP = new object;
	$store=new mysqlstore(".", $store_config);

	// fill in your own server ip number:
	$FTP->server_ip = "your.ip.number";

	$FTP->host = "muze.nl";
	$FTP->store = &$store;
	$FTP->listMode = "objects";

	$FTP->stdin=fopen("php://stdin", "r");
	if ($FTP->stdin) {
		$FTP->stdout=fopen("php://stdout", "w");
		if ($FTP->stdout) {

			// find our ftp site
			$criteria="object.implements = 'psite'";
			$criteria.=" and url.host = '".AddSlashes($FTP->host)."'";
			$result=$FTP->store->call("system.get.phtml", "",
									$FTP->store->find("/", $criteria));


			if (is_array($result)) {
				$site=current($result);
				$FTP->site=substr($site->path, 0, -1);
				$FTP->cwd="/";
				ftp_Tell(220, "Test php-ftp loader");
			} else {
				$FTP->site="";
				$FTP->cwd="/";
				ftp_Tell(220, "Test php-ftp loader");
			}

			ftp_CheckLogin();
			ftp_Run();

		} else {
			fclose($FTP->stdin);
			$FTP->error="Could not open stdout";
		}
	} else {
		$FTP->error="Could not open stdin";
	}

	debugoff();
?>
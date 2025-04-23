<?php

	/*
		Integration test framework

create = created [new][id] on [new][path]

delete + create = replaced on path, old id ( [old_stale][id] with [new][id] )

delete = verwijderd [old][id]

move = [old][path] -> [new][path]

update = diff


	*/

	require_once( AriadneBasePath . "/ar/beta/diff.php" );

	require_once( AriadneBasePath . "/includes/diff/DiffEngine.php" );
	require_once( AriadneBasePath . "/includes/diff/ariadne.diff.inc" );

	class mod_ITF {

		public $dbh = null;


		function __construct( $config = [] ) {
		global $AR;
			$this->config =
				[
					"db_file" =>
						$AR->dir->install . "/files/temp/itf.db"
					,
					"project" =>
						0
					,
				]
			;
			$this->config =
				array_merge( $this->config, $config )
			;

			$this->project = [];
		}

		function record() {
			$data =
				[
					"_ENV" => getenv(),
					"_GET" => $_GET,
					"_POST" => $_POST,
					"_SERVER" => $_SERVER,
					"_COOKIE" => $_COOKIE
				] 
			;
			$this->Init();
			$this->InitWS();

			$query_string = 
				"
					select * from project where id = :id
				"
			;
			$project = $this->DBQuery( $query_string, [ "id" => $this->config[ "project" ] ] )->fetchArray( SQLITE3_ASSOC );
			if ( !is_array( $project ) ) {
				return false;
			}
			if ( !$project[ "is_recording" ] ) {
				return false;
			}

			$query_string =
				"
					insert into record ( project, url, params ) values ( :project, :url, :params )
				"
			;

			$this->DBQuery( $query_string,
				[
					"project" =>
						$this->config[ "project" ]
					,
					"url" =>
						$data[ "_SERVER" ][ "REQUEST_URI" ]
					,
					"params" =>
						serialize( $data )
					,
				]
			);

			return true;
		}

		function Init() {
			if ( !$this->dbh ) {
				$this->dbh = new SQLite3( $this->config[ "db_file" ] );
				$this->dbh->busyTimeout( 4000 );				
			}
			$this->InitDB();
		}

		function InitWS() {
			$query_string = 
				"
					select * from project where is_recording = 1
				"
			;
			$project = $this->DBQuery( $query_string )->fetchArray( SQLITE3_ASSOC );
			if ( is_array( $project ) ) {
				$this->config[ "project" ] = $project[ "id" ];
				putenv( "ARIADNE_WORKSPACE=". $this->config[ "project" ] );
				putenv( "ARIADNE_WORKSPACE_PATHS=/" );			
			}
		}

		function InitDB() {
			$query_string =
				"
					create table if not exists record (
						id integer primary key autoincrement,
						project integer not null,
						timestamp unsigned integer not null default ( strftime( '%s', 'now' ) ),
						url text,
						params text
					)
				"
			;
			$this->DBQuery( $query_string );

			$query_string =
				"
					create index if not exists record_project on record ( project )
				"
			;
			$this->DBQuery( $query_string );

			$query_string =
				"
					create table if not exists commits (
						id integer primary key autoincrement,
						record integer not null,
						diff text,
						mute text
					)
				"
			;
			$this->DBQuery( $query_string );

			$query_string =
				"
					create index if not exists commits_record on commits ( record )
				"
			;
			$this->DBQuery( $query_string );

			$query_string =
				"
					create table if not exists project (
						id integer primary key autoincrement,
						name text,
						description text,
						is_recording int(1) default false
					)
				"
			;
			$this->DBQuery( $query_string );


			$query_string =
				"
					create index if not exists project_is_recording on project ( is_recording )
				"
			;
			$this->DBQuery( $query_string );

		}

		function getRecord( $id ) {
			$this->init();

			$query_string =
				"
					select * from record where id = :id
				"
			;
			$result =
				$this->DBQuery( $query_string, [ "id" => (int)$id ] )->fetchArray( SQLITE3_ASSOC )
			;
			return $result;
		}


		function DBQuery( $query_string, $params = [] ) {
			//echo ">> " . $query_string . "\n\n";
			$stmt =
				$this->dbh->prepare( $query_string )
			;
			if ( $stmt === false ) {
				$errorCode = $this->dbh->lastErrorCode();
				$errorMessage = $this->dbh->lastErrorMsg();
				$this->error = "ERROR: Sqlite: $errorCode: $errorMessage";
				error_log( "Sqlite::store_run_query: " . $this->error);// , "store" );
				error_log( "Sqlite::store_run_query: " . preg_replace( "|[\t \n]+|", "  ", $query_string ));//, "store" ); 
				error_log( "Sqlite::store_run_query: " . print_r( $params, true ) );
				return false;
			}

			foreach ( $params as $key => $value ) {
				$type = SQLITE3_TEXT;
				if ( is_int( $value ) ) {
					$type = SQLITE3_INTEGER;
				} else if ( is_float( $value ) ) {
					$type = SQLITE3_FLOAT;
				}
				$stmt->bindValue( ":" . $key, $value, $type );
			}
			$query = $stmt->execute();
			if ( $query === false ) {
				$errorCode = $this->dbh->lastErrorCode();
				$errorMessage = $this->dbh->lastErrorMsg();
				$this->error = "ERROR: Sqlite: $errorCode: $errorMessage";
				error_log( "Sqlite::store_run_query: " . $this->error);// , "store" );
				error_log( "Sqlite::store_run_query: " . preg_replace( "|[\t \n]+|", "  ", $query_string ));//, "store" ); 
				error_log( "Sqlite::store_run_query: " . print_r( $params, true ) );
				return false;
			}
			return $query;
		}

		function diff( $a, $b ) {
			$a = is_array($a) ? $a : explode("\n", $a);
			$b = is_array($b) ? $b : explode("\n", $b);

			$result = [];
			$diff = new Diff( $a, $b );
			foreach ( $diff->edits as $edit ) {
				if ( $edit->type === "change" ) {
					array_push( $result, [ "from" => $edit->orig, "to" => $edit->closing ] );
				}
			}
			return $result;
		}

		function getDiff( $id ) {
		global $store;
			$result = "";
			$query_string =
				"select * from project where id = :id"
			;
			$project = $this->DBQuery( $query_string, [ "id" => $id ] )->fetchArray( SQLITE3_ASSOC );
			if ( !is_array( $project ) ) {
				die( "No project with id '$id'\n" );
			}
			$store->setLayer( $project[ "id" ] );
			$list = $store->getLayerstatus( "/", true );
			foreach ( $list as $item ) {
				if ( in_array( "delete", $item[ "operation" ] ) &&  in_array( "create", $item[ "operation" ] ) ) {
					$result .= "+ replaced " . $item[ "old_stale" ][ "id" ] . " with " . $item[ "new" ][ "id" ] . " on " . $item[ "new" ][ "path" ] . "\n";
					$diff = $this->objectDiff( $item[ "new" ][ "data" ] ?? null, $item[ "old" ][ "data" ] ?? null, "data" );
					foreach ( $diff as $field => $chunks ) {
						$result .= "\t$field\n";
						foreach ( $chunks as $j => $data ) {
							foreach ( $data[ "to" ] as $line ) {
								$result .= "\t\t>  " . $line . "\n";
							}
						}
					}
				} else
				if ( in_array( "delete", $item[ "operation" ] ) ) {
					$result .= "+ deleted " . $item[ "old" ][ "path" ] . " with id " . $item[ "old" ][ "id" ] . "\n";
				} else
				if ( in_array( "create", $item[ "operation" ] ) ) {
					$result .= "+ created " . $item[ "new" ][ "path" ] . " with id " . $item[ "new" ][ "id" ] . "\n";
					$diff = $this->objectDiff( $item[ "new" ][ "data" ] ?? null, $item[ "old" ][ "data" ] ?? null, "data" );
					foreach ( $diff as $field => $chunks ) {
						$result .= "\t$field\n";
						foreach ( $chunks as $j => $data ) {
							foreach ( $data[ "to" ] as $line ) {
								$result .= "\t\t>  " . $line . "\n";
							}
						}
					}
				}
				if ( in_array( "update", $item[ "operation" ] ) ) {
					$result .= "+ edited " . $item[ "new" ][ "path"] . " with id " . $item[ "new" ][ "id" ] . ":\n";
					$diff = $this->objectDiff( $item[ "new" ][ "data" ] ?? null, $item[ "old" ][ "data" ] ?? null, "data" );
					foreach ( $diff as $field => $chunks ) {
						$result .= "\t$field\n";
						foreach ( $chunks as $j => $data ) {
							foreach ( $data[ "from" ] as $line ) {
								$result .= "\t\t<  " . $line . "\n";
							}
							foreach ( $data[ "to" ] as $line ) {
								$result .= "\t\t>  " . $line . "\n";
							}
						}
					}
				}
				if ( in_array( "move", $item[ "operation" ] ) ) {
					$result .= "+ moved " . $item[ "old" ][ "path" ] . " with id " . $item[ "old" ][ "id" ] . " to " . $item[ "new" ][ "path" ] . "\n";
				}
			}
			return $result;
		}

		function printDiff( $id ) {
			echo $this->getDiff( $id );
		}

		function commitDiff( $id ) {
		global $store;
			$diff = $this->getDiff( $id );
			if ( !$diff ) {
				die( "Nothing to commit.\n" );
			}
			$pathList = array_keys( $store->getLayerStatus( "/" ) );

			$query_string =
				"select max( id ) as record from record where project = :id"
			;
			$record = $this->DBQuery( $query_string, [ "id" => $id ] )->fetchArray( SQLITE3_ASSOC )[ "record" ];

			$query_string =
				"insert into commits ( record, diff ) values ( :record, :diff )"
			;
			$this->DBQuery( $query_string, [ "record" => (int)$record, "diff" => $diff ] );

			$store->commitLayer( "/", $pathList );
		}

		function loadDiff( $id ) {
			$result = null;
			$query_string = "select diff from commits where id = :id";
			$ediff = $this->DBQuery( $query_string, [ "id" => (int)$id ] )->fetchArray( SQLITE3_ASSOC )[ "diff" ];
			if ( $ediff ) {
				$result = $ediff;
			}
			return $result;
		}

		function objectDiff( $object1, $object2, $prefix="" ) {
			$result = [];
			foreach ($object1 as $key => $value) {
				if (is_string($value)) {
					if (is_array($object1)) {
						$diff = $this->diff( $object2[ $key ] ?? null, $value );
						if ($diff) {
							$result[ $prefix . "[" . $key . "]" ] = $diff;
							unset($diff);
						}
					} elseif (is_object($object1)) {
						$diff = $this->diff( $object2->{$key} ?? null, $value );
						if ($diff) {
							$result[ $prefix . "->" . $key ] = $diff;
							unset($diff);
						}
					}
				} elseif (is_array($value)) {
					if (is_array($object1)) {
						$result = array_merge( $result, $this->objectDiff($object1[$key] ?? null, $object2[$key] ?? null, $prefix . "[" . $key . "]" ) );
					} elseif (is_object($object1)) {
						$result = array_merge( $result, $this->objectDiff($object1->{$key} ?? null, $object2->{$key} ?? null, $prefix . "[" . $key . "]" ) );
					}
				} elseif (is_object($value)) {
					if (is_array($object1)) {
						$result = array_merge( $result, $this->objectDiff($value, $object2[$key] ?? null, $prefix . "->" . $key) );
					} elseif (is_object($object1)) {
						$result = array_merge( $result, $this->objectDiff($value, $object2->{$key} ?? null, $prefix . "->" . $key ) );
					}
				}
	 		}

			return $result;
		}

	}

?>
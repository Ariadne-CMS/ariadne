<?php
	function check_php_version() {
		if (version_compare(PHP_VERSION, '5.0.0', '>')) {
			return true;
		}
		return false;
	}

	function check_database_support() {
		if (check_mysql() || check_postgresql()) {
			return true;
		}
		return false;
	}

	function check_mysql() {
		if(function_exists('mysql_connect')) {
			return true;
		}
		return false;
	}

	function check_postgresql() {
		if (function_exists('pg_connect')) {
			return true;
		}
		return false;
	}

	function check_apache() {
		if (preg_match("/^Apache\/2/", $_SERVER['SERVER_SOFTWARE'])) {
			return true;
		}
		return false;
	}

	function check_webserver() {
		if (
			check_apache()
			// FIXME: Add more compatible webservers.
		) {
			return true;
		}
		return false;
	}

	function check_accept_path_info() {
		if (check_apache()) {
			$extrapath = "/test_path_info/";
			$object = apache_lookup_uri($_SERVER['REQUEST_URI'] . $extrapath);
			if ($object->path_info == $extrapath) {
				return true;
			}
			return false;
		} else {
			if ($_SERVER['PATH_INFO']) {
				// FIXME: Need a better check for this.
				return true;
			}
		}
		return false;
	}

	function check_zend_compat() {
		if (!ini_get("zend.ze1_compatibility_mode")) {
			return true;
		}
		return false;
	}

	function check_ariadne_inc_read() {
		if (is_readable("../ariadne.inc")) {
			return true;
		}
		return false;
	}

	function check_ariadne_path() {
		@include("../ariadne.inc");
		if (is_readable($ariadne . "/templates/pobject/")) {
			return true;
		}
		return false;
	}


	function check_files_write() {
		@include("../ariadne.inc");
		if (is_writable($ariadne . "/../files/")) {
			return true;
		}
		return false;
	}

	function check_ariadne_phtml_write() {
		@include("../ariadne.inc");
		if (file_exists($ariadne . "/configs/ariadne.phtml")) {
			if (is_writable($ariadne . "/configs/ariadne.phtml")) {
				return true;
			}
		} else {
			if (is_writable($ariadne . "/configs/")) {
				return true;
			}
		}
		return false;
	}

	function check_im_convert() {
		$bin = find_in_path('convert');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_convert'] = $bin;
			return true;
		}
		return false;
	}

	function check_im_mogrify() {
		$bin = find_in_path('mogrify');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_mogrify'] = $bin;
			return true;
		}
		return false;
	}

	function check_im_composite() {
		$bin = find_in_path('composite');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_composite'] = $bin;
			return true;
		}
		return false;
	}

	function check_im_identify() {
		$bin = find_in_path('identify');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_identify'] = $bin;
			return true;
		}
		return false;
	}

	function check_image_magick() {
		if (
			check_im_convert() &&
			check_im_mogrify() &&
			check_im_composite() &&
			check_im_identify()
		) {
			return true;
		}
		return false;
	}

	function check_svn() {
		if (
			check_svn_class() && 
			check_svn_binary()
		) {
			return true;
		}
		return false;
	}

	function check_svn_class() {
		@include_once("VersionControl/SVN.php");
		if (class_exists("VersionControl_SVN")) {
			return true;
		}
		return false;
	}

	function check_svn_binary() {
		$bin = find_in_path('svn');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_svn'] = $bin;
			return true;
		}
		return false;
	}
		
	function check_svn_write() {
		@include("../ariadne.inc");
		if (is_writeable($ariadne . "/configs/svn/")) {
			return true;
		}
		return false;
	}

	function check_html_tidy() {
		$bin = find_in_path('tidy');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_tidy'] = $bin;
			return true;
		}
		return false;
	}

	function check_grep() {
		$bin = find_in_path('grep');
		if (is_executable($bin)) {
			global $found_bins;
			$found_bins['bin_grep'] = $bin;
			return true;
		}
		return false;
	}

	function check_connect_db($conf) {
		switch ( $conf->dbms ) {
			case 'mysql':
				return check_connect_db_mysql($conf);
			break;
			case 'postgresql':
				return check_connect_db_postgresql($conf);
			break;
		}
		// FIXME: Add postgresql checks too
		return false;
	}

	function check_select_db($conf) {
		switch ( $conf->dbms ) {
			case 'mysql':
				return check_select_db_mysql($conf);
			break;
			case 'postgresql':
				return check_select_db_postgresql($conf);
			break;
		}
		return false;
	}

	function check_db_grants($conf) {
		switch ( $conf->dbms ) {
			case 'mysql':
				return check_db_grants_mysql($conf);
			break;
			case 'postgresql':
				return check_db_grants_postgresql($conf);
			break;
		}
		return false;
	}

	function check_db_grants_mysql($conf) {
		if (check_connect_db_mysql($conf) && check_select_db_mysql($conf)) {
			$query = "SHOW GRANTS FOR CURRENT_USER();";
			$grantchecks = array(
				"SELECT" => false,
				"INSERT" => false,
				"UPDATE" => false,
				"DELETE" => false,
				"CREATE" => false
			);
			
			$result = mysql_query($query);
			while ($row = mysql_fetch_row($result)) {
				if (preg_match("/^GRANT ALL/", $row[0])) {
					return true;
				}
				if (
					preg_match("/^GRANT.*?SELECT.*?ON/", $row[0]) &&
					preg_match("/^GRANT.*?INSERT.*?ON/", $row[0]) &&
					preg_match("/^GRANT.*?UPDATE.*?ON/", $row[0]) &&
					preg_match("/^GRANT.*?CREATE.*?ON/", $row[0]) &&
					preg_match("/^GRANT.*?DELETE.*?ON/", $row[0])
				) {
					return true;
				}
			}
		}
		return false;
	}

	function check_db_grants_postgresql($conf) {
		if ( check_select_db_postgresql($conf) ) {
			$query = "SELECT has_database_privilege ( ".$conf->database.", 'CREATE' );"; 
			$result = pg_query( $query );
			while ( $row = pg_fetch_row( $result ) ) {
				if ( $row[0]=='True' ) {
					return true;
				}
			}
		}
		return false;
	}
	
	function check_connect_db_mysql($conf) {
		if(@mysql_pconnect($conf->host, $conf->user, $conf->password)) {
			return true;
		}
		return false;
	}

	function check_connect_db_postgresql($conf) {
		if (strpos(':', $conf->host)) {
			$host = explode($conf->host, ':')[0];
			$port = explode($conf->host, ':')[1];
			$host .= ' port='.$port;
		} else {
			$host = $conf->host;
		}
		$conf->connection = @pg_connect('host='.$host.' dbname='.$conf->database.' user='.$conf->user.' password='.$conf->password);
		return (bool) $conf->connection;
	}

	function check_select_db_mysql($conf) {
		if (check_connect_db_mysql($conf)) {
			if (mysql_select_db($conf->database)) {
				return true;
			}
		}
		return false;
	}

	function check_select_db_postgresql($conf) {
		return check_connect_db_postgresql($conf); //connect also checks database
	}


	function check_db_is_empty($conf) {
		switch( $conf->dbms ) {
			case 'mysql':
				return check_db_is_empty_mysql($conf);
			break;
			case 'postgresql':
				return check_db_is_empty_postgresql($conf);			
			break;
		}
		return false;
	}

	function check_db_is_empty_mysql($conf) {
		if (check_connect_db_mysql($conf)) {
			if (check_select_db_mysql($conf)) {
				$query = "SHOW TABLES;";
				$result = mysql_query($query);
				if (mysql_num_rows($result) == 0) {
					return true;
				}
			}
		}
		return false;
	}

	function check_db_is_empty_postgresql($conf) {
		if (check_connect_db_postgresql($conf)) {
			$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';";
			$result = pg_query($conf->connection, $query);
			if (pg_num_rows($result) == 0) {
				return true;
			}
		}
		return false;
	}

	function check_file( $file ) {
		if (file_exists($file) && is_readable($file)) {
			return true;
		}
		return false;
	}

	function check_base_ax() {
		if (check_file("packages/base.ax")) {
			return true;
		}
		return false;
	}

	function check_demo_ax() {
		if (check_file("packages/demo.ax")) {
			return true;
		}
		return false;
	}

	function check_libs_ax() {
		if (check_file("packages/libs.ax")) {
			return true;
		}
		return false;
	}

	function check_docs_ax() {
		if (check_file("packages/docs.ax")) {
			return true;
		}
		return false;
	}

	function check_admin_password($admin_passwords) {
		if ($admin_passwords[0] && $admin_passwords[1] && $admin_passwords[0] == $admin_passwords[1]) {
			return true;
		}
		return false;
	}

	function check_tar_class() {
		@include_once("Archive/Tar.php");
		if (class_exists("Archive_Tar")) {
			return true;
		}
		return false;
	}

	function check_exif() {
		if (function_exists('exif_read_data')) {
			return true;
		}
		return false;
	}


	function find_in_path($needle,array $extrapath=array()) {
		$paths = explode(PATH_SEPARATOR,$_SERVER['PATH']);
		$paths = array_merge($paths,$extrapath);
				
		$exts = explode(PATH_SEPARATOR,$_SERVER['PATHEXT']);

		foreach($paths as $path){
			$file = $path . DIRECTORY_SEPARATOR . $needle;
			if(file_exists($file)) {
				return $file;
			}
			
			// W32 needs this
			foreach ($exts as $ext) {
				if(file_exists($file.$ext)) {
					return $file.$ext;
				}
			}
		}
	}

	$found_bins = array(); // will be filled by the check functions

	$required_checks = array(
		"check_php_version" => check_php_version(),		// php => 5.0.0
		"check_database_support" => check_database_support(),	// MySQL or Postgres
		"check_webserver" => check_webserver(),			// Apache, IIS, NGINX?
		"check_accept_path_info" => check_accept_path_info(),	// Apache config: AcceptPathInfo 
		"check_zend_compat" => check_zend_compat(),		// zend.ze1_compatibility_mode = Off
		"check_ariadne_inc_read" => check_ariadne_inc_read(),	// Check if configuration file (ariadne.inc) can be read bij www-data
		"check_ariadne_path" => check_ariadne_path(),		// Check if path in ariadne.inc looks like an Ariadne tree
		"check_files_write" => check_files_write(),		// Check if files dir can be written by www-data
		"check_base_ax"	=> check_base_ax(),
		"check_tar_class" => check_tar_class(),			// Check if Archive/Tar class is available to import packages with.
	);

	$recommended_checks = array(
		"check_ariadne_phtml_write" => check_ariadne_phtml_write(),	// Check if configuration file (ariadne.phtml) can be written bij www-data
		"check_exif" => check_exif(),
		"check_image_magick" => check_image_magick(),
		"check_svn" => check_svn(),
		"check_svn_write" => check_svn_write(),
		"check_html_tidy" => check_html_tidy(),
		"check_grep" => check_grep(),
		"check_demo_ax" => check_demo_ax(),
//		"check_libs_ax" => check_libs_ax(),
//		"check_docs_ax" => check_docs_ax()
	);

?>

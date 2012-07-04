<?php
/**
 * @file
 * @brief database configuration file
 * @details
 *  This file is read for database information on every run
 *  of the web application.
 *  Before configuration, this script also contains
 *  a One-time installation function that will execute.
 *  The intent is for this to be used in a "require" statement
 *  in a calling php script.
 */

//These are basically just defaults until the configured flag is set
/// This is the global database configuration variable
$GLOBALS['database_config'] = array(			///< Global database configuration variable
	//'configured' => true,				///< Flag indicating if it has been installed
	'type' => 'sqlite',				///< Type (string): xml, sqlite, or mysql
	'config_options' => array(			///< Database configuration options (array)
		'xml_filename' => 'gpsdata.xml',	///< [For xml type only] Filename (string)
		'sqlite_filename' => 'gpsdata.db',	///< [For sqlite type only] Filename (string)
		'mysql_host' => 'db.host.com',		///< [For mysql type only] Database host (string)
		'mysql_name' => 'gpsdata',		///< [For mysql type only] Database name (string)
		'mysql_user' => 'gpswa_username',	///< [For mysql type only] Username (string)
		'mysql_pass' => 'gpswa_password',	///< [For mysql type only] Password (string)
	),
);

// ******** DO NOT MODIFY ANYTHING BELOW THIS LINE  ********
// ******** CONTENT BELOW INDICATES THAT AN         ********
// ******** INSTALLATION HAS NOT YET TAKEN PLACE    ********

/**
 * @ingroup wa
 * @brief This is the install function
 */
function install_db() {

	// If we are sending a configuration
	$install_message = '';
	$install_good = false;
	if (isset($_POST['configure']) && $_POST['configure'] == '1') {
		// Assume the rest of the post vars are set if configure is

		$config = '<?php' . "\n";
		$config .= '$GLOBALS[\'database_config\'] = array(' . "\n";
		$config .= ' \'configured\' => true,' . "\n";

		$GLOBALS['database_config']['type'] = $_POST['type'];
		$config .= ' \'type\' => \'' . $_POST['type'] . '\',' . "\n";

		$config .= ' \'config_options\' => array(' . "\n";

		///< @todo these are all the same with different names so can be streamlined
		$GLOBALS['database_config']['config_options']['xml_filename'] = $_POST['xml_filename'];
		$config .= '  \'xml_filename\' => \'' . $_POST['xml_filename'] . '\',' . "\n";

		$GLOBALS['database_config']['config_options']['sqlite_filename'] = $_POST['sqlite_filename'];
		$config .= '  \'sqlite_filename\' => \'' . $_POST['sqlite_filename'] . '\',' . "\n";

		$GLOBALS['database_config']['config_options']['mysql_host'] = $_POST['mysql_host'];
		$config .= '  \'mysql_host\' => \'' . $_POST['mysql_host'] . '\',' . "\n";

		$GLOBALS['database_config']['config_options']['mysql_name'] = $_POST['mysql_name'];
		$config .= '  \'mysql_name\' => \'' . $_POST['mysql_name'] . '\',' . "\n";

		$GLOBALS['database_config']['config_options']['mysql_user'] = $_POST['mysql_user'];
		$config .= '  \'mysql_user\' => \'' . $_POST['mysql_user'] . '\',' . "\n";

		$GLOBALS['database_config']['config_options']['mysql_pass'] = $_POST['mysql_pass'];
		$config .= '  \'mysql_pass\' => \'' . $_POST['mysql_pass'] . '\',' . "\n";

		$config .= ' ),' . "\n";
		$config .= ');' . "\n";
		$config .= '?>' . "\n";

		// open ourself and rewrite with new data
		if($fp = fopen(__FILE__, "w")) {
			fwrite($fp, $config);
			fclose($fp);
			$install_message = 'Install Successful' . "\n<BR>";
			$install_message .= '<a href="' . $_SERVER['PHP_SELF'] . '">Navigate back to the web app</a>, you\'re ready to use it now!' . "\n";
			$install_good = true;
		} else {
			$install_message = 'ERROR: could not open "' . __FILE__ . '" for writing, please change its permissions';
		}
	}

	echo '<html><body><H1>One-time Installation:</H1>';
	echo '<H2>' . $install_message . '</H2>';
	if ($install_good != true) {
		echo '<form id="install" action="' . $_SERVER['PHP_SELF'] . '" method="post">';
		echo '<input type="hidden" name="configure" value="1"/>';
		echo 'Database type: <select id="type" name="type">';
		$info = (isset($GLOBALS['database_config']['type']) && $GLOBALS['database_config']['type'] == 'xml') ? 'SELECTED' : '';
		echo '<option value="xml" ' . $info . '>XML</option>';
		$info = (isset($GLOBALS['database_config']['type']) && $GLOBALS['database_config']['type'] == 'sqlite') ? 'SELECTED' : '';
		echo '<option value="sqlite" ' . $info . '>SQLite</option>';
		$info = (isset($GLOBALS['database_config']['type']) && $GLOBALS['database_config']['type'] == 'mysql') ? 'SELECTED' : '';
		echo '<option value="mysql" ' . $info . '>MySQL</option>';
		echo '</select>';
		echo '<BR>';
		$info = isset($GLOBALS['database_config']['config_options']['xml_filename']) ? 'value="' . $GLOBALS['database_config']['config_options']['xml_filename'] . '"' : '';
		echo 'For XML only, XML Filename: <input id="xml_filename" name="xml_filename" ' . $info . '/><BR>';
		$info = isset($GLOBALS['database_config']['config_options']['sqlite_filename']) ? 'value="' . $GLOBALS['database_config']['config_options']['sqlite_filename'] . '"' : '';
		echo 'For SQLite only, Filename: <input id="sqlite_filename" name="sqlite_filename" ' . $info . '/><BR>';
		$info = isset($GLOBALS['database_config']['config_options']['mysql_host']) ? 'value="' . $GLOBALS['database_config']['config_options']['mysql_host'] . '"' : '';
		echo 'For MySQL only, Hostname: <input id="mysql_host" name="mysql_host" ' . $info . '/><BR>';
		$info = isset($GLOBALS['database_config']['config_options']['mysql_name']) ? 'value="' . $GLOBALS['database_config']['config_options']['mysql_name'] . '"' : '';
		echo 'For MySQL only, Database name: <input id="mysql_name" name="mysql_name" ' . $info . '/><BR>';
		$info = isset($GLOBALS['database_config']['config_options']['mysql_user']) ? 'value="' . $GLOBALS['database_config']['config_options']['mysql_user'] . '"' : '';
		echo 'For MySQL only, Database username: <input id="mysql_user" name="mysql_user" ' . $info . '/><BR>';
		$info = isset($GLOBALS['database_config']['config_options']['mysql_pass']) ? 'value="' . $GLOBALS['database_config']['config_options']['mysql_pass'] . '"' : '';
		echo 'For MySQL only, Database password: <input id="mysql_pass" name="mysql_pass" ' . $info . '/><BR>';
		echo '<input id="form_sub" type="submit" value="Install!"/>';
		echo '</form>';
	}
	echo '</body></html>';
}

// If we are not yet configured try to install
if (!isset($GLOBALS['database_config']['configured']) || $GLOBALS['database_config']['configured'] != true) {
	install_db();
	// this will end the execution from the calling script
	exit;
}

?>

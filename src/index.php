<?PHP

/**
 * @file
 * @brief The main web application routines
 * @ingroup wa
 * @details
 *  REQUIRES: apache2 mysql-server mysql-client php5 php5-mysql php5-sqlite
 * @copyright Copyright 2012 OMSE Cell Phone GPS Team
 * @section LICENSE
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/// @todo security audit
/// @todo create a real-time (live) mode route type to track last n points

// ********* GLOBALS ************

$GLOBALS['gpswa_defaults'] = array(
	'db_ver' => '1.0.0',
	'db_username' => 'admin',
	'db_password' => 'password',
); ///< Default username and password

// ********* FUNCTIONS **********

/**
 * @ingroup wa
 * @brief This generates the stylesheets for the web app
 * @return string Returns an html string with css code
 */
function get_stylesheet() {

	$more_head = '';
	$more_head .= '<style type="text/css">' . "\n";
	$more_head .= ' body { background: #002b36 /*B03*/; height: 100%; margin: 0; padding: 0; }' . "\n";

	$more_head .= ' a:link, a:visited, a:active { color: #FFFFFF; text-decoration: none; }' . "\n";
	$more_head .= ' a:hover { color: #FFFFEE; text-decoration: none; border-bottom-width: 1px; border-bottom-style: dotted; border-bottom-color: #FFFFFF; }' . "\n";

	$more_head .= ' #map_canvas { height: 100%; }' . "\n";

	$more_head .= ' #body_container { background: #002b36 /*B03*/; }' . "\n";

	$more_head .= ' #app_title h1 { margin: 0px; text-align: center; color: #cb4b16 /*Aorange*/; background: #fdf6e3 /*B3*/; }' . "\n";
	$more_head .= ' #app_title h1 span { font-family: Georgia; }' . "\n";
	$more_head .= ' #page_header h1 { margin: 0px; padding-right: 10px; text-align: right; color: #93a1a1 /*B1*/; background: #fdf6e3 /*B3*/; }' . "\n";
	$more_head .= ' #page_header h1 span { font-size: smaller; }' . "\n";

	$more_head .= ' #menu_bar { margin-top: 5px; padding: 10px 10px 7px 10px; }' . "\n";
	$more_head .= ' #menu_bar_button { display: inline; border-top-right-radius: 15px; border-top-left-radius: 15px; border: 5px solid #eee8d5 /*B2*/; background: #eee8d5 /*B2*/; padding: 2px; margin-right: 2px; }' . "\n";
	$more_head .= ' #menu_bar_button .button_text { display: inline; }' . "\n";
	$more_head .= ' #menu_bar_button a { display: inline; color: #839496 /*B0*/; }' . "\n";

	$more_head .= ' #no_menu { margin-top: 5px; padding: 10px 10px 7px 10px; }' . "\n";
	$more_head .= ' #page_content { border: 1px dashed #839496 /*B0*/; padding: 5px; margin-left: 10px; margin-right: 10px; margin-bottom: 10px; padding-top: 20px; color: #586e75 /*B01*/; background: #073642 /*B02*/; }' . "\n";

	$more_head .= ' #login_error { text-align: center; color: #cb4b16 /*Aorange*/; }' . "\n";

	$more_head .= ' #login_form { text-align: center; }' . "\n";
	$more_head .= ' #login_form .username_field { font-variant: small-caps; }' . "\n";
	$more_head .= ' #login_form .password_field { font-variant: small-caps; }' . "\n";

	$more_head .= '</style>' . "\n";

	return $more_head;
}

/**
 * @ingroup wa
 * @brief This generates the javascript for the position sender
 * @return string Returns an html string with html javascript code
 */
function get_position_js() {

	$more_head = '';

//Docs: http://dev.w3.org/geo/api/spec-source.html
	$more_head .= '<script type="text/javascript">' . "\n";
	$more_head .= ' function start_stop_button_click(sid) {' . "\n";
	$more_head .= '  window.sessionid = sid;' . "\n";
	$more_head .= '  if (navigator.geolocation) {' . "\n";
	$more_head .= '   if (document.getElementById("start_stop_state").value == "stopped") {' . "\n";
	$more_head .= '    rname = document.getElementById("route_name").value;' . "\n";
	$more_head .= '    getNewRoute(sid, rname);' . "\n";
	$more_head .= '    document.getElementById("msg_box").innerHTML = "";' . "\n";
	$more_head .= '    //navigator.geolocation.getCurrentPosition(position_success_func, position_error_func);' . "\n";
	$more_head .= '    window.watch_id = navigator.geolocation.watchPosition(position_success_func, position_error_func, { enableHighAccuracy: true, maximumAge:31000, timeout:30000});' . "\n"; //max cache 1 min new coord every 30 sec
	$more_head .= '    document.getElementById("start_stop_button").innerHTML = "Stop";' . "\n";
	$more_head .= '    document.getElementById("start_stop_state").value = "started";' . "\n";
	$more_head .= '   } else {' . "\n";
	$more_head .= '    //it is started so we want to stop it' . "\n";
	$more_head .= '    navigator.geolocation.clearWatch(window.watch_id);' . "\n";
	$more_head .= '    document.getElementById("start_stop_button").innerHTML = "Start";' . "\n";
	$more_head .= '    document.getElementById("start_stop_state").value = "stopped";' . "\n";
	$more_head .= '   }' . "\n";
	$more_head .= '  } else {' . "\n";
/// @todo pass something into func
	$more_head .= '   position_error_func();' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= ' function position_error_func(pos_error) {' . "\n";
	$more_head .= '  var error_string = "";' . "\n";
	$more_head .= '  switch(pos_error.code) {' . "\n";
	$more_head .= '   case pos_error.PERMISSION_DENIED:' . "\n";
	$more_head .= '    error_string = "ERROR: The location acquisition process failed because the document does not have permission to use the Geolocation API."' . "\n";
	$more_head .= '    break;' . "\n";
	$more_head .= '   case pos_error.POSITION_UNAVAILABLE:' . "\n";
	$more_head .= '    error_string = "ERROR: The position of the device could not be determined."' . "\n";
	$more_head .= '    break;' . "\n";
	$more_head .= '   case pos_error.TIMEOUT:' . "\n";
	$more_head .= '    error_string = "ERROR: The length of time specified by the timeout property has elapsed before the implementation could successfully acquire a new position object."' . "\n";
	$more_head .= '    break;' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= '  appendTextDivToDiv(document.getElementById("msg_box"), error_string + " (" + pos_error.message + ")" + "<BR>");' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= ' function position_success_func(pos) {' . "\n";
	$more_head .= '  var coords = pos.coords' . "\n";
	$more_head .= '  var newmsg = "You are here: " + coords.latitude + ", " + coords.longitude + " Accurate to (m): " + coords.accuracy + "<BR>";' . "\n";
	$more_head .= '  var norestrict = document.getElementById("start_stop_restrict").checked;' . "\n";
	$more_head .= '  if(norestrict || coords.accuracy <= 65) {' . "\n";
	$more_head .= '   if(norestrict || document.getElementById("start_stop_last_pos").value != coords.latitude + "," + coords.longitude) {' . "\n";
	$more_head .= '    sendCoord(window.sessionid, window.routeid, coords.latitude, coords.longitude, coords.accuracy);' . "\n";
	$more_head .= '    document.getElementById("start_stop_last_pos").value = coords.latitude + "," + coords.longitude;' . "\n";
	$more_head .= '    appendTextDivToDiv(document.getElementById("msg_box"), newmsg);' . "\n";
	$more_head .= '   } else {' . "\n";
	$more_head .= '    //appendTextDivToDiv(document.getElementById("msg_box"), ".");' . "\n";
	$more_head .= '   }' . "\n";
	$more_head .= '  } else {' . "\n";
	$more_head .= '   appendTextDivToDiv(document.getElementById("msg_box"), "Waiting for better accuracy (<=65), current: " + coords.accuracy + "<BR>");' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= ' function appendTextDivToDiv(f_div, f_text) {' . "\n";
	$more_head .= '  var newdiv = document.createElement("div");' . "\n";
	$more_head .= '  newdiv.innerHTML = f_text;' . "\n";
	$more_head .= '  f_div.insertBefore(newdiv, f_div.firstChild);' . "\n";
//	$more_head .= '  while(newdiv.firstChild) {' . "\n";
//	$more_head .= '   f_div.appendChild(newdiv.firstChild);' . "\n";
//	$more_head .= '  }' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= ' function getNewRoute(sid, rname) {' . "\n";
	$more_head .= '  var http = false;' . "\n";
	$more_head .= '  if(navigator.appName == "Microsoft Internet Explorer") {' . "\n";
	$more_head .= '   http = new ActiveXObject("Microsoft.XMLHTTP");' . "\n";
	$more_head .= '  } else {' . "\n";
	$more_head .= '   http = new XMLHttpRequest();' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= '  http.abort();' . "\n";
	$more_head .= '  http.open("GET", "' . $_SERVER['PHP_SELF'] . '?action=new_route&sessionid=" + sid + "&route_name=" + rname, true);' . "\n";
	$more_head .= '  http.onreadystatechange=function() {' . "\n";
	$more_head .= '   if(http.readyState == 4) {' . "\n";
	$more_head .= '    var rid = http.responseXML.getElementsByTagName("i4")[0].childNodes[0].nodeValue;' . "\n";
	$more_head .= '    window.routeid = rid;' . "\n";
	$more_head .= '    appendTextDivToDiv(document.getElementById("msg_box"), "Using route_id: " + rid);' . "\n";
	$more_head .= '   }' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= '  http.send(null);' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= ' function sendCoord(sid, rid,lat,lon,acc) {' . "\n";
	$more_head .= '  var http = false;' . "\n";
	$more_head .= '  if(navigator.appName == "Microsoft Internet Explorer") {' . "\n";
	$more_head .= '   http = new ActiveXObject("Microsoft.XMLHTTP");' . "\n";
	$more_head .= '  } else {' . "\n";
	$more_head .= '   http = new XMLHttpRequest();' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= '  http.abort();' . "\n";
	$more_head .= '  http.open("GET", "' . $_SERVER['PHP_SELF'] . '?action=post_coord&sessionid=" + sid + "&route_id=" + rid + "&lat=" + lat + "&lon=" + lon + "&accuracy=" + acc, true);' . "\n";
	$more_head .= '  http.send(null);' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= '</script>' . "\n";

	return $more_head;
}

/**
 * @ingroup wa
 * @brief This generates the javascript for the google map with datapoints
 * @param[in] array $f_route This is an array of coordinate ('coord_data') keyed assoc arrays
 * @return string Returns an html string with html javascript code
 */
function get_gmap_js($f_route) {

	$more_head = '';

	$more_head .= '<script type="text/javascript">' . "\n";
	$more_head .= ' function initialize() {' . "\n";
	// FYI center is set below so this is moot
	$more_head .= '  document.getElementById("map_canvas").style.height = window.innerHeight + "px";' . "\n";
	$more_head .= '  var cloc = new google.maps.LatLng (37.422005, -122.084095);' . "\n";
	$more_head .= '  var myOptions = {' . "\n";
	$more_head .= '   center: cloc,' . "\n";
	$more_head .= '   zoom: 1,' . "\n"; 
	$more_head .= '   mapTypeId: google.maps.MapTypeId.ROADMAP' . "\n";
	$more_head .= '  };' . "\n";
	$more_head .= '  var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);' . "\n";
	$more_head .= '  var polyOptions = {' . "\n";
	$more_head .= '   strokeColor: "blue",' . "\n";
	$more_head .= '   strokeOpacity: 0.5,' . "\n";
	$more_head .= '   strokeWeight: 3,' . "\n";
	$more_head .= '  };' . "\n";
	$more_head .= '  var poly = new google.maps.Polyline(polyOptions);' . "\n";
	$more_head .= '  poly.setMap(map);' . "\n";
	$more_head .= '  var path = poly.getPath();' . "\n";
	$t_route = $f_route;
	$t_first = array_shift($t_route);
	$t_last = array_pop($t_route);
	$more_head .= '  new google.maps.Marker({position: new google.maps.LatLng(' . $t_first['coord_data'] . '), title: "Start " + "(#" + path.getLength() + ") (' . $t_first['coord_data'] . ')", map: map});' . "\n";
	foreach($f_route as $r_row) {
		$more_head .= '  path.push(new google.maps.LatLng(' . $r_row['coord_data'] . '));' . "\n";
		//$more_head .= ' new google.maps.Marker({position: new google.maps.LatLng(' . $r_row['coord_data'] . '), title: "#" + path.getLength(), map: map});' . "\n";
	}
	$more_head .= '  new google.maps.Marker({position: new google.maps.LatLng(' . $t_last['coord_data'] . '), title: "End " + "(#" + path.getLength() + ") (' . $t_last['coord_data'] . ')", map: map});' . "\n";
	$more_head .= '  var latlngbounds = new google.maps.LatLngBounds();' . "\n";
	$more_head .= '  for ( var i = 0; i < path.getLength(); i++ ) {' . "\n";
	$more_head .= '   latlngbounds.extend(path.getAt(i));' . "\n";
	$more_head .= '  }' . "\n";
	$more_head .= '  map.setCenter( latlngbounds.getCenter() );' . "\n";
	$more_head .= '  map.fitBounds(latlngbounds);' . "\n";
	$more_head .= ' }' . "\n";
	$more_head .= '</script>' . "\n";
	return $more_head;
}

// ********* CLASSES **********

/**
 * @ingroup wa
 * @brief The is a base database class/interface used to get and store position and user info in a standard way.
 * @details
 *  This class could be implemented as an inteface, but a class can do more, so here we are.
 */
abstract class base_db {

	/**
	 * @brief Gets the list of positions given the route
	 * @param[in] int $route_id The integer route identifier
	 * @return array coord_seq, coord_date, coord_data array
	 */
	abstract public function db_get_route($route_id);

	/**
	 * @brief Gets a list of all user routes
	 * @return array route_id, route_name array
	 */
	abstract public function db_get_route_list();

	/**
	 * @brief Gets the route attributes
	 * @param[in] int $route_id The integer route identifier
	 * @return array|bool route_id, route_name array or false (failure, no route id match)
	 */
	abstract public function db_get_info_for_route($route_id);

	/**
	 * @brief Writes the given position info to the database
	 * @param[in] int $route_id The integer route identifier
	 * @param[in] string $lat The string float latitude
	 * @param[in] string $lon The string float longitude
	 * @param[in] int $accuracy The int containing the accuracy of the position data in meters
	 * @param[in] int $capture_time An int containing the date/time of the coord in YYYYMMDDHHMMSS (YmdHis) format
	 * @return int 1 (num coords posted)
	 */
	abstract public function db_post_coord($route_id, $lat, $lon, $accuracy, $capture_time);

	/**
	 * @brief Gets and creates a new route id in the database
	 * @param[in] string $route_name The string name of a new route to create
	 * @return int The integer route identifier (route_id)
	 */
	abstract public function db_new_route($route_name);

	/**
	 * @brief Removes the route and all coord info from database.
	 * @param[in] int $route_id The integer route identifier
	 * @return int Removed rows count integer (rows deleted)
	 */
	abstract public function db_delete_route($route_id);

	/**
	 * @brief Renames the route in the database.
	 * @param[in] int $route_id The integer route identifier
	 * @param[in] string $new_route_name The new name of the route
	 * @return int Renamed rows count integer (rows renamed)
	 */
	abstract public function db_rename_route($route_id, $new_route_name);

	/**
	 * @brief Checks a user/password pair agains the database for a match
	 * @param[in] string $f_username A string of the username
	 * @param[in] string $f_password A string of the user password
	 * @return bool False for a failure, no match
	 */
	abstract public function db_check_user_password_match($f_username, $f_password);

	/**
	 * @brief Finds a username associated with the given sessionid
	 * @param[in] string $f_sessionid The hash of the sessionid for a user
	 * @return string|bool username string or false (failure, no session match)
	 */
	abstract public function db_get_user_for_session($f_sessionid);

	/**
	 * @brief Finds a sessionid associated with the given username
	 * @param[in] string $f_username A username string
	 * @return string|bool sessionid or false (failure, no user match)
	 */
	abstract public function db_get_session_for_user($f_username);

	/**
	 * @brief Sets the sessionid for the given username
	 * @param[in] string $f_username A username string
	 * @param[in] string $f_sessionid The hash of the sessionid for a user
	 */
	abstract public function db_set_session_for_user($f_username, $f_sessionid);

	/**
	 * @brief Sets the username and password to the given values for an authenticated sessionid
	 * @param[in] string $f_sessionid
	 *  The hash of the sessionid for a user
	 * @param[in] string $f_username
	 *  A username string
	 * @param[in] string $f_password
	 *  (optional) A string of the user password
	 */
	abstract public function db_set_new_user_password($f_sessionid, $f_username, $f_password = '');
} 
/**
 * @ingroup wa
 * @brief The is a base abstract SQL database class.
 * @details
 *  It implements the standard base_db interface calls.
 *  However, it is a base abstract SQL class (using some SQL methods just different handlers).  Not used directly.
 * @sa base_db
 */
abstract class sql_db extends base_db {

	protected $autoinc = ''; ///< Set string if it should include an auto increment flag for the indexes when creating the tables

	/**
	 * @brief This function connects to the sql database with the command and returns the response
	 * @details
	 *  It is intended to be used by handler functions that have already sanitized the options so there are no security problems.
	 *
	 *  DO NOT USE THIS FUNCTION DIRECTLY
	 * @param[in] string $query A string SQL statement (SELECT, INSERT, UPDATE, DELETE, etc)
	 * @param[in] int $mode An integer query mode.  1 = return results, 2 = return rows (insert/update)
	 * @return array|int Depends on $mode, returns array of assoc arrays or number of affected rows
	 */
	abstract protected function sql_make_unsanitized_query($query, $mode = 0);

	/**
	 * @brief This function uses a DB specific call to detect if a database table exists
	 * @param[in] string $table_name A string table name (like gpswa_routes)
	 * @return bool True or false, true if it found the table in the DB.
	 */
	abstract protected function table_exists($table_name);

	/**
	 * @brief Detects if the user table exists and assumes this is the first run and creates all of them if it doesn't.
	 */
	protected function create_new_database_tables_if_needed() {

		//initialize database tables
		if(!$this->table_exists('gpswa_users')) {
			$this->sql_make_unsanitized_query("CREATE TABLE gpswa_coords (\n" . 
			"  idx int(11) PRIMARY KEY " . $this->autoinc . ",\n" . 
			"  route_id int(11) NOT NULL,\n" . 
			"  coord_seq int(11) NOT NULL,\n" . 
			"  coord_date text NOT NULL,\n" . 
			"  coord_accuracy int(11) NOT NULL,\n" . 
			"  coord_data text NOT NULL\n" . 
			")");
			$this->sql_make_unsanitized_query("CREATE TABLE gpswa_routes (\n" . 
			"  idx int(11) PRIMARY KEY " . $this->autoinc . ",\n" . 
			"  route_id int(11) NOT NULL,\n" . 
			"  route_startdate text NOT NULL,\n" . 
			"  route_enddate text NOT NULL,\n" . 
			"  route_name text NOT NULL\n" . 
			")");
			$this->sql_make_unsanitized_query("CREATE TABLE gpswa_users (\n" . 
			"  idx int(11) PRIMARY KEY " . $this->autoinc . ",\n" . 
			"  username text NOT NULL,\n" . 
			"  password text NOT NULL,\n" . 
			"  sessionid text NOT NULL,\n" . 
			"  passhint text NOT NULL,\n" . 
			"  passtime int(11) NOT NULL\n" . 
			")");
			$this->sql_make_unsanitized_query("INSERT INTO gpswa_users (idx, username, password, sessionid, passhint, passtime) VALUES\n" . 
			"(1, '" . $GLOBALS['gpswa_defaults']['db_username'] . "', '" . crypt($GLOBALS['gpswa_defaults']['db_password']) . "', '', '', 0)", 2);
			$this->sql_make_unsanitized_query("CREATE TABLE gpswa_settings (\n" . 
			"  idx int(11) PRIMARY KEY " . $this->autoinc . ",\n" . 
			"  ver text NOT NULL\n" . 
			")");
			$this->sql_make_unsanitized_query("INSERT INTO gpswa_settings (idx, ver) VALUES\n" . 
			"(1, '" . $GLOBALS['gpswa_defaults']['db_ver'] . "')", 2);
		}
	}

	/**
	 * @brief This function escapes all special SQL characters so it is safe to pass to a database without fearing injection attacks.
	 * @param[in] string $inp A string to be reformatted
	 * @return string SQL escaped string
	 */
	protected function sql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);
		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}
		return $inp;
	}

	/**
	 * @copydoc base_db::db_get_route
	 * @sa base_db::db_get_route
	 */
	public function db_get_route($route_id) {
		//sanitize
		if (!is_numeric($route_id)) {
			return array(); //just return an empty array
		}
		return $this->sql_make_unsanitized_query("SELECT coord_seq,coord_date,coord_accuracy,coord_data FROM gpswa_coords WHERE route_id = '" . $route_id . "' ORDER BY coord_seq");
	}

	/**
	 * @copydoc base_db::db_get_route_list
	 * @sa base_db::db_get_route_list
	 */
	public function db_get_route_list() {
		return $this->sql_make_unsanitized_query("SELECT route_id,route_name FROM gpswa_routes ORDER BY route_id");
	}

	/**
	 * @copydoc base_db::db_get_info_for_route
	 * @sa base_db::db_get_info_for_route
	 */
	public function db_get_info_for_route($route_id) {
		//sanitize with mysql_real_escape_string
		$rows = $this->sql_make_unsanitized_query("SELECT * FROM gpswa_routes WHERE route_id = '" . $this->sql_escape_mimic($route_id) . "'");
	
		if (isset($rows[0]) && !empty($rows[0])) {
			return $rows[0];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_post_coord
	 * @sa base_db::db_post_coord
	 */
	public function db_post_coord($route_id, $lat, $lon, $accuracy, $capture_time) {
		//sanitize
		if (!is_numeric($route_id) || !is_numeric($lat) || !is_numeric($lon) || !is_numeric($accuracy) || !is_numeric($capture_time)) {
			return 0; //did not insert anything
		}
		$max = $this->sql_make_unsanitized_query("SELECT MAX(coord_seq) as max FROM gpswa_coords WHERE route_id = " . $route_id);
		/// @todo make it zero for the first one, dont know what is happening here right now
		$seq = $max[0]['max'] + 1;
		$this->sql_make_unsanitized_query("INSERT INTO gpswa_coords (idx, route_id, coord_seq, coord_date, coord_accuracy, coord_data) VALUES (NULL,'" . $route_id . "', '" . $seq . "', '" . $capture_time . "', $accuracy, '" . $lat . "," . $lon . "')", 2);
		return 1; //only doing one at a time
	}

	/**
	 * @copydoc base_db::db_new_route
	 * @sa base_db::db_new_route
	 */
	public function db_new_route($route_name) {
		$max = $this->sql_make_unsanitized_query("SELECT MAX(route_id) as max FROM gpswa_routes");
		$seq = $max[0]['max'] + 1;
		$this->sql_make_unsanitized_query("INSERT INTO gpswa_routes (idx, route_id, route_startdate, route_enddate,route_name) VALUES (NULL,'" . $seq . "', '" . date("YmdHis") . "', '', '" . $this->sql_escape_mimic($route_name) . "')", 2);
		return $seq; //only doing one at a time
	}
	/// @todo flag the route closed

	/**
	 * @copydoc base_db::db_delete_route
	 * @sa base_db::db_delete_route
	 */
	public function db_delete_route($route_id) {
		//sanitize
		if (!is_numeric($route_id)) {
			return array(); //just return an empty array
		}
		$coord_rows = $this->sql_make_unsanitized_query("DELETE FROM gpswa_coords WHERE route_id = '" . $route_id . "'", 2);
		$route_rows = $this->sql_make_unsanitized_query("DELETE FROM gpswa_routes WHERE route_id = '" . $route_id . "'", 2);
		return ($coord_rows + $route_rows);
	}
	
	/**
	 * @copydoc base_db::db_rename_route
	 * @sa base_db::db_rename_route
	 */
	public function db_rename_route($route_id, $new_route_name) {
		//sanitize
		if (!is_numeric($route_id)) {
			return 0; //just return none updated
		}
		$num_changed = $this->sql_make_unsanitized_query("UPDATE gpswa_routes SET route_name = '" . $this->sql_escape_mimic($new_route_name) . "' WHERE route_id = '" . $route_id . "'", 2);
		return ($num_changed);
	}

	/**
	 * @copydoc base_db::db_check_user_password_match
	 * @sa base_db::db_check_user_password_match
	 */
	public function db_check_user_password_match($f_username, $f_password) {
		$rows = $this->sql_make_unsanitized_query("SELECT password FROM gpswa_users WHERE username = '" . $this->sql_escape_mimic($f_username) . "'");
		if (isset($rows[0]) && isset($rows[0]['password'])) {
			if (crypt($f_password, $rows[0]['password']) == $rows[0]['password']) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_get_user_for_session
	 * @sa base_db::db_get_user_for_session
	 */
	public function db_get_user_for_session($f_sessionid) {
		//sanitize with mysql_real_escape_string
		$rows = $this->sql_make_unsanitized_query("SELECT username FROM gpswa_users WHERE sessionid = '" . $this->sql_escape_mimic($f_sessionid) . "'");
	
		if (isset($rows[0]) && isset($rows[0]['username'])) {
			return $rows[0]['username'];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_get_session_for_user
	 * @sa base_db::db_get_session_for_user
	 */
	public function db_get_session_for_user($f_username) {
		//sanitize with mysql_real_escape_string
		$rows = $this->sql_make_unsanitized_query("SELECT sessionid FROM gpswa_users WHERE username = '" . $this->sql_escape_mimic($f_username) . "'");
	
		if (isset($rows[0]) && isset($rows[0]['sessionid'])) {
			return $rows[0]['sessionid'];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_set_session_for_user
	 * @sa base_db::db_set_session_for_user
	 */
	public function db_set_session_for_user($f_username, $f_sessionid) {
		//sanitize with mysql_real_escape_string
		$this->sql_make_unsanitized_query("UPDATE gpswa_users SET sessionid = '" . $this->sql_escape_mimic($f_sessionid) . "' WHERE username = '" . $this->sql_escape_mimic($f_username) . "'", 2);
	}

	/**
	 * @copydoc base_db::db_set_new_user_password
	 * @sa base_db::db_set_new_user_password
	 */
	public function db_set_new_user_password($f_sessionid, $f_username, $f_password = '') {
		//sanitize with mysql_real_escape_string
		if ($f_password != '') {
			$this->sql_make_unsanitized_query("UPDATE gpswa_users SET username = '" . $this->sql_escape_mimic($f_username) . "', password = '" . crypt($f_password) . "' WHERE sessionid = '" . $this->sql_escape_mimic($f_sessionid) . "'", 2);
		} else {
			$this->sql_make_unsanitized_query("UPDATE gpswa_users SET username = '" . $this->sql_escape_mimic($f_username) . "' WHERE sessionid = '" . $this->sql_escape_mimic($f_sessionid) . "'", 2);
		}
	}

}

/**
 * @ingroup wa
 * @brief The is the SQLite database class.
 * @details
 *  It uses the standard sql_db interface calls just different handlers that make SQLite unique from standard SQL.
 *
 *  REQUIRES php5-sqlite
 * @sa sql_db
 */
class sqlite_db extends sql_db {

	protected $db_file = ''; ///< location path of sqlite db file
	protected $db_mode = 0666; ///< Permission mode for opening database

	/**
	 * @copydoc sql_db::sql_make_unsanitized_query
	 * @sa sql_db::sql_make_unsanitized_query
	 */
	protected function sql_make_unsanitized_query($query, $mode = 0) {

		$rows = array();
		/// @todo do something with the die messages
		/// @todo make a sql debug output mode
		$link = sqlite_open($this->db_file, $this->db_mode, $sqlite_error)
		        or die('No open: ' . $sqlite_error);
		$result = sqlite_query($link, $query, SQLITE_ASSOC, $sqlite_error)
		//$ok = sqlite_exec($link, $query, $sqlite_error);
		        or die('No query: ' . $sqlite_error);

		// if there are rows to get
		if($mode == 2) {
			//insert or update
			$rows = sqlite_changes($link);
		} else {
			//select
			while($rows[] = sqlite_fetch_array($result, SQLITE_ASSOC));
			array_pop($rows); // pop the last row off, which is an empty row
		}

		sqlite_close($link);

		return $rows;
	}

	/**
	 * @copydoc sql_db::table_exists
	 * @sa sql_db::table_exists
	 */
	protected function table_exists($table_name) {

		$rows = $this->sql_make_unsanitized_query("SELECT * FROM sqlite_master WHERE type='table' and name='" . $table_name . "'");
		return (!empty($rows));
	}

	/**
	 * @brief Constructor to create a new sqlite_db, creates initial tables if needed
	 * @param[in] string $f_file A string that contains the sqlite filepath
	 * @param[in] int $f_mode An integer that contains the mode to open the file database
	 */
	public function __construct($f_file, $f_mode = 0666) {
		$this->db_file = $f_file;
		$this->db_mode = $f_mode;

		$this->create_new_database_tables_if_needed();
		/// @todo get sql handle and keep?
	}

}

/**
 * @ingroup wa
 * @brief The is the MySQL database class.
 * @details
 *  It uses the standard sql_db interface calls just different handlers that make MySQL unique from standard SQL.
 *
 *  REQUIRES mysql
 * @sa sql_db
 */
class mysql_db extends sql_db {

	protected $db_host = ''; ///< Server/hostname of where SQL database server is running
	protected $db_name = ''; ///< Name of the MySQL database to use
	protected $db_user = ''; ///< the database username
	protected $db_pass = ''; ///< the plaintext database password

	protected $autoinc = 'AUTO_INCREMENT'; ///< @see sql_db

	/**
	 * @copydoc sql_db::sql_make_unsanitized_query
	 * @sa sql_db::sql_make_unsanitized_query
	 */
	protected function sql_make_unsanitized_query($query, $mode = 0) {
		$rows = array();
		/// @todo do something with the die messages
		/// @todo make a sql debug output mode
		$link = mysql_connect($this->db_host, $this->db_user, $this->db_pass)
		        or die('No connect: ' . mysql_error());
		mysql_select_db($this->db_name)
		        or die('No select: ' . mysql_error());
		//$query = 'SELECT * FROM gpswa_coords';
		$result = mysql_query($query)
		        or die('No query: ' . mysql_error());

		// if there are rows to get
		if($mode == 2) {
			//insert or update
			$rows = mysql_affected_rows();
		} else {
			//select
			while($rows[] = mysql_fetch_array($result, MYSQL_ASSOC));
			array_pop($rows); // pop the last row off, which is an empty row
			//mysql_free_result($result); /// @todo should we free - no rows?
		}

		mysql_close($link);

		return $rows;
	}

	/**
	 * @copydoc sql_db::table_exists
	 * @sa sql_db::table_exists
	 */
	protected function table_exists($table_name) {

		$rows = $this->sql_make_unsanitized_query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . $this->db_name . "' and TABLE_NAME = '" . $table_name . "'");
		return (!empty($rows));
	}

	/**
	 * @brief Constructor to create a new mysql_db, creates initial tables if needed
	 * @param[in] string $f_host A string that contains the server/hostname where the SQL server is running
	 * @param[in] string $f_name A string that contains the name of the MySQL database to use
	 * @param[in] string $f_user A string that contains the username
	 * @param[in] string $f_pass A string that contains the database password
	 */
	public function __construct($f_host, $f_name, $f_user, $f_pass) {
		$this->db_host = $f_host;
		$this->db_name = $f_name;
		$this->db_user = $f_user;
		$this->db_pass = $f_pass;

		$this->create_new_database_tables_if_needed();
		/// @todo get sql handle and keep?
	}

}

/**
 * @ingroup wa
 * @brief The is the XML database class.
 * @details
 *  It implements the standard base_db interface calls.
 *
 *  REQUIRES SimpleXMLElement php module (usually comes standard)
 * @sa base_db
 * @todo the bulk of xml queries are not sanitized to not contain xpath characters, security problem
 */
class xml_db extends base_db {

	protected $xml_filename = ''; ///< An xml file path for the .xml db file

	/**
	 * @brief Constructor to create a new xml_db, creates initial tables if needed
	 * @param[in] string $f_xmlfile A string that contains the database filename path
	 */
	public function __construct($f_xmlfile) {
		$this->xml_filename = $f_xmlfile;

		// initialize the file if it doesnt exist
		if (!file_exists($this->xml_filename)) {
			$xml = new SimpleXMLElement("<gps_db><routes></routes><coords></coords><users></users><settings></settings></gps_db>");
			$xml->users->addChild('user');
			$xml->users->user->addChild('username', $GLOBALS['gpswa_defaults']['db_username']);
			$xml->users->user->addChild('password', crypt($GLOBALS['gpswa_defaults']['db_password']));
			$xml->users->user->addChild('sessionid', '');
			$xml->users->user->addChild('passhint', '');
			$xml->users->user->addChild('passtime', '');
			$xml->settings->addChild('ver', $GLOBALS['gpswa_defaults']['db_ver']);
			$this->xml_write_to_file($xml);
		}
		/// @todo get file handle and keep?
	}

	/**
	 * @brief Function that writes a SimpleXMLElement to the class $xml_filename file
	 * @param[in] string $f_xml A string that contains the SimpleXMLElement to be written to file
	 * @return bool True or false value if the write succeeded
	 */
	protected function xml_write_to_file($f_xml) {
		if($fp = fopen($this->xml_filename, "w")) {
			if (flock($fp, LOCK_EX)) {
				fwrite($fp, $f_xml->asXML());
				fflush($fp);
				flock($fp, LOCK_UN);
			} else {
				fclose($fp);
				return false;
			}
			fclose($fp);
		} else {
			return false;
		}
		return true;
	}

	/**
	 * @brief This function opens, parses, modifies and writes XML back to an xml file database
	 * @param[in] string $query
	 *  A string xpath statement
	 * @param[in] int $mode
	 *  An integer query mode.  0 = return results, 2 = insert, 3 = update, 4 = delete 
	 * @param[in] string $update_value
	 *  The string that is used to update the entry when mode is set to update or used as key when inserting a value
	 * @param[in] string $insert_value
	 *  The string that is used when inserting a value into the given update_value field name
	 * @return array|int Depends on $mode, returns SimpleXMLElement or number of affected rows
	 */
	protected function xml_query($query, $mode = 0, $update_value = '', $insert_value = '') {

		$result = array();

		// parse the file
		$xml = new SimpleXMLElement(file_get_contents($this->xml_filename));

		switch($mode) {
			case 0: //get
				$result = $xml->xpath($query);
				break;

			case 2: //insert
				$result = 1;
				$ret = $xml->xpath($query);
				$ret[0]->addChild($update_value, $insert_value);
				$this->xml_write_to_file($xml);
				break;

			case 3: //update value
				$result = 1;
				$node = $xml->xpath($query);
				$node[0][0] = $update_value;
				$this->xml_write_to_file($xml);
				break;

			case 4: //delete node
				$result = 0;
				$nodes = $xml->xpath($query);
				foreach($nodes as $node) {
					$dxml = dom_import_simplexml($node);
					$dxml->parentNode->removeChild($dxml);
					$result++;
				}
				$this->xml_write_to_file($xml);
				break;
		}

		return $result;

	}

	/**
	 * @brief This function converts a SimpleXMLElement into an array of assoc array
	 * @param[in] SimpleXMLElement $xml XML to be converted
	 * @return array returns Array of assoc arrays
	 * @todo find a better conversion that does recursive etc
	 */
	protected function xml_to_array($xml) {
		$array = (array)$xml;
		foreach ( array_slice($array, 0) as $key => $value ) {
			$array[$key] = empty($value) ? NULL : (array) $value;
			//if ( $value instanceof SimpleXMLElement ) {
			//	$array[$key] = empty($value) ? NULL : toArray($value);
			//}
		}
		return $array;
	}

	/**
	 * @copydoc base_db::db_get_route
	 * @sa base_db::db_get_route
	 */
	public function db_get_route($route_id) {
		//sanitize
		if (!is_numeric($route_id)) {
			return array(); //just return an empty array
		}
		$ret = $this->xml_query('/gps_db/coords/coord[route_id=' . $route_id . ']');
		return $this->xml_to_array($ret);
	}

	/**
	 * @copydoc base_db::db_get_route_list
	 * @sa base_db::db_get_route_list
	 */
	public function db_get_route_list() {
		$ret = $this->xml_query('/gps_db/routes/route');
		return $this->xml_to_array($ret);
	}

	/**
	 * @copydoc base_db::db_get_info_for_route
	 * @sa base_db::db_get_info_for_route
	 */
	public function db_get_info_for_route($route_id) {
		$ret = $this->xml_query("/gps_db/routes/*[route_id='" . $route_id . "']");
		$rows = $this->xml_to_array($ret);
	
		if (isset($rows[0]) && !empty($rows[0])) {
			return $rows[0];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_post_coord
	 * @sa base_db::db_post_coord
	 */
	public function db_post_coord($route_id, $lat, $lon, $accuracy, $capture_time) {
		//sanitize
		if (!is_numeric($route_id) || !is_numeric($lat) || !is_numeric($lon) || !is_numeric($accuracy) || !is_numeric($capture_time)) {
			return 0; //did not insert anything
		}
		$max = $this->xml_query('/gps_db/coords/coord[route_id=' . $route_id . ']/coord_seq');
		$max_array = array_map('intval', $max);
		$seq = 0;
		if(!empty($max_array)) {
			$seq = max($max_array) + 1;
		}

		$this->xml_query('/gps_db/coords', 2, 'coord', "\n");
		$this->xml_query('/gps_db/coords/coord[last()]', 2, 'route_id', $route_id);
		$this->xml_query('/gps_db/coords/coord[last()]', 2, 'coord_seq', $seq);
		$this->xml_query('/gps_db/coords/coord[last()]', 2, 'coord_date', $capture_time);
		$this->xml_query('/gps_db/coords/coord[last()]', 2, 'coord_accuracy', $accuracy);
		$this->xml_query('/gps_db/coords/coord[last()]', 2, 'coord_data', $lat . ',' . $lon);
		return 1; //only doing one at a time
	}

	/**
	 * @copydoc base_db::db_new_route
	 * @sa base_db::db_new_route
	 */
	public function db_new_route($route_name) {
		$seq = 0;

		$max = $this->xml_query('/gps_db/routes/route/route_id');
		$max_array = array_map('intval', $max);
		$seq = max($max_array) + 1;
		$this->xml_query('/gps_db/routes', 2, 'route', "\n");
		$this->xml_query('/gps_db/routes/route[last()]', 2, 'route_id', $seq);
		$this->xml_query('/gps_db/routes/route[last()]', 2, 'route_startdate', date("YmdHis"));
		$this->xml_query('/gps_db/routes/route[last()]', 2, 'route_enddate', '');
		$this->xml_query('/gps_db/routes/route[last()]', 2, 'route_name', $route_name);
		return $seq;
	}

	/**
	 * @copydoc base_db::db_delete_route
	 * @sa base_db::db_delete_route
	 */
	public function db_delete_route($route_id) {
		//sanitize
		if (!is_numeric($route_id)) {
			return array(); //just return an empty array
		}
		$coord_rows = $this->xml_query("/gps_db/coords/coord[route_id='" . $route_id . "']", 4);
		$route_rows = $this->xml_query("/gps_db/routes/route[route_id='" . $route_id . "']", 4);
		return ($coord_rows + $route_rows);
	}

	/**
	 * @copydoc base_db::db_rename_route
	 * @sa base_db::db_rename_route
	 */
	public function db_rename_route($route_id, $new_route_name) {
		//sanitize
		if (!is_numeric($route_id)) {
			return 0; //just return none updated
		}
		$num_changed = $this->xml_query("/gps_db/routes/route[route_id='" . $route_id . "']/route_name", 3, $new_route_name);
		return ($num_changed);
	}

	/**
	 * @copydoc base_db::db_check_user_password_match
	 * @sa base_db::db_check_user_password_match
	 */
	public function db_check_user_password_match($f_username, $f_password) {
		$ret = $this->xml_query("/gps_db/users/user[username='" . $f_username . "']");
		$rows = $this->xml_to_array($ret);

		if (isset($rows[0]) && isset($rows[0]['password'])) {
			if (crypt($f_password, $rows[0]['password']) == $rows[0]['password']) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_get_user_for_session
	 * @sa base_db::db_get_user_for_session
	 */
	public function db_get_user_for_session($f_sessionid) {
		$ret = $this->xml_query("/gps_db/users/user[sessionid='" . $f_sessionid . "']");
		$rows = $this->xml_to_array($ret);
	
		if (isset($rows[0]) && isset($rows[0]['username'])) {
			return $rows[0]['username'];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_get_session_for_user
	 * @sa base_db::db_get_session_for_user
	 */
	public function db_get_session_for_user($f_username) {
		$ret = $this->xml_query("/gps_db/users/user[username='" . $f_username . "']");
		$rows = $this->xml_to_array($ret);
	
		if (isset($rows[0]) && isset($rows[0]['sessionid'])) {
			return $rows[0]['sessionid'];
		}
		return false;
	}

	/**
	 * @copydoc base_db::db_set_session_for_user
	 * @sa base_db::db_set_session_for_user
	 */
	public function db_set_session_for_user($f_username, $f_sessionid) {
		$ret = $this->xml_query("/gps_db/users/user[username='" . $f_username . "']/sessionid", 3, $f_sessionid);
	}

	/**
	 * @copydoc base_db::db_set_new_user_password
	 * @sa base_db::db_set_new_user_password
	 */
	public function db_set_new_user_password($f_sessionid, $f_username, $f_password = '') {
		$ret = $this->xml_query("/gps_db/users/user[sessionid='" . $f_sessionid . "']/username", 3, $f_username);
		if ($f_password != '') {
			$ret = $this->xml_query("/gps_db/users/user[sessionid='" . $f_sessionid . "']/password", 3, crypt($f_password));
		}
	}
}

/**
 * @ingroup wa
 * @brief This class is used for authentication
 * @details
 *  It needs a database to authenticate
 */
class Auth {

	private $db = NULL; ///< The database interface for user authentication information
	public $authenticatedSession = ''; ///< Flag if the user is authorized.  Initialized to empty aka not authenticated

	/**
	 * @brief Initializes the class with required database
	 * @param[in] object $f_db
	 *  An instance of the base_db to use for authentication
	 */
	public function __construct($f_db) {
		$this->db = $f_db;
	}

	/**
	 * @brief Says if the user has logged in or passed a valid session
	 * @return bool True or false value if the user has authenticated or not
	 */
	public function isAuthorized() {
		if ($this->authenticatedSession != '') {
			return true;
		}
		return false;
	}

	/**
	 * @brief Validates the session against the database and saves the sessionid for later use
	 * @param[in] string $sessionid
	 *   Session value to check if valid
	 * @return bool True or false value if it could find a valid session to use for authentication
	 */
	public function validate_session($sessionid) {
	
		//try to use the passed in sessionid
		$mysessionid = $sessionid;

		//check if we are already authenticated
		if($this->authenticatedSession != '') {
			$mysessionid = $this->authenticatedSession;
		}

		// Check the id and authenticate if valid
		$user = $this->db->db_get_user_for_session($mysessionid);
		if (isset($user) && $user != '') {
			$this->authenticatedSession = $mysessionid;
			return true;
		}
		return false;
	}

	/**
	 * @brief This is to check if a username and password are valid
	 * @param[in] string $username
	 *  The username to authenticate against the database
	 * @param[in] string $password
	 *  The cleartext password to authenticate against the database
	 * @return bool True or false value based on if the username and password were correct
	 */
	public function authenticate($username, $password) {

		// Check the names and authenticate if valid
		if($this->db->db_check_user_password_match($username, $password)) {
			// we passed, now look for a session to attach to
			$mysession = $this->db->db_get_session_for_user($username);
			if (!isset($mysession) || $mysession == '') {
				//generate session
				$mysession = md5($password . date("YmdHis"));
				$this->db->db_set_session_for_user($username, $mysession);
			}
			return $this->validate_session($mysession);
		}
		// failed authentication
		return false;
	}

	/**
	 * @brief This function invalidates the current sessionid for the authenticated user
	 * @return int 1 for true or 0 for false based on if a valid session was deauthenticated
	 */
	public function logout() {
		$myusername = $this->db->db_get_user_for_session($this->authenticatedSession);
		if (isset($myusername) && $myusername != '') {
			$this->db->db_set_session_for_user($myusername, '');
			return 1;
		}
		return 0;
	}
}

/**
 * @ingroup wa
 * @brief The is a base command class/interface used to store action information in a standard way.
 * @details
 *  A command is made by passing in the command as an "action" HTTP POST/GET variable.
 *  For example http://host/index.php?action=ACTION&arg1=ARG1
 *  However, it is a base abstract command class.  Not used directly.
 *
 *  The constructor should be created unique for each implementing command class for parsing the correct command elements from the request.
 */
abstract class cmd_base {
	public $isComplete = false; ///< If the required arguments have all been parsed
	public $required_args = array(); ///< Required arguments
	public $optional_args = array(); ///< Optional arguments

	/**
	 * @brief Executes the working command and saves off the important info
	 * @param[in] object $f_db
	 *   The database object of type base_db
	 * @param[in] object $f_auth
	 *   The auth object of type auth
	 * @return string Full XML response to be used or printed by caller - see the Mobile and Web ICD
	 */
	abstract public function execute($f_db, $f_auth);

	/**
	 * @brief Parses all the GET or POST requests for the required and optional action arguments
	 * @details
	 * GLOBAL USED: string $_GET["action"]
	 *  The requested action command, may also be a POST using $_POST["action"]
	 * GLOBAL USED: string $_GET
	 *  All the other get and post arguments to action, may also be a POST using $_POST
	 */
	public function parse() {

		$this->isComplete = true;
		foreach($this->required_args as $key => $value) {
			if (isset($_GET[$key])) {
				$this->required_args[$key] = $_GET[$key];
			} elseif (isset($_POST[$key])) {
				$this->required_args[$key] = $_POST[$key];
			} else {
				// An error occurred, these are required fields
				$this->isComplete = false;
			}
		}

		foreach($this->optional_args as $key => $value) {
			if (isset($_GET[$key])) {
				$this->optional_args[$key] = $_GET[$key];
			} elseif (isset($_POST[$key])) {
				$this->optional_args[$key] = $_POST[$key];
			} else {
				// This field has not been filled in so remove it
				unset($this->optional_args[$key]);
			}
		}
	}

	/**
	 * @brief This function takes a SimpleXMLElement and returns a pretty formatted xml string
	 * @param[in] object $simplexml A SimpleXMLElement object
	 * @return string Full XML response to be used or printed by caller
	 */
	protected function pp_xml($simplexml) {
		$dom = dom_import_simplexml($simplexml)->ownerDocument;
		$dom->formatOutput = true;
		return $dom->saveXML();
	}

	/**
	 * @brief This function creates a xml response from an error code 
	 * @param[in] int $faultCode
	 *  An error code value
	 * @param[in] sting $faultString
	 *  An error code descriptive string
	 * @return string Full XML-RPC response to be used or printed by caller
	 * @details See the Web and Mobile ICD
	 */
	public function get_error_output($faultCode, $faultString) {
		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$n = $xml->addChild('fault');
		$v = $n->addChild('value');
		$s = $v->addChild('struct');

		$m1 = $s->addChild('member');
		$n1 = $m1->addChild('name', 'faultCode');
		$v1 = $m1->addChild('value');
		$i1 = $v1->addChild('int', $faultCode);

		$m2 = $s->addChild('member');
		$n2 = $m2->addChild('name', 'faultString');
		$v2 = $m2->addChild('value');
		$i2 = $v2->addChild('string', htmlspecialchars($faultString));

		return $this->pp_xml($xml);
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when generating a new route on request
 * @sa cmd_base
 * @details
 *  This is the new route sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_new_route;
 *  |||;
 *  main box cmd_new_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_new_route [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_new_route=>base_db [label="db_new_route()", URL="\ref base_db::db_new_route()"];
 *  cmd_new_route<<base_db;
 *  cmd_new_route=>cmd_new_route [label="response()", URL="\ref cmd_new_route::response()"];
 *  Controller<<cmd_new_route;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_new_route extends cmd_base {

	private $route_id = 0; ///< The route identifier returned by the database

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 * GLOBAL USED: string $_GET["route_name"]
	 *  (optional) A string that contains the name of the route to be created
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '',);
		$this->optional_args = array('route_name' => '',);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$r = $v->addChild('i4', htmlspecialchars($this->route_id));

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function creates a new route index for the given sessionid
	 */
	public function execute($f_db, $f_auth) {

		// set defaults for optional args
		$route_name = isset($this->optional_args['route_name']) ? $this->optional_args['route_name'] : '';
		
		$this->route_id = $f_db->db_new_route($route_name);

		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when posting coordinates.
 * @sa cmd_base
 * @details
 *  This is for the posting coords (location) sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_post_coord;
 *  |||;
 *  main box cmd_post_coord [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_post_coord [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_post_coord=>base_db [label="db_get_info_for_route()", URL="\ref base_db::db_get_info_for_route()"];
 *  cmd_post_coord<<base_db;
 *  cmd_post_coord=>base_db [label="db_post_coord()", URL="\ref base_db::db_post_coord()"];
 *  cmd_post_coord<<base_db;
 *  cmd_post_coord=>cmd_post_coord [label="response()", URL="\ref cmd_post_coord::response()"];
 *  Controller<<cmd_post_coord;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_post_coord extends cmd_base {

	private $num = 0; ///< Number of coords successfully posted

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 * GLOBAL USED: int $_GET["route_id"]
	 *  An integer that contains the Route ID to add the coord to
	 * GLOBAL USED: float $_GET["lat"]
	 *  A float that contains the Latitude in Decimal Degrees
	 * GLOBAL USED: float $_GET["lon"]
	 *  A float that contains the Longitude in Decimal Degrees
	 * GLOBAL USED: string $_GET["capture_time"]
	 *  (optional) A string that contains the date/time of the coord in YYYYMMDDHHMMSS (YmdHis) format
	 * GLOBAL USED: int $_GET["accuracy"]
	 *  (optional) An integer that contains the accuracy of the position data in meters
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '', 'route_id' => 0, 'lat' => 0, 'lon' => 0,);
		$this->optional_args = array('capture_time' => '', 'accuracy' => 0,);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$r = $v->addChild('i4', htmlspecialchars($this->num));

		return $this->pp_xml($xml);

	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function stores the given coords in the routeid for the given sessionid
	 *  Sets default capture_time and accuracy if not given and saves the number of coords posted
	 */
	public function execute($f_db, $f_auth) {

		// set defaults for optional args
		$capture_time = isset($this->optional_args['capture_time']) ? $this->optional_args['capture_time'] : date("YmdHis");
		$accuracy = isset($this->optional_args['accuracy']) ? $this->optional_args['accuracy'] : 0;

		if (!$f_db->db_get_info_for_route($this->required_args['route_id'])) {
			return $this->get_error_output(-3,'Route does not exist, create a new route first');
		}

		$this->num = $f_db->db_post_coord($this->required_args['route_id'], $this->required_args['lat'], $this->required_args['lon'], $accuracy, $capture_time);

		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when a user authenticates
 * @sa cmd_base
 * @details
 *  This is the user login sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_user_login;
 *  |||;
 *  main box cmd_user_login [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_user_login [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_user_login=>Auth [label="authenticate(username,password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_get_session_for_user()", URL="\ref base_db::db_get_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_set_session_for_user()", URL="\ref base_db::db_set_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  cmd_user_login<<Auth;
 *  cmd_user_login=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  cmd_user_login<<Auth;
 *  cmd_user_login=>cmd_user_login [label="response()", URL="\ref cmd_user_login::response()"];
 *  Controller<<cmd_user_login;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_user_login extends cmd_base {

	private $sessionid = ''; ///< authenticated session id

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["username"]
	 *  A string that contains the Username
	 * GLOBAL USED: string $_GET["password"]
	 *  A string that contains the plaintext password of the user
	 */
	public function __construct() {
		$this->required_args = array('username' => '', 'password' => '',);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$r = $v->addChild('string', htmlspecialchars($this->sessionid));

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function authenticates the user and returns a sessionid
	 *  Failed login gives an error or sucessful login gives a valid sessionid
	 */
	public function execute($f_db, $f_auth) {

		$f_auth->authenticate($this->required_args['username'], $this->required_args['password']);
		if (!$f_auth->isAuthorized()) {
			return $this->get_error_output(-2,'Authentication Failure');
		}
		$this->sessionid = $f_auth->authenticatedSession;
		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when a user deauthenticates
 * @sa cmd_base
 * @details
 *  This is the user logout sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_user_logout;
 *  |||;
 *  main box cmd_user_logout [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_user_logout [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_user_logout=>Auth [label="logout()", URL="\ref Auth::logout()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_set_session_for_user()", URL="\ref base_db::db_set_session_for_user()"];
 *  Auth<<base_db;
 *  cmd_user_logout<<Auth;
 *  cmd_user_logout=>cmd_user_logout [label="response()", URL="\ref cmd_user_logout::response()"];
 *  Controller<<cmd_user_logout;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_user_logout extends cmd_base {

	private $success = 0; ///< success value 1 for true

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '',);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$r = $v->addChild('i4', htmlspecialchars($this->success));

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function deauthenticates the logged in user 
	 *  return with int success value, 1 for true 0 for false
	 */
	public function execute($f_db, $f_auth) {
		$this->success = $f_auth->logout();
		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when a list of routes is requested
 * @sa cmd_base
 * @details
 *  This is for getting the list of routes sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_get_route_list;
 *  |||;
 *  main box cmd_get_route_list [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_get_route_list [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_get_route_list=>base_db [label="db_get_route_list()", URL="\ref base_db::db_get_route_list()"];
 *  cmd_get_route_list<<base_db;
 *  cmd_get_route_list=>cmd_get_route_list [label="response()", URL="\ref cmd_get_route_list::response()"];
 *  Controller<<cmd_get_route_list;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_get_route_list extends cmd_base {

	private $r_rows = array(); ///< Array of routes

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '',);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$a = $v->addChild('array');
		$d = $a->addChild('data');

		foreach($this->r_rows as $r_row) {
			$r = $d->addChild('value');
			$r->addChild('i4', htmlspecialchars($r_row['route_id']));
		}

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function gets all available route ids
	 *  with array of route_ids
	 */
	public function execute($f_db, $f_auth) {
		$this->r_rows = $f_db->db_get_route_list();
		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when a list of coords for a given route are requested
 * @sa cmd_base
 * @details
 *  This is for getting the coords in a given route sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_get_route;
 *  |||;
 *  main box cmd_get_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_get_route [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_get_route=>base_db [label="db_get_route()", URL="\ref base_db::db_get_route()"];
 *  cmd_get_route<<base_db;
 *  cmd_get_route=>cmd_get_route [label="response()", URL="\ref cmd_get_route::response()"];
 *  Controller<<cmd_get_route;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class cmd_get_route extends cmd_base {

	private $r_rows = array(); ///< Array of routes

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 * GLOBAL USED: int $_GET["route_id"]
	 *  An integer that contains the Route ID to add the coord to
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '', 'route_id' => 0,);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {
	
		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$a = $v->addChild('array');
		$d = $a->addChild('data');

		foreach($this->r_rows as $r_row) {
			$split_coord = explode(',', $r_row['coord_data']);

			$l = $d->addChild('value');
			$s = $l->addChild('struct');

			$m1 = $s->addChild('member');
			$n1 = $m1->addChild('name', 'lat');
			$v1 = $m1->addChild('value');
			$i1 = $v1->addChild('string', htmlspecialchars($split_coord[0]));

			$m2 = $s->addChild('member');
			$n2 = $m2->addChild('name', 'lon');
			$v2 = $m2->addChild('value');
			$i2 = $v2->addChild('string', htmlspecialchars($split_coord[1]));
		}

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function gets the details of a given route_id (position coords)
	 *  with array of lat lon pairs
	 */
	public function execute($f_db, $f_auth) {
		$this->r_rows = $f_db->db_get_route($this->required_args['route_id']);
		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used when the user would like to rename a route given a route id and new name
 * @sa cmd_base
 * @details
 *  This is for renaming a route
 * @msc
 *  main,Controller,base_db,Auth,cmd_rename_route;
 *  |||;
 *  main box cmd_rename_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_request_response()", URL="\ref Controller::handle_request_response()"];
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>cmd_rename_route [label="execute()", URL="\ref cmd_base::execute()"];
 *  cmd_rename_route=>base_db [label="db_rename_route()", URL="\ref base_db::db_rename_route()"];
 *  cmd_rename_route<<base_db;
 *  cmd_rename_route=>cmd_rename_route [label="response()", URL="\ref cmd_rename_route::response()"];
 *  Controller<<cmd_rename_route;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */

class cmd_rename_route extends cmd_base {

	private $new_route_name = ''; ///< string value returns new route name

	/**
	 * @brief Initializes the class with associated args and their defaults
	 * @details
	 * GLOBAL USED: string $_GET["sessionid"]
	 *  A string that contains the User Session ID
	 * GLOBAL USED: int $_GET["route_id"]
	 *  An integer that contains the Route ID to add the coord to
	 * GLOBAL USED: int $_GET["new_route_name"]
	 *  A string that contains a new route name to modify the route
	 */
	public function __construct() {
		$this->required_args = array('sessionid' => '', 'route_id' => 0, 'new_route_name' => '',);
	}

	/**
	 * @brief Builds up an xml response
	 * @return string XML response
	 */
	private function response() {

		$xml = new SimpleXMLElement("<methodResponse></methodResponse>");
		$pp = $xml->addChild('params');
		$p = $pp->addChild('param');
		$v = $p->addChild('value');

		$r = $v->addChild('string', htmlspecialchars($this->new_route_name));

		return $this->pp_xml($xml);
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  This function renames the route (given route_id) to the given route name
	 */
	public function execute($f_db, $f_auth) {
		$changed = $f_db->db_rename_route($this->required_args['route_id'], $this->required_args['new_route_name']);
		if ($changed) {
			$this->new_route_name = $this->required_args['new_route_name'];
		}
		return $this->response();
	}
}

/**
 * @ingroup wa
 * @brief This command class is used in an error case to display an error to the client
 * @sa cmd_base
 */
class cmd_error extends cmd_base {

	private $faultCode = -1; ///< Error code int
	private $faultString = ''; ///< Error description string

	/**
	 * @brief This function creates a xml response from an error code 
	 * @param[in] int $f_faultCode
	 *  An error code value
	 * @param[in] sting $f_faultString
	 *  An error code descriptive string
	 * @return string Full XML-RPC response to be used or printed by caller
	 * @details See the Web and Mobile ICD
	 */
	public function error($f_faultCode, $f_faultString) {
		$this->faultCode = $f_faultCode;
		$this->faultString = $f_faultString;
		$this->isComplete = true;
	}

	/**
	 * @copydoc cmd_base::execute
	 * @sa cmd_base::execute
	 * @details
	 *  Returns XML describing the fault
	 */
	public function execute($f_db, $f_auth) {
		return $this->get_error_output($this->faultCode, $this->faultString);
	}
}

/**
 * @ingroup wa
 * @brief The is a base view class/interface used to store view information in a standard way.
 * @details
 *  Different views are displayed by passing in the view as a "display" HTTP POST/GET variable.
 *  For example http://host/index.php?display=ACTION&arg1=ARG1
 *  However, it is a base abstract class.  Not used directly.
 *
 *  The constructor should be created unique for each implementing class for parsing the correct elements from the request.
 */
abstract class view_base {

	/**
	 * @brief Executes the working command and saves off the important info
	 * @param[in] object $f_db
	 *   The database object of type base_db
	 * @param[in] object $f_auth
	 *   The auth object of type auth
	 * @return string Full XML response to be used or printed by caller - see the Mobile and Web ICD
	 */
	abstract public function execute($f_db, $f_auth);

	/**
	 * @brief Get a display view page start
	 * @param[in] string $extra_head
	 *  (optional) Any extra html that should be inserted before the end of the head tag
	 * @param[in] string $onload
	 *  (optional) javascript onload function
	 * @return string HTML start body to be printed by caller
	 * @details
	 *  Docs: https://developers.google.com/maps/documentation/javascript/reference
	 */
	public function get_page_start($extra_head = '', $onload = '') {
		$html = '';
		// Google maps api
		$html .= '<!DOCTYPE html>' . "\n";
		$html .= '<html><head><title>GPS.Manager</title>' . "\n";
		//$html .= '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />' . "\n";
		$html .= get_stylesheet();
		$html .= '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTVZp29T_aBdCQbuEt3spvi4cqmHbA-wg&sensor=false"></script>' . "\n";
		$html .= $extra_head;
		$html .= '</head>' . "\n";
		$html .= '<body id="page_body" onload="' . $onload . '">' . "\n";
		$html .= '<div id="body_container">' . "\n\n";
		$html .= ' <div id="app_title">' . "\n";
		$html .= '  <H1><span>GPS.Manager</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //app_title
		return $html;
	}

	/**
	 * @brief Get a display view page end
	 * @return string HTML ending body to be printed by caller
	 */
	public function get_page_end() {
		$html = '';
		$html .= "\n\n" . '</div>' . "\n"; //body_container
		$html .= '<div id="extraDiv"><span></span></div>' . "\n"; //extra catchalls to add extra imagery if needed
		$html .= '</body>' . "\n";
		$html .= '</html>' . "\n";
		return $html;
	}

	/**
	 * @brief This generates the html for the menu bar
	 * @param[in] string $sid A page sessionid hash
	 * @param[in] string $back_link
	 *  (optional) If passed, shows a back button to the URL passed in (variables after the script name e.g. ?display=blah)
	 * @return string Returns an html string
	 */
	public function get_menu_bar($sid, $back_link = '') {

		$bar_html = '';
		$bar_html .= ' <div id="menu_bar">' . "\n";
		if ($back_link != '') {
			$bar_html .= '  <div id="menu_bar_button"><p class="button_text"><span><A HREF="' . $_SERVER['PHP_SELF'] . $back_link . '">Back</A></span></p></div>' . "\n";
		}
		$bar_html .= '  <div id="menu_bar_button"><p class="button_text"><span><A HREF="' . $_SERVER['PHP_SELF'] . '?display=show_location_recorder&sessionid=' . $sid . '">Location Recorder</A></span></p></div>' . "\n";
		$bar_html .= '  <div id="menu_bar_button"><p class="button_text"><span><A HREF="' . $_SERVER['PHP_SELF'] . '?display=settings&sessionid=' . $sid . '">Configure Settings</A></span></p></div>' . "\n";
		$bar_html .= '  <div id="menu_bar_button"><p class="button_text"><span><A HREF="' . $_SERVER['PHP_SELF'] . '?display=logout&sessionid=' . $sid . '">Logout</A></span></p></div>' . "\n";
		$bar_html .= ' </div>' . "\n";
		return $bar_html;
	}

	/**
	 * @brief Redirects the page
	 * @param[in] string $string
	 *  (optional) url variables after the page name (would start with a question mark)
	 */
	public function reload_page_with($string='') {
		header("Location: " . ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "$string");
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to show a login prompt and authenticate a user
 * @sa view_base
 * @details
 *  This is login page view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_login;
 *  |||;
 *  main box view_login [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_login [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Check for initial install option"];
 *  view_login=>Auth [label="authenticate(default_username,default_password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  view_login<<Auth;
 *  --- [label="Try a passed in username and password"];
 *  view_login=>Auth [label="authenticate(username,password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  view_login<<Auth;
 *  view_login=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  view_login<<Auth;
 *  --- [label="Display username and password prompt"];
 *  view_login=>view_login [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_login=>view_login [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_login;
 *  main<<Controller [label="echo()"];
 * @endmsc
 *  This is the login post (username and password provided) sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_login;
 *  |||;
 *  main box view_login [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_login [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Check for initial install option"];
 *  view_login=>Auth [label="authenticate(default_username,default_password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  view_login<<Auth;
 *  --- [label="Try a passed in username and password"];
 *  view_login=>Auth [label="authenticate(username,password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_get_session_for_user()", URL="\ref base_db::db_get_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_set_session_for_user()", URL="\ref base_db::db_set_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  view_login<<Auth;
 *  view_login=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  view_login<<Auth;
 *  --- [label="redirect to welcome page"];
 *  view_login=>view_login [label="reload_page_with()", URL="\ref view_base::reload_page_with()"];
 * @endmsc
 *  This is the default login (one-time installation) sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_login;
 *  |||;
 *  main box view_login [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_login [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Check for initial install option"];
 *  view_login=>Auth [label="authenticate(default_username,default_password)", URL="\ref Auth::authenticate()"];
 *  Auth=>base_db [label="db_check_user_password_match()", URL="\ref base_db::db_check_user_password_match()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_get_session_for_user()", URL="\ref base_db::db_get_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_set_session_for_user()", URL="\ref base_db::db_set_session_for_user()"];
 *  Auth<<base_db;
 *  Auth=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  view_login<<Auth;
 *  --- [label="Display link to authenticated settings page"];
 *  view_login=>view_login [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_login=>view_login [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_login;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_login extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function shows a login page to prompt the user for credentials
	 */
	public function execute($f_db, $f_auth) {

/// @todo website session id should be different
/// @todo password recovery info (hint) or create file to recover or?
/// @todo login page timeout after 3 attempts
/// @todo login page timeout session after three days

		// If the default credentials are still set, force a configure
		if ($f_auth->authenticate($GLOBALS['gpswa_defaults']['db_username'], $GLOBALS['gpswa_defaults']['db_password'])) {
			$inst_html = '';
			$inst_html .= ' <div id="page_header">' . "\n";
			$inst_html .= '  <H1><span>Install</span></H1>' . "\n";
			$inst_html .= ' </div>' . "\n"; //page_header

			$inst_html .= ' <div id="page_content">' . "\n";
			$inst_html .= '  Default user/password are still set, continue to the setting screen...';
			$inst_html .= '  <A HREF="' . $_SERVER['PHP_SELF'] . '?display=settings&sessionid=' . $f_auth->authenticatedSession . '">Configure Settings</A>';
			$inst_html .= ' </div>' . "\n"; //page_content
			return $this->get_page_start() . $inst_html . $this->get_page_end();
		}

		$html = '';

		$html .= ' <div id="page_header">' . "\n";
		$html .= '  <H1><span>Login</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //page_header

		// see if we are trying to login
		$login_error = '';
		if (isset($_POST['username']) && isset($_POST['password'])) {
			$f_auth->authenticate($_POST['username'], $_POST['password']);
			if ($f_auth->isAuthorized()) {
				//redirect to the welcome screen on success
				$this->reload_page_with('?display=welcome&sessionid=' . $f_auth->authenticatedSession);
				exit;
			} else {
				$login_error .= '<div id="login_error"><span>WRONG STUFF!</span></div>' . "\n";
			}
		}

		$html .= ' <div id="no_menu"></div>';
		$html .= ' <div id="page_content">' . "\n";
		$html .= $login_error;
		$html .= '  <div id="login_form">' . "\n";
		$html .= '   <form name="creds" method="post" action="' . $_SERVER['PHP_SELF'] . '?display=login">';
		$html .= '   <p class="username_field"><span>' . "\n";
		$html .= '   Username: <input type="text" name="username" value=""/><BR>';
		$html .= '   </span></p>' . "\n";
		$html .= '   <p class="password_field"><span>' . "\n";
		$html .= '   Password: <input type="password" name="password" value=""/><BR>';
		$html .= '   </span></p>' . "\n";
		$html .= '   <input type="submit" value="Authenticate"/></form>';
		$html .= '  </div>' . "\n"; //login_form
		$html .= ' </div>' . "\n"; //page_content

		return $this->get_page_start('', 'document.creds.username.focus();') . $html . $this->get_page_end();
	}

}

/**
 * @ingroup wa
 * @brief This view class is used to deauthenticate a user session and show a logout screen
 * @sa view_base
 * @details
 *  This is the logout page view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_logout;
 *  |||;
 *  main box view_logout [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_logout [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="deauthenticate in database"];
 *  view_logout=>Auth [label="logout()", URL="\ref Auth::logout()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  Auth=>base_db [label="db_set_session_for_user()", URL="\ref base_db::db_set_session_for_user()"];
 *  Auth<<base_db;
 *  view_logout<<Auth;
 *  --- [label="Display a link to log back in"];
 *  view_logout=>view_logout [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_logout=>view_logout [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_logout;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_logout extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function deauthenticates a user
	 */
	public function execute($f_db, $f_auth) {
		$f_auth->logout();
		$html = '';
		$html .= ' <div id="no_menu"></div>';
		$html .= ' <div id="page_content">' . "\n";
		$html .= '  You are now logged out, <A HREF="' . $_SERVER['PHP_SELF'] . '">Log back in</A>' . "\n"; 
		$html .= ' </div>' . "\n"; //page_content
		return $this->get_page_start() . $html . $this->get_page_end();
	}

}

/**
 * @ingroup wa
 * @brief This view class is used to show a welcome page containing route info
 * @sa view_base
 * @details
 *  This is the welcome page view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_welcome;
 *  |||;
 *  main box view_welcome [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_welcome [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Display a menu and list of routes"];
 *  view_welcome=>view_welcome [label="get_menu_bar()", URL="\ref view_base::get_menu_bar()"];
 *  view_welcome=>base_db [label="db_get_route_list()", URL="\ref base_db::db_get_route_list()"];
 *  view_welcome<<base_db;
 *  view_welcome=>view_welcome [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_welcome=>view_welcome [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_welcome;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_welcome extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function displays a welcome page that shows a route list and menu
	 */
	public function execute($f_db, $f_auth) {

		$html = '';
		$change_message = '';
		if (isset($_GET['msg']) && $_GET['msg'] != '') {
			$change_message = $_GET['msg'];
		}

		// Header
		$html .= ' <div id="page_header">' . "\n";
		$html .= '  <H1><span>Routes</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //page_header

		// Print the menu bar
		$html .= $this->get_menu_bar($f_auth->authenticatedSession);

		$html .= ' <div id="page_content">' . "\n";
		// show any change message
		$html .= '<B>' . $change_message . '</B><BR>';

		// Show route list
		foreach($f_db->db_get_route_list() as $r_row) {
			$html .= '[<A HREF="' . $_SERVER['PHP_SELF'] . '?display=delete_route&route_id=' . $r_row['route_id'] . '&sessionid=' . $f_auth->authenticatedSession . '" onclick="return confirm(\'Are you sure you want to delete this route?\')">delete</A>] ';
			$html .= '[<A HREF="' . $_SERVER['PHP_SELF'] . '?display=rename_route&route_id=' . $r_row['route_id'] . '&sessionid=' . $f_auth->authenticatedSession . '" onclick="var ret = prompt(\'New name:\'); this.href = (this.href + \'&new_route_name=\' + ret);">rename</A>] ';
			$html .= '<A HREF="' . $_SERVER['PHP_SELF'] . '?display=show_route&route_id=' . $r_row['route_id'] . '&sessionid=' . $f_auth->authenticatedSession . '">' . $r_row['route_id'] . ') ' . $r_row['route_name'] . '</A> ';
			$html .= '<BR>' . "\n";
		}
		$html .= '  <HR>' . "\n";
		$html .= '  [<A HREF="' . $_SERVER['PHP_SELF'] . '?action=get_route_list&sessionid=' . $f_auth->authenticatedSession . '">Export XML</A>]' . "\n";
		$html .= ' </div>' . "\n"; //page_content

		return $this->get_page_start() . $html . $this->get_page_end();
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to show a list of coords and a map
 * @sa view_base
 * @details
 *  This is the route map view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_show_route;
 *  |||;
 *  main box view_show_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_show_route [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Display a menu and route map"];
 *  view_show_route=>view_show_route [label="get_menu_bar()", URL="\ref view_base::get_menu_bar()"];
 *  view_show_route=>base_db [label="db_get_route()", URL="\ref base_db::db_get_route()"];
 *  view_show_route<<base_db;
 *  view_show_route=>view_show_route [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_show_route=>view_show_route [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_show_route;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_show_route extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function displays the coords for a route on a map and the menu
	 */
	public function execute($f_db, $f_auth) {

		$html = '';

		// Header
		$html .= ' <div id="page_header">' . "\n";
		$html .= '  <H1><span>Route ' . $_GET['route_id'] . '</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //page_header

		// Print the menu bar
		$html .= $this->get_menu_bar($f_auth->authenticatedSession, '?display=welcome&sessionid=' . $f_auth->authenticatedSession);

		$html .= ' <div id="page_content">' . "\n";
		// show a map
		$l_route = $f_db->db_get_route($_GET['route_id']);
		if (empty($l_route)) {
			$html .= 'Route has no data, ';
			$html .= '<A HREF="' . $_SERVER['PHP_SELF'] . '?display=delete_route&route_id=' . $_GET['route_id'] . '&sessionid=' . $f_auth->authenticatedSession . '" onclick="return confirm(\'Are you sure you want to delete this route?\')">delete it?</A>';
		} else {
			$html .= '  <div id="map_canvas"></div>' . "\n";

			// Show route list
			/*
			$html .= '  <HR>' . "\n";
			foreach($l_route as $r_row) {
				$split_coord = explode(',', $r_row['coord_data']);
				$html .= '  ' . $r_row['coord_seq'] . ') (' . $r_row['coord_date'] . ') Lat: ' . $split_coord[0] . ', Lon: ' . $split_coord[1] . ' @' . $r_row['coord_accuracy'] . '<BR>' . "\n";
			}
			*/
		}
		$html .= '  <HR>' . "\n";
		$html .= '  [<A HREF="' . $_SERVER['PHP_SELF'] . '?action=get_route&route_id=' . $_GET['route_id'] . '&sessionid=' . $f_auth->authenticatedSession . '">Export XML</A>]' . "\n";
		$html .= ' </div>' . "\n"; //page_content

		return $this->get_page_start(get_gmap_js($l_route), 'initialize()') . $html . $this->get_page_end();
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to delete a route given a route id
 * @sa view_base
 * @details
 *  This is the delete route view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_delete_route;
 *  |||;
 *  main box view_delete_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_delete_route [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Remove from the database"];
 *  view_delete_route=>base_db [label="db_delete_route()", URL="\ref base_db::db_delete_route()"];
 *  view_delete_route<<base_db;
 *  view_delete_route=>view_delete_route [label="reload_page_with()", URL="\ref view_base::reload_page_with()"];
 * @endmsc
 */
class view_delete_route extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function deletes a route and redirects to the welcome page
	 */
	public function execute($f_db, $f_auth) {
		$msg = 'Failed to delete given route';
		if (isset($_GET['route_id']) && $_GET['route_id'] != '') {
			$f_db->db_delete_route($_GET['route_id']);
			$msg = 'Route ' . $_GET['route_id'] . ' successfully removed.';
		}
		$this->reload_page_with('?display=welcome&msg=' . $msg . '&sessionid=' . $f_auth->authenticatedSession);
		exit;
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to rename a route given a route id and new name
 * @sa view_base
 * @details
 *  This is the rename route view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_rename_route;
 *  |||;
 *  main box view_rename_route [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_rename_route [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Rename in the database"];
 *  view_rename_route=>base_db [label="db_rename_route()", URL="\ref base_db::db_rename_route()"];
 *  view_rename_route<<base_db;
 *  view_rename_route=>view_rename_route [label="reload_page_with()", URL="\ref view_base::reload_page_with()"];
 * @endmsc
 */
class view_rename_route extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function renames a route and redirects to the welcome page
	 */
	public function execute($f_db, $f_auth) {
		$msg = 'Failed to rename given route';
		if (isset($_GET['route_id']) && $_GET['route_id'] != ''
		&& isset($_GET['new_route_name']) && $_GET['new_route_name'] != ''
		&& $_GET['new_route_name'] != 'null') {
			$f_db->db_rename_route($_GET['route_id'], $_GET['new_route_name']);
			$msg = 'Route ' . $_GET['route_id'] . ' name changed to ' . $_GET['new_route_name'] . ' successfully.';
		}
		$this->reload_page_with('?display=welcome&msg=' . $msg . '&sessionid=' . $f_auth->authenticatedSession);
		exit;
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to show a web based coord sender
 * @sa view_base
 * @details
 *  This is the location recorder view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_show_location_recorder;
 *  |||;
 *  main box view_show_location_recorder [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_show_location_recorder [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Display menu and recorder controls"];
 *  view_show_location_recorder=>view_show_location_recorder [label="get_menu_bar()", URL="\ref view_base::get_menu_bar()"];
 *  view_show_location_recorder=>view_show_location_recorder [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_show_location_recorder=>view_show_location_recorder [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_show_location_recorder;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_show_location_recorder extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function shows the location recorder which sends coords to the web app
	 */
	public function execute($f_db, $f_auth) {

		$html = '';

		// Header
		$html .= ' <div id="page_header">' . "\n";
		$html .= '  <H1><span>Location Recorder</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //page_header

		// Print the menu bar
		$html .= $this->get_menu_bar($f_auth->authenticatedSession, '?display=welcome&sessionid=' . $f_auth->authenticatedSession);

		$html .= ' <div id="page_content">' . "\n";
		$html .= 'New route name: <input id="route_name" type="text" value="AutoWebRoute"/><BR>';
		$html .= '<button id="start_stop_button" onclick="start_stop_button_click(\'' . $f_auth->authenticatedSession . '\');">Start</button>';
		$html .= '<input id="start_stop_restrict" type="checkbox"/>' . "(Don't restrict innaccurate or duplicate posts)" . "\n";
		$html .= '<input id="start_stop_state" type="hidden" value="stopped"/>';
		$html .= '<input id="start_stop_last_pos" type="hidden" value=""/>' . "\n";
		$html .= '<HR>' . "\n";
		$html .= '<div id="msg_box"></div>';
		$html .= ' </div>' . "\n"; //page_content

		return $this->get_page_start(get_position_js()) . $html . $this->get_page_end();
	}
}

/**
 * @ingroup wa
 * @brief This view class is used to display a settings form and change settings
 * @sa view_base
 * @details
 *  This is the location recorder view sequence:
 * @msc
 *  main,Controller,base_db,Auth,view_settings;
 *  |||;
 *  main box view_settings [label="Controller Construction Sequence", URL="\ref Controller"];
 *  main=>Controller [label="handle_display_response()", URL="\ref Controller::handle_display_response()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Controller<<Auth;
 *  Controller=>Auth [label="isAuthorized()", URL="\ref Auth::isAuthorized()"];
 *  Controller<<Auth;
 *  Controller=>view_settings [label="execute()", URL="\ref view_base::execute()"];
 *  --- [label="Try to use posted values"];
 *  view_settings=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  view_settings<<base_db;
 *  view_settings=>base_db [label="db_set_new_user_password()", URL="\ref base_db::db_set_new_user_password()"];
 *  view_settings<<base_db;
 *  --- [label="Display menu and settings form"];
 *  view_settings=>view_settings [label="get_menu_bar()", URL="\ref view_base::get_menu_bar()"];
 *  view_settings=>view_settings [label="get_page_start()", URL="\ref view_base::get_page_start()"];
 *  view_settings=>view_settings [label="get_page_end()", URL="\ref view_base::get_page_end()"];
 *  Controller<<view_settings;
 *  main<<Controller [label="echo()"];
 * @endmsc
 */
class view_settings extends view_base {

	/**
	 * @copydoc view_base::execute
	 * @sa view_base::execute
	 * @details
	 *  This function shows the settings screen to change username and password etc
	 */
	public function execute($f_db, $f_auth) {

		$change_message = '';

		// If a new username is set, lets modify the database with it
		$acting_username = $f_db->db_get_user_for_session($_GET['sessionid']);
		$new_username = false;
		if (isset($_POST['new_username']) && $_POST['new_username'] != '' && $_POST['new_username'] != $acting_username) {
			$new_username = true;
			$acting_username = $_POST['new_username'];
		}

		$new_password = '';
		if (isset($_POST['new_password']) && $_POST['new_password'] != '') {
			// new password is set, lets use new password and new username
			$f_db->db_set_new_user_password($_GET['sessionid'], $acting_username, $_POST['new_password']);
			$change_message = 'Password has been changed for user: ' . $acting_username . '<BR>';
		} elseif ($new_username) {
			// no new password, but lets change the new username
			$f_db->db_set_new_user_password($_GET['sessionid'], $acting_username);
			$change_message = 'Username has been changed to: ' . $acting_username . '<BR>';
		}


		$html = '';

		// Header
		$html .= ' <div id="page_header">' . "\n";
		$html .= '  <H1><span>Settings</span></H1>' . "\n";
		$html .= ' </div>' . "\n"; //page_header

		// Print the menu bar
		$html .= $this->get_menu_bar($f_auth->authenticatedSession, '?display=welcome&sessionid=' . $f_auth->authenticatedSession);

		$html .= ' <div id="page_content">' . "\n";
		// show any change message
		$html .= '<B>' . $change_message . '</B><BR>';

		/// @todo multiple users
		// Show the settings form
		$html .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?display=settings&sessionid=' . $f_auth->authenticatedSession . '">';
		$html .= 'Username:<BR>';
		$html .= '<input type="text" name="new_username" value="' . $acting_username . '"/>';
		$html .= '<BR>';
		$html .= 'Password:<BR>';
		//$html .= '<input type="password" name="new_password" value="' . $GLOBALS['gpswa_defaults']['db_password'] . '" onclick="if (this.value == \'' . $GLOBALS['gpswa_defaults']['db_password'] . '\') { this.value = \'\';}" />';
		$html .= '<input type="password" name="new_password" value=""/>';

		$html .= '<BR>';
		$html .= '<HR>';
		$html .= '<input type="submit" value="Make Changes"/>';
		$html .= '</form>';
		$html .= ' </div>' . "\n"; //page_content

		return $this->get_page_start() . $html . $this->get_page_end();
	}
}

/**
 * @ingroup wa
 * @brief The controller class manages session, db, and action info
 * @details
 *  A new controller object will initially create a database and authentication object
 *  Then, it tries to validate any sessionid passed and stores for later use
 *  This is the Controller Construction Sequence:
 * @msc
 *  main,Controller,base_db,Auth,cmd_base;
 *  main=>Controller [label="__construct()", URL="\ref Controller::__construct()"];
 *  Controller=>base_db [label="__construct()"];
 *  Controller=>Auth [label="__construct()", URL="\ref Auth::__construct()"];
 *  Controller=>>cmd_base [label="__construct()"];
 *  Controller=>Auth [label="validate_session()", URL="\ref Auth::validate_session()"];
 *  Auth=>base_db [label="db_get_user_for_session()", URL="\ref base_db::db_get_user_for_session()"];
 *  Auth<<base_db;
 *  Controller<<Auth;
 *  main<<Controller;
 * @endmsc
 */
class Controller {

	public $action_obj = NULL; ///< The associated action object with $action containing args and methods
	public $auth_obj = NULL; ///< Authentication mechanism
	public $db = NULL; ///< The database interface for making queries and storing position and user data

	/**
	 * @brief Initializes the database and commands
	 * @details
	 * GLOBAL USED: string $_GET["action"]
	 *  (optional) A string that contains the action command.  Can also use $_POST
	 */
	public function __construct() {

		// Require a configuration file
		if (!file_exists('conf.' . basename(__FILE__))) {
			die("FATAL ERROR: could not find the web application configuration, please check the conf." . basename(__FILE__) . " file exists in the same directory as this file");
		}
		require 'conf.' . basename(__FILE__);
		if (!isset($GLOBALS['database_config']['configured']) || $GLOBALS['database_config']['configured'] != true) {
			die("FATAL ERROR: could not get the web application configuration, please check the conf." . basename(__FILE__) . " file exists in the same directory as this file");
		}
		if (!isset($GLOBALS['database_config']['type'])) {
			die("FATAL ERROR: configuration file says it has been configured but database type has not been defined");
		}

		//Always at least obtain a database object
		switch($GLOBALS['database_config']['type']) {
			case 'xml':
				$this->db = new xml_db($GLOBALS['database_config']['config_options']['xml_filename']);
				break;
			case 'sqlite':
				$this->db = new sqlite_db($GLOBALS['database_config']['config_options']['sqlite_filename']);
				break;
			case 'mysql':
				$this->db = new mysql_db(
					$GLOBALS['database_config']['config_options']['mysql_host'], 
					$GLOBALS['database_config']['config_options']['mysql_name'], 
					$GLOBALS['database_config']['config_options']['mysql_user'], 
					$GLOBALS['database_config']['config_options']['mysql_pass']
				);
				break;
			default:
				die("FATAL ERROR: configuration file says it has been configured but database type has not been defined");
				break;
		}

		// Once we have a db we can create an auth
		$this->auth_obj = new Auth($this->db);

		// Create the action class
		$action = '';
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
		} elseif (isset($_POST['action'])) {
			$action = $_POST['action'];
		}

		// dont set an action object if this is not an action
		if ($action != '') {
			$newCmdClassName = "cmd_" . $action;
			if(class_exists($newCmdClassName)) {
				$this->action_obj = new $newCmdClassName();
				$this->action_obj->parse();

				// always try to validate a session variable out of the action
				if (isset($this->action_obj->required_args['sessionid']) && $this->action_obj->required_args['sessionid'] != '') {
					$this->auth_obj->validate_session($this->action_obj->required_args['sessionid']);
				}
			} else {
				$this->action_obj = new cmd_error();
				$this->action_obj->error(-1, 'Action not understood: ' . $action);
			}
		}
	}

	/**
	 * @brief Destructor to delete database and action objects
	 */
	public function __destruct() {
		unset($this->action_obj);
		unset($this->auth_obj);
		unset($this->db);
	}

	/**
	 * @brief This function just passes the buck to a cmd function based on the action type
	 * @return string Full XML-RPC response to be used or printed by caller
	 * @details See the Web and Mobile ICD
	 */
	public function handle_request_response() {


		// If we don't have an action command object then we cant continue (we shouldn't get here since we check ahead of time)
		if (!isset($this->action_obj)) {
			$this->action_obj = new cmd_error();
			$this->action_obj->error(-1, 'Action not passed in or action not handled');
		}

		// see if action is ready to output an error
		if (get_class($this->action_obj) == 'cmd_error') {
			return $this->action_obj->execute($this->db, $this->auth_obj);
		}

		// If we are not authenticated and not trying to log in
		if (!$this->auth_obj->isAuthorized() && get_class($this->action_obj) != 'cmd_user_login') {
			// overwrite the current action object with an error
			$this->action_obj = new cmd_error();
			$this->action_obj->error(-2, 'Authentication Failure, bad session');
			return $this->action_obj->execute($this->db, $this->auth_obj);
		}

		// If we are missing required inputs
		if (!$this->action_obj->isComplete) {
			// overwrite the current action object with an error
			$reason = 'Required inputs missing, required fields are: ' . implode(', ', array_keys($this->action_obj->required_args));
			$this->action_obj = new cmd_error();
			$this->action_obj->error(-1, $reason);
			return $this->action_obj->execute($this->db, $this->auth_obj);
		}

		return $this->action_obj->execute($this->db, $this->auth_obj);
	}

	/**
	 * @brief This function just passes the buck to a callback function based on the display page type
	 * @return string HTML response to be used or printed by caller
	 */
	public function handle_display_response() {

		// authenticate for a get session variable
		if (isset($_GET['sessionid'])) {
			$this->auth_obj->validate_session($_GET['sessionid']);
		}

		// default to show login screen and see if we requested a different page
		$display = 'login';
		if (isset($_GET['display'])) {
			$display = $_GET['display'];
		}

		// If we are not authenticated and not trying to log in then send to login view
		if(!$this->auth_obj->isAuthorized() && $display != 'login') {
			$display = 'login';
		}

		if ($display != '') {
			$newViewClassName = "view_" . $display;
			if(class_exists($newViewClassName)) {
				$view_obj = new $newViewClassName();
				return $view_obj->execute($this->db, $this->auth_obj);
			} else {
				die('FATAL ERROR: Display of "' . $display . '" not handled');
			}
		}
	}
}

// ********* MAIN **********

/// @todo do some fancy debug thing instead
// Check for a debug flag so we can see errors on the screen
if (isset($_GET['DEBUG'])) {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 'On');
}

$controller = new Controller();

// if we are trying to use the ICD API
if (isset($controller->action_obj)) {
	// we will be outputting xml with an action signaled
	header('Content-type: text/xml');
	echo $controller->handle_request_response();
} else {
	echo $controller->handle_display_response();
}

?>

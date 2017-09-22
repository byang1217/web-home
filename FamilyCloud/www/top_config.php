<?php
/* Database config */
/* username, password and database are all named "FamilyCloud" */

date_default_timezone_set('Asia/Shanghai');

#define("TOPCFG_DB_DSN", '');
#define("TOPCFG_DB_HOSTNAME", 'localhost');
#define("TOPCFG_DB_DATABASE", 'FamilyCloud');
#define("TOPCFG_DB_DRV", 'mysqli');

define("TOPCFG_DB_DSN", '');
#define("TOPCFG_DB_DSN", '../data/FamilyCloud.db');
define("TOPCFG_DB_HOSTNAME", '');
#define("TOPCFG_DB_HOSTNAME", 'sqlite: ../data/FamilyCloud.db');
define("TOPCFG_DB_DATABASE", '../data/FamilyCloud.db');
define("TOPCFG_DB_DRV", 'sqlite3');

/* network setup */
define("NETWORK_TIMEOUT", 5000); /* ajax will use it. pls change this setting based on your network condition */

/* UI config */
define("LOAD_NUM_ONCE", 4);
define("LOAD_NUM_ONCE_SMALL", 8);
define("THUMBNAIL_SIZE", 160);
define("FLUENT_SIZE", 720);

/* folder config */
function __path_to_dir($path)
{
	$path = preg_replace('/[\\\\\\/]$/', '', $path);
	return preg_replace('/[\\\\\\/][^\\\\\\/]+$/', '', $path);
}
//$top_dir = @file_get_contents('top_dir');
$top_dir = __path_to_dir(__path_to_dir(__FILE__));

define("TOPCFG_DATA_DIR", sprintf("%s/%s", $top_dir, "data"));
define("TOPCFG_DB_FILE", sprintf("%s/%s", TOPCFG_DATA_DIR, "database.db"));
define("TOPCFG_PHOTOS_DIR", sprintf("%s/%s", TOPCFG_DATA_DIR, "photos"));
define("TOPCFG_PHOTOS_DIR_ORIGINAL", sprintf("%s/%s", TOPCFG_PHOTOS_DIR, "Original"));
define("TOPCFG_PHOTOS_DIR_HD", sprintf("%s/%s", TOPCFG_PHOTOS_DIR, "HD"));
define("TOPCFG_PHOTOS_DIR_FLUENT", sprintf("%s/%s", TOPCFG_PHOTOS_DIR, "Fluent"));
define("TOPCFG_PHOTOS_DIR_THUMB", sprintf("%s/%s", TOPCFG_PHOTOS_DIR, "Thumb"));
define("TOPCFG_TMP_DIR", sprintf("%s/%s", $top_dir, "tmp"));
define("TOPCFG_IS_FIST_SETUP_FILE", sprintf("%s/%s", $top_dir, "first_setup"));

/* common functions */
function pr_info($msg)
{
	log_message('info', $msg);
}
function pr_debug($msg)
{
	log_message('debug', $msg);
}
function pr_err($msg)
{
	log_message('error', $msg);
}

/*

function prof_flag($str)
{
	global $prof_timing, $prof_names;
	$prof_timing[] = microtime(true);
	$prof_names[] = $str;
}

function prof_print()
{
	global $prof_timing, $prof_names;
	$size = count($prof_timing);
	for($i=0;$i<$size - 1; $i++)
	{
		error_log(sprintf("%s --- %f\n", $prof_names[$i], $prof_timing[$i+1]-$prof_timing[$i]), 3, "php.trace");
	}
}

*/


/* early init */
define("TOPCFG_RELEASE_VERSION", false);

//		$myhome_session_dir = "session/";
//		$timeout = 31*24*3600;

//		ini_set('session.cookie_lifetime', $timeout);
//		ini_set('session.gc_maxlifetime', $timeout);
//		ini_set('session.save_path', $myhome_session_dir);
//		date_default_timezone_set('Asia/Shanghai');





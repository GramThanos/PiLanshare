<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Config
 */

// App Info
define('APP_NAME', 'PiLanshare WebUI');
define('APP_DESCRIPTION', 'Monitor your Pi\'s Lanshare');
define('APP_VERSION', 'v0.2-beta');
define('APP_WEBSITE', 'https://github.com/GramThanos/PiLanshare');
define('APP_DEBUG', true);
define('APP_ROOT_PATH', dirname(__DIR__));
define('ACTIONS_SCRIPT_PATH', APP_ROOT_PATH . '/scripts/actions.sh');

// Login type
define('APP_LOGIN', true);
define('APP_LOGIN_TYPE', 'LIST');
define('APP_LOGIN_TYPE_LIST', array('admin' => '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918'));

// Session Info
define('APP_SESSION_NAME', 'PISHARE_SESSION');
define('APP_SESSION_LIFETIME', 2 * 60 * 60);
define('APP_SESSION_LIFETIME_REMEMBER', 6 * 60 * 60);
define('APP_SESSION_AUTO_UPDATE', true);
define('APP_SESSION_AUTO_UPDATE_INTERVAL', 60);
define('APP_SESSION_AUTO_REGENERATE', true);
define('APP_SESSION_AUTO_REGENERATE_INTERVAL', 60);

// For debugging
if (APP_DEBUG) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

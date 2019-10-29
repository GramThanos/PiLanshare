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
define('APP_VERSION', 'v0.3-beta');
define('APP_WEBSITE', 'https://github.com/GramThanos/PiLanshare');
define('APP_DEBUG', true);
define('APP_ROOT_PATH', dirname(__DIR__));

// Login type
define('APP_LOGIN', true);
define('APP_LOGIN_TYPE', 'LIST');
define('APP_LOGIN_TYPE_LIST', array('admin' => 'sha256|8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918'));

// Session Info
define('APP_SESSION_NAME', 'PISHARE_SESSION');
define('APP_SESSION_LIFETIME', 2 * 60 * 60);
define('APP_SESSION_LIFETIME_REMEMBER', 2 * 24 * 60 * 60);
define('APP_SESSION_AUTO_UPDATE', true);
define('APP_SESSION_AUTO_UPDATE_INTERVAL', 60);
define('APP_SESSION_AUTO_REGENERATE', true);
define('APP_SESSION_AUTO_REGENERATE_INTERVAL', 60);

// API Configuration
define('API_USE_CSRF_TOKEN', true); // Requires APP_LOGIN true
define('API_DAEMON_SOCKET', '/etc/pilanshare/daemon.sock');

// IEEE OUI text file
// Download file `wget http://standards-oui.ieee.org/oui/oui.txt`
// or install package `sudo apt install arp-scan -y`
define('IEEE_OUI_TXT', '/var/www/html/pilanshare/includes/oui.txt');
//define('IEEE_OUI_TXT', '/usr/share/ieee-data/oui.txt');


// For debugging
if (APP_DEBUG) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

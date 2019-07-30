<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Session Management
 */

// Session name change
session_name(APP_SESSION_NAME);
// Max session expiration
ini_set('session.gc-maxlifetime', APP_SESSION_LIFETIME_REMEMBER);
// Session lifetime and path
session_set_cookie_params(APP_SESSION_LIFETIME, '/');
// Start session
session_start();


function session_isLoggedIn() {
	if (isset($_SESSION['id']) && $_SESSION['id'] > 0) {
		// If auto regenerate is enabled
		if (APP_SESSION_AUTO_REGENERATE && time() - $_SESSION['session_lifetime_last_regenerate'] > APP_SESSION_AUTO_REGENERATE_INTERVAL) {
			// Regenerate session expiration
			session_regenerate_id(true);
			$_SESSION['session_lifetime_last_regenerate'] = time();
		}
		// If auto update is enabled
		if (APP_SESSION_AUTO_UPDATE && time() - $_SESSION['session_lifetime_last_update'] > APP_SESSION_AUTO_UPDATE_INTERVAL) {
			// Update session expiration
			setcookie(session_name(), session_id(), time() + $_SESSION['session_lifetime'], '/');
			$_SESSION['session_lifetime_last_update'] = time();
		}
		return true;
	}
	return false;
}

function session_logIn($user, $remember = false) {
	if (APP_SESSION_AUTO_REGENERATE) session_regenerate_id(true);
	$_SESSION['id'] = $user['id'];
	$_SESSION['username'] = $user['username'];
	$_SESSION['session_lifetime'] = ($remember) ? APP_SESSION_LIFETIME_REMEMBER : APP_SESSION_LIFETIME;
	$_SESSION['session_lifetime_last_update'] = time();
	$_SESSION['session_lifetime_last_regenerate'] = time();
	setcookie(session_name(), session_id(), time() + $_SESSION['session_lifetime'], '/');
}

function session_logOut() {
	unset($_SESSION['id']);
	unset($_SESSION['username']);
	session_unset();
	session_destroy();
}


// SETTERS - GETTERS

function session_get_userId() {
	return $_SESSION['id'];
}

function session_get_userUsername() {
	return $_SESSION['username'];
}

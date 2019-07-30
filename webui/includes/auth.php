<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Authenticate User
 */

// Check user authorization
function authenticate($username, $password) {
	switch (APP_LOGIN_TYPE) {
		case 'LIST': return authenticate_list($username, $password);
		default: return false;
	}
}

function authenticate_list($username, $password) {

	// For each user on the list
	foreach (APP_LOGIN_TYPE_LIST as $_username => $_password_hash) {
		if (
			// Match username
			$_username === $username &&
			// Match pass hashes
			$_password_hash === hash('sha256', $password)
		) {
			return array(
				'id' => 1,
				'username' => $_username
			);
		}
	}

	// Failed
	return false;
}
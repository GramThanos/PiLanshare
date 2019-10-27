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
		// Match username
		if ($_username === $username) {
			$info = explode('|', $_password_hash);
			// Match pass hashes
			if ($info[1] === hash($info[0], $password)) {
				return array(
					'id' => 1,
					'username' => $_username
				);
			}
		}
	}

	// Failed
	return false;
}

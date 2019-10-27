<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Daemon Socket
 */

// Socket location
define('API_DAEMON_SOCKET_URI', 'unix://' . realpath(API_DAEMON_SOCKET));

// Communicate using socket
function socket_request($request) {
	# Connect on socket
	$fp = @stream_socket_client(API_DAEMON_SOCKET_URI, $errno, $errstr, 30);
	
	# If connection failed
	if (!$fp) {
		$response = NULL;
	}

	# Connection successful
	else {
		# Send command
		fwrite($fp, json_encode($request));
		# Get response
		$json = '';
		while (!feof($fp)) {
			$json .= fgets($fp, 1024);
		}
		fclose($fp);

		# Parse response
		$response = json_decode($json, true);
	}
	
	return $response;
}

// Send an action request
function socket_action($action, $sub_action=false) {
	if ($sub_action) {
		return socket_request(array(
			'action' => $action,
			'sub-action' => $sub_action
		));
	}
	else {
		return socket_request(array(
			'action' => $action
		));
	}
}

// Send a command request
function socket_command($command) {
	return socket_request(array(
		'action' => 'command',
		'command' => $command
	));
}

//var_dump(socket_action('version'));
//var_dump(socket_action('test'));
//var_dump(socket_action('interfaces'));
//var_dump(socket_action('interfaces-stats'));
//var_dump(socket_action('arp'));
//var_dump(socket_action('dnsmasq_leases'));
//var_dump(request(array('action' => 'system', 'sub-action' => 'reboot')));
//var_dump(request(array('action' => 'system', 'sub-action' => 'dnsmasq-restart')));
//var_dump(request(array('action' => 'command', 'command' => 'ifconfig')));
//var_dump(request(array('action' => 'command', 'command' => 'iwconfig')));

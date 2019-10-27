<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI
 */

	// Info
	include(dirname(__FILE__) . '/includes/config.php');
	// Tools
	include(dirname(__FILE__) . '/includes/tools.php');
	
	// Response will be json
	header('Content-Type: application/json');

	// If login is enabled
	if (APP_LOGIN) {
		// Session
		include(dirname(__FILE__).'/includes/session.php');
		// If not logged in
		if (!session_isLoggedIn()) {
			die(json_encode(array('error' => 'Unauthorized access')));
		}
	
		// Use secure token
		if (API_USE_CSRF_TOKEN) {
			// Include the PHP-CSRF library
			include(dirname(__FILE__) . '/includes/php-csrf.php');
			$csrf = new CSRF();
			// If invalid token
			if (!isset($_GET['token']) || !$csrf->validate('api', $_GET['token'])) {
				// Turn down request
				die(json_encode(array('error' => 'Unauthorized access')));
			}
		}
	}

	// Daemon Socket
	include(dirname(__FILE__) . '/includes/socket.php');

	// Handle GET
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {

		if (isset($_GET['version'])) {
			die(safe_parse(socket_action('version')));
		}

		if (isset($_GET['interfaces'])) {
			die(safe_parse(socket_action('interfaces')));
		}

		if (isset($_GET['interfaces-stats'])) {
			die(safe_parse(socket_action('interfaces-stats')));
		}

		if (isset($_GET['arp'])) {
			$result = socket_action('arp');
			if (!is_null($result)) {
				foreach ($result as &$device) {
					$hostname = gethostbyaddr($device['ip_address']);
					$device['hostname'] = ($hostname == $device['ip_address'] ? '' : $hostname);
					$device['vendor'] = mac_address_lookup($device['hw_address']);
				}
			}
			die(safe_parse($result));
			//die(safe_parse(socket_action('arp')));
		}

		if (isset($_GET['dnsmasq-leases'])) {
			if (isset($_GET['count'])) {
				die(safe_parse(socket_action('dnsmasq_leases_count')));
			}
			else {
				$result = socket_action('dnsmasq_leases');
				if (!is_null($result)) {
					foreach ($result as &$device) {
						$device['vendor'] = mac_address_lookup($device['mac_address']);
					}
				}
				die(safe_parse($result));
				//die(safe_parse(socket_action('dnsmasq_leases')));
			}
		}
		if (isset($_GET['dnsmasq-queries'])) {
			$max = isset($_GET['max']) ? intval($_GET['max']) : null;
			if ($max === 0 || isset($_GET['count'])) {
				die(safe_parse(socket_action('dnsmasq_queries_count')));
			}
			else if ($max === null) {
				die(safe_parse(socket_action('dnsmasq_queries')));
			}
			else if ($max > 0) {
				die(safe_parse(socket_request(array(
					'action' => 'dnsmasq_queries',
					'max' => $max
				))));
			}
			die(json_encode(array('error' => 'Invalid request')));
		}
		if (isset($_GET['dnsmasq-cached-queries'])) {
			$max = isset($_GET['max']) ? intval($_GET['max']) : null;
			if ($max === 0 || isset($_GET['count'])) {
				die(safe_parse(socket_action('dnsmasq_queries_cached_count')));
			}
			else if ($max === null) {
				die(safe_parse(socket_action('dnsmasq_queries_cached')));
			}
			else if ($max > 0) {
				die(safe_parse(socket_request(array(
					'action' => 'dnsmasq_queries_cached',
					'max' => $max
				))));
			}
			die(json_encode(array('error' => 'Invalid request')));
		}

		if (isset($_GET['system'])) {
			if (isset($_GET['reboot'])) {
				die(safe_parse(socket_action('system', 'reboot')));
			}
			if (isset($_GET['shutdown'])) {
				die(safe_parse(socket_action('system', 'shutdown')));
			}
			if (isset($_GET['dnsmasq-start'])) {
				die(safe_parse(socket_action('system', 'dnsmasq-start')));
			}
			if (isset($_GET['dnsmasq-stop'])) {
				die(safe_parse(socket_action('system', 'dnsmasq-stop')));
			}
			if (isset($_GET['dnsmasq-restart'])) {
				die(safe_parse(socket_action('system', 'dnsmasq-restart')));
			}
			die(json_encode(array('error' => 'Invalid request')));
		}

		if (isset($_GET['ifconfig'])) {
			die(safe_parse(socket_command('ifconfig')));
		}

		if (isset($_GET['iwconfig'])) {
			die(safe_parse(socket_command('iwconfig')));
		}

		if (isset($_GET['lanshare'])) {
			die(safe_parse(socket_request(array(
				'action' => 'lanshare',
				'method' => 'get'
			))));
		}

	}

	// Handle POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		if (isset($_GET['lanshare'])) {
			$data = json_decode(file_get_contents('php://input'), true);
			die(safe_parse(socket_request(array(
				'action' => 'lanshare',
				'method' => 'set',
				'data' => $data
			))));
		}

	}

	// Bad request
	else {
		die(json_encode(array('error' => 'Bad request')));
	}

	// Check response, if null return error message
	function safe_parse($json) {
		if (is_null($json)) {
			return json_encode(array('error' => 'Socket error'));
		}
		return json_encode($json);
	}

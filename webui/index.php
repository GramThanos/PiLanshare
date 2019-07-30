<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI
 */

	// Info
	include(dirname(__FILE__) . '/includes/config.php');

	// If login is enabled
	if (APP_LOGIN) {
		// Session
		include(dirname(__FILE__).'/includes/session.php');
		// If not logged in
		if (!session_isLoggedIn()) {
			header('Location: login.php');
			exit();
		}
	}

	// Actions
	# No password root script run with visudo, based on
	# https://stackoverflow.com/questions/3173201/sudo-in-php-exec
	# https://stackoverflow.com/questions/10915241/how-to-run-from-php-a-bash-script-under-root-user
	# 
	# # First run the command
	# sudo visudo
	# # Add at the end
	# %www-data ALL=NOPASSWD: /var/www/html/scripts/lanshare-actions.sh
	# 
	# # Fix permissions
	# sudo chown root:root /var/www/html/scripts/lanshare-actions.sh
	# sudo chmod 755 /var/www/html/scripts/lanshare-actions.sh
	#
	function lanshare_action ($command) {
		exec('sudo ' . ACTIONS_SCRIPT_PATH . ' ' . $command . ' 2>&1', $text, $return_var);
		//var_dump($text);
		if ($return_var) return false;
		$output = '';
		foreach ($text as $line) {
			$output .= $line . "\n";
		}
		return $output;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['action'])) {
			$action = $_REQUEST['action'];
			switch ($action) {
				case 'ping':
					die('pong');
					break;
				case 'system-reboot':
					$output = lanshare_action('system-reboot');
					die($output ? $output : 'ERROR');
					break;
				case 'system-shutdown':
					$output = lanshare_action('system-shutdown');
					die($output ? $output : 'ERROR');
					break;
				case 'lanshare-init':
					$output = lanshare_action('lanshare-init');
					die($output ? $output : 'ERROR');
					break;
				case 'dnsmasq-start':
					$output = lanshare_action('dnsmasq-start');
					die($output ? $output : 'ERROR');
					break;
				case 'dnsmasq-stop':
					$output = lanshare_action('dnsmasq-stop');
					die($output ? $output : 'ERROR');
					break;
				case 'version':
					$output = lanshare_action('version');
					die($output ? $output : 'ERROR');
					break;
				case 'test':
					$output = lanshare_action('help');
					die($output ? $output : 'ERROR');
					break;
			}
		}
		die('ACTION-NOT-FOUND');
	}


	// Get leases
	$leases = file_get_contents("/var/lib/misc/dnsmasq.leases");
	$leases = explode("\n", $leases);

	// Parse clients
	$clients = array();
	foreach ($leases as $lease) {
		if (strlen($lease) > 4) {
			$lease = explode(" ", $lease);
			if (count($lease) == 5) {
				array_push($clients, array(
					'timestamp' => $lease[0],	// Expiration Time
					'link' => $lease[1],		// Link Address
					'ip' => $lease[2],			// IP Address
					'hostname' => $lease[3],	// Hostname 
					'client-id' => $lease[4]	// Client ID
				));
			}
			else {
				array_push($clients, array(
					'timestamp' => 0,
					'link' => '',
					'ip' => '',
					'hostname' => 'Unknown',
					'client-id' => ''
				));
			}
		}
	}
	usort($clients, function($a, $b) {
		return strnatcmp($a['ip'], $b['ip']);
	});

	// Ifconfig
	exec('ifconfig', $ifconfig, $return_var);
	$ifconfig_txt = '';
	foreach ($ifconfig as $line) {
		$ifconfig_txt .= $line . "\n";
	}
	// Interfaces
	$interfaces = array();
	$r = preg_match_all('/^(\S+): flags=[^\n]+\n        inet ([0-9\.]+)  netmask ([0-9\.]+)(?:  broadcast ([0-9\.]+)|)\n/m', $ifconfig_txt, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach ($matches as $interface) {
			array_push($interfaces, array(
				'name' => $interface[1],
				'inet' => $interface[2],
				'netmask' => $interface[3],
				'broadcast' => count($interface) == 5 ? $interface[4] : false
			));
		}
	}
	usort($interfaces, function($a, $b) {
		return strnatcmp($a['inet'], $b['inet']);
	});

	// Iwconfig
	exec('iwconfig', $iwconfig, $return_var);
	$iwconfig_txt = '';
	foreach ($iwconfig as $line) {
		$iwconfig_txt .= $line . "\n";
	}

	// Get server uptime
	$str   = @file_get_contents('/proc/uptime');
	$num   = floatval($str);
	$secs  = fmod($num, 60); $num = intdiv($num, 60);
	$mins  = $num % 60;      $num = intdiv($num, 60);
	$hours = $num % 24;      $num = intdiv($num, 24);
	$days  = $num;
	$uptime = ($days > 0 ? $days . ' days ' : '') . ($hours > 0 ? $hours . ' hours ' : '') . ($mins > 0 ? $mins . ' minutes ' : '') . floor($secs) .' seconds';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?=htmlspecialchars(APP_NAME);?></title>
		<meta name="description" content="<?=htmlspecialchars(APP_DESCRIPTION);?>">
		<meta name="author" content="GramThanos">
		<link rel='shortcut icon' type='image/x-icon' href='favicon.ico'/>

		<!-- Google Font -->
		<link href="https://fonts.googleapis.com/css?family=Quicksand:400,700&display=swap" rel="stylesheet">
		<!-- Bootstrap -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<!-- Custom Style -->
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>

		<!-- Top Bar -->
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="container">
				<a class="navbar-brand" href="/"><img src="images/logo-16.png" style="margin-bottom: 4px;"> <?=htmlspecialchars(APP_NAME);?> <small><?=htmlspecialchars(APP_VERSION);?></small></a>

				<?php if (APP_LOGIN) { ?>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navbar" aria-controls="main-navbar" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="main-navbar">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item">
							<a class="nav-link"><?=htmlspecialchars(session_get_userUsername());?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="login.php?logout">Logout</a>
						</li>
					</ul>
				</div>
				<?php } ?>

			</div>
		</nav>

		<div class="container">

			<!-- Dnsmasq Section -->
			<div class="row">
				<div class="col">
					<div class="card">
						<div class="card-body">
							<ul class="nav nav-tabs" id="dnsmasq-tabs" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="dnsmasq-leases-tab" data-toggle="tab" href="#dnsmasq-leases" role="tab" aria-controls="dnsmasq-leases" aria-selected="true">Dnsmasq Leases</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="dnsmasq-tools-tab" data-toggle="tab" href="#dnsmasq-tools" role="tab" aria-controls="dnsmasq-tools" aria-selected="false">Tools</a>
								</li>
							</ul>
							<div class="tab-content" id="dnsmasq-tabs-content">
								<div class="tab-pane fade show active" id="dnsmasq-leases" role="tabpanel" aria-labelledby="dnsmasq-leases-tab">
									<div class="table-responsive">
										<table class="table table-bordered">
											<thead>
												<tr>
													<th scope="col">#</th>
													<th scope="col">Expiration Time</th>
													<th scope="col">Link Address</th>
													<th scope="col">IP Address</th>
													<th scope="col">Hostname</th>
													<th scope="col">Client ID</th>
												</tr>
											</thead>
											<tbody>
												<?php
													$count = 0;
													foreach ($clients as $client) {
														$count++;
												?>
												<tr>
													<th scope="row"><?=$count;?></th>
													<td><?=htmlspecialchars($client['timestamp']);?></td>
													<td><?=htmlspecialchars($client['link']);?></td>
													<td><?=htmlspecialchars($client['ip']);?></td>
													<td><?=htmlspecialchars($client['hostname']);?></td>
													<td><?=htmlspecialchars($client['client-id']);?></td>
												</tr>
												<?php
													}
													if ($count == 0) {
														echo '<tr><td colspan="6">No clients</td></tr>';
													}
												?>
											</tbody>
										</table>
									</div>
								</div>
								<div class="tab-pane fade" id="dnsmasq-tools" role="tabpanel" aria-labelledby="dnsmasq-tools-tab">
									<div class="table-responsive">
										<table class="table table-bordered">
											<tbody>
												<tr>
													<td>LAN share script</td>
													<td><button id="tools-lanshare-restart" type="button" class="btn btn-sm btn-outline-primary">Restart LanShare</button></td>
												</tr>
												<tr>
													<td>System</td>
													<td>
														<button id="tools-system-reboot" type="button" class="btn btn-sm btn-outline-danger">Reboot System</button>
														<button id="tools-system-shutdown" type="button" class="btn btn-sm btn-outline-danger">Shutdown System</button>
													</td>
												</tr>
												<tr>
													<td>Qnsmasq Service</td>
													<td>
														<button id="tools-dnsmasq-start" type="button" class="btn btn-sm btn-outline-success">Qnsmasq Start</button>
														<button id="tools-dnsmasq-stop" type="button" class="btn btn-sm btn-outline-danger">Qnsmasq Stop</button>
													</td>
												</tr>
												<?php if (APP_DEBUG) { ?>
												<tr>
													<td>Actions script test <span class="badge badge-success">Debug</span></td>
													<td><button id="tools-actions-version" type="button" class="btn btn-sm btn-outline-success">Get actions version</button></td>
												</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>

									
									
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Network Section -->
			<div class="row">
				<div class="col">
					<div class="card">
						<div class="card-body">
							<ul class="nav nav-tabs" id="networks-tabs" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="interfaces-tab" data-toggle="tab" href="#interfaces" role="tab" aria-controls="interfaces" aria-selected="true">Interfaces</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="ifconfig-tab" data-toggle="tab" href="#ifconfig" role="tab" aria-controls="ifconfig" aria-selected="false">ifconfig</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="iwconfig-tab" data-toggle="tab" href="#iwconfig" role="tab" aria-controls="iwconfig" aria-selected="false">iwconfig</a>
								</li>
							</ul>
							<div class="tab-content" id="networks-tabs-content">

								<!-- Interfaces -->
								<div class="tab-pane fade show active" id="interfaces" role="tabpanel" aria-labelledby="interfaces-tab">
									<div class="table-responsive">
										<table class="table table-bordered">
											<thead>
												<tr>
													<th scope="col">#</th>
													<th scope="col">Name</th>
													<th scope="col">Inet</th>
													<th scope="col">Netmask</th>
													<th scope="col">Broadcast</th>
												</tr>
											</thead>
											<tbody>
												<?php
													$count = 0;
													foreach ($interfaces as $interface) {
														$count++;
												?>
												<tr>
													<th scope="row"><?=$count;?></th>
													<td><?=htmlspecialchars($interface['name']);?></td>
													<td><?=htmlspecialchars($interface['inet']);?></td>
													<td><?=htmlspecialchars($interface['netmask']);?></td>
													<td><?=($interface['broadcast'] ? htmlspecialchars($interface['broadcast']) : '-');?></td>
												</tr>
												<?php
													}
													if ($count == 0) {
														echo '<tr><td colspan="5">No interfaces</td></tr>';
													}
												?>
											</tbody>
										</table>
									</div>
								</div>

								<!-- ifconfig -->
								<div class="tab-pane fade" id="ifconfig" role="tabpanel" aria-labelledby="ifconfig-tab">
									<pre><?=htmlspecialchars($ifconfig_txt);?></pre>
								</div>

								<!-- iwconfig -->
								<div class="tab-pane fade" id="iwconfig" role="tabpanel" aria-labelledby="iwconfig-tab">
									<pre><?=htmlspecialchars($iwconfig_txt);?></pre>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- System Info Section -->
			<div class="row">
				<div class="col">
					<div class="card">
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered">
									<tbody>
										<tr>
											<td>Hostname</td>
											<td><?=htmlspecialchars(gethostname());?></td>
										</tr>
										<tr>
											<td>Uptime</td>
											<td><?=$uptime;?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer -->
			<div class="row footer">
				<div class="col col-12 col-sm-6 text-left">
					<a href="<?=APP_WEBSITE;?>" target="_blank"><?=htmlspecialchars(APP_NAME);?></a> <?=htmlspecialchars(APP_VERSION);?>
				</div>
				<div class="col col-12 col-sm-6 text-right">
					Created by <a href="https://github.com/GramThanos" target="_blank">GramThanos</a>
				</div>
			</div>

		</div>

		<!-- Action Loading Modal -->
		<div class="modal fade" id="commandModal" tabindex="-1" role="dialog" aria-labelledby="commandModalLabel" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="commandModalLabel">Executing Action <span id="action-name"></span></h5>
					</div>
					<div class="modal-body">
						<div class="progress">
							<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- jQuery & Popper -->
		<script src="js/jquery-3.4.1.min.js"></script>
		<script src="js/popper.min.js"></script>
		<!-- Bootstrap -->
		<script src="js/bootstrap.min.js"></script>
		<script src="js/bootbox.min.js"></script>
		<!-- Custom Javascript -->
		<script src="js/actions.js"></script>
	</body>
</html>

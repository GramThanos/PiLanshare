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

	// Tools
	include(dirname(__FILE__) . '/includes/tools.php');
	// Daemon Socket
	include(dirname(__FILE__) . '/includes/socket.php');

	// Menu
	$menu = 'dashboard';

	// Get info
	
	$dnsmasq_leases = socket_action('dnsmasq_leases_count');
	if (is_null($dnsmasq_leases) || !isset($dnsmasq_leases['count'])) $dnsmasq_leases = 'N/A';
	else $dnsmasq_leases = number_format($dnsmasq_leases['count'], 0, ',', '.');
	
	$dnsmasq_queries = socket_action('dnsmasq_queries_count');
	if (is_null($dnsmasq_queries) || !isset($dnsmasq_queries['count'])) $dnsmasq_queries = 'N/A';
	else $dnsmasq_queries = number_format($dnsmasq_queries['count'], 0, ',', '.');

	$uptime = system_uptime_short();

	$traffic = socket_action('interfaces-stats');
	if (is_null($traffic) || isset($traffic['error'])) $traffic = 'N/A';
	else {
		$total = 0;
		foreach ($traffic as $interface) {
			if (isset($interface['rx']) && isset($interface['rx']['bytes']))
				$total += $interface['rx']['bytes'];
			if (isset($interface['tx']) && isset($interface['tx']['bytes']))
				$total += $interface['tx']['bytes'];
		}
		$traffic = bytes_to_readable_text($total);
	}
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

		<!-- Font-Awesome -->
		<link rel="stylesheet" href="css/fontawesome.min.css">
		<!-- Bootstrap -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<!-- Custom Style -->
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>

		<!-- Top Bar --><?php include(dirname(__FILE__) . '/includes/topbar.php');?>

		<!-- Menu Topbar --><?php include(dirname(__FILE__) . '/includes/menu-topbar.php');?>

		<div class="container">
			<div class="row">

				<!-- Menu Sidebar --><?php include(dirname(__FILE__) . '/includes/menu-sidebar.php');?>

				<!-- Main content -->
				<div class="col-12 col-lg-10 col-xl-10">
					<div class="card">
						<h6 class="card-header">Dashboard</h6>
						<div class="card-body" style="padding-top: 0px;">
							<div class="row">

								<div class="col-12 col-md-6 col-lg-3">
									<div class="card bg-primary card-shortcut">
										<div class="card-body">
											<h6 class="card-title">
												<i class="fas fa-laptop"></i> Clients
											</h6>
											<a href="devices.php" class="stretched-link">
												<?=$dnsmasq_leases;?>
											</a>
										</div>
									</div>
								</div>

								<div class="col-12 col-md-6 col-lg-3">
									<div class="card bg-secondary card-shortcut">
										<div class="card-body">
											<h6 class="card-title">
												<i class="fas fa-map-marker"></i> DNS Queries
											</h6>
											<a href="queries.php" class="stretched-link">
												<?=$dnsmasq_queries;?>
											</a>
										</div>
									</div>
								</div>

								<div class="col-12 col-md-6 col-lg-3">
									<div class="card bg-success card-shortcut">
										<div class="card-body">
											<h6 class="card-title">
												<i class="fas fa-network-wired"></i> Total Traffic
											</h6>
											<a href="netstats.php" class="stretched-link">
												<?=$traffic;?>
											</a>
										</div>
									</div>
								</div>

								<div class="col-12 col-md-6 col-lg-3">
									<div class="card bg-warning card-shortcut">
										<div class="card-body">
											<h6 class="card-title">
												<i class="far fa-clock"></i> Uptime
											</h6>
											<a href="system.php" class="stretched-link" style="font-size: 18px;">
												<?=$uptime;?>
											</a>
										</div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				</div>

			</div>

			<!-- Footer --><?php include(dirname(__FILE__) . '/includes/footer.php');?>
		</div>

		<!-- jQuery & Popper -->
		<script src="js/jquery-3.4.1.min.js"></script>
		<script src="js/popper.min.js"></script>
		<!-- Bootstrap -->
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>

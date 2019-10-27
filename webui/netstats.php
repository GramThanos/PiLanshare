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
		// if API has secure token enabled
		if (API_USE_CSRF_TOKEN) {
			// Include the PHP-CSRF library
			include(dirname(__FILE__) . '/includes/php-csrf.php');
			$csrf = new CSRF();
			$hashes = $csrf->getHashes('api');
			$api_token = (count($hashes) == 0) ? $csrf->string('api') : $hashes[0];
		}
	}

	// Tools
	include(dirname(__FILE__) . '/includes/tools.php');

	// Menu
	$menu = 'netstats';
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
					<div class="row" id="stats-wrapper">

						<!-- Loading message -->
						<div class="col-12" id="loading">
							<div class="card">
								<h6 class="card-header">Loading</h6>
								<div class="card-body" style="min-height: 200px;text-align: center;">
									<br><br><span><i class="fas fa-circle-notch fa-spin"></i> Please Wait ...</span><br><br>
								</div>
							</div>
						</div>

						<!-- Interface Template -->
						<div class="col-12 col-md-6" id="interface-template" style="display: none;">
							<div class="card">
								<h6 class="card-header">Total</h6>
								<div class="table-responsive">
									<table class="table table-full-card stats-card">
										<thead>
											<tr>
												<td></td>
												<td><small><i class="fas fa-long-arrow-alt-down"></i> Traffic In</small></td>
												<td><small><i class="fas fa-long-arrow-alt-up"></i> Traffic Out</small></td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td><small><i class="fas fa-box"></i> Packets</small></td>
												<td>0</td>
												<td>0</td>
											</tr>
											<tr>
												<td><small><i class="fas fa-file"></i> Volume</small></td>
												<td>0</td>
												<td>0</td>
											</tr>
											<tr>
												<td><small><i class="fas fa-exclamation-circle"></i> Errors</small></td>
												<td>0</td>
												<td>0</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="chart-wrapper"></div>
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
		<!-- Chartjs -->
		<script src="js/chart.min.js"></script>
		<!-- Script -->
		<?php if (isset($api_token)) {
			echo '<script type="text/javascript">const API_TOKEN = ' . json_encode($api_token) . ";</script>\n";
		}?>
		<script src="js/netstats.js"></script>
	</body>
</html>

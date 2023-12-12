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
	$menu = 'lanshare';
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
		<!-- jsNotify -->
		<link rel="stylesheet" href="css/jsNotify.css">
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
					<div class="row">

						<!-- Loading message -->
						<div class="col-12" id="loading" style="display: none;">
							<div class="card">
								<h6 class="card-header">Error</h6>
								<div class="card-body" style="min-height: 200px;text-align: center;">
									<br><br><span>...</span><br><br>
								</div>
							</div>
						</div>

						<!-- Main Content -->
						<div class="col-12" id="content">
							<div class="card">
								<h6 class="card-header">LAN Share</h6>
								<div class="card-body">

									<h5>IPTABLES <span class="badge badge-secondary" id="iptables_enable_badge" style="float:right;"><i class="fas fa-circle-notch fa-spin"></i> loading</span></h5>
									<br>

									<div class="form-group row">
										<div class="col-md-2">iptables</div>
										<div class="col-md-10">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="iptables_enable" id="iptables_enable_true" value="true" checked>
												<label class="form-check-label" for="iptables_enable_true">
													Enable
												</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="iptables_enable" id="iptables_enable_false" value="false">
												<label class="form-check-label" for="iptables_enable_false">
													Disable
												</label>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-2">Interfaces</div>
										<div class="col-md-10">
											Share from interface
											<select class="form-control" id="iptables_interface_source">
												<option>Loading ...</option>
											</select>
											to interface
											<select class="form-control" id="iptables_interface_target">
												<option>Loading ...</option>
											</select>
										</div>
									</div>
									<div class="form-group row">
										<label for="iptables_ip_address" class="col-md-2 col-form-label">Gateway</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="iptables_ip_address" placeholder="ex. 192.168.3.1">
										</div>
									</div>
									<div class="form-group row">
										<label for="iptables_netmask" class="col-md-2 col-form-label">Netmask</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="iptables_netmask" placeholder="ex. 255.255.255.0">
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-2">Other</div>
										<div class="col-md-10">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="iptables_enable_ip_forward">
												<label class="form-check-label" for="iptables_enable_ip_forward">
													Enable IP forward
												</label>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-2"></div>
										<div class="col-md-10">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="iptables_remove_default_routes">
												<label class="form-check-label" for="iptables_remove_default_routes">
													Remove default routes
												</label>
											</div>
										</div>
									</div>

									<br>
									<hr>

									<h5>DNSMASQ <span class="badge badge-secondary" id="dnsmasq_enable_badge" style="float:right;"><i class="fas fa-circle-notch fa-spin"></i> loading</span></h5>
									<br>

									<div class="form-group row">
										<div class="col-md-2">dnsmasq</div>
										<div class="col-md-10">
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="dnsmasq_enable" id="dnsmasq_enable_true" value="true" checked>
												<label class="form-check-label" for="dnsmasq_enable_true">
													Enable
												</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="dnsmasq_enable" id="dnsmasq_enable_false" value="false">
												<label class="form-check-label" for="dnsmasq_enable_false">
													Disable
												</label>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_interface" class="col-md-2 col-form-label">Interface</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_interface" placeholder="Loading ..." disabled="disabled">
										</div>
									</div>

									<br>
									<h6>DNSMASQ DHCP</h6>
									<br>


									<div class="form-group row">
										<label for="dnsmasq_dhcp_start" class="col-md-2 col-form-label">Start Address</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_dhcp_start" placeholder="ex. 192.168.3.2">
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_dhcp_end" class="col-md-2 col-form-label">End Address</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_dhcp_end" placeholder="ex. 192.168.3.100">
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_dhcp_netmask" class="col-md-2 col-form-label">Netmask</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_dhcp_netmask" placeholder="ex. 255.255.255.0">
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_dhcp_broadcast" class="col-md-2 col-form-label">Broadcast Address</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_dhcp_broadcast" placeholder="ex. 192.168.3.255">
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_dhcp_lease_time" class="col-md-2 col-form-label">Lease Time</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_dhcp_lease_time" placeholder="ex. 12h">
										</div>
									</div>

									<br>
									<hr>

									<h5>DNSMASQ DNS</h5>
									<br>

									<div class="row">
										<div class="col-md-8">
											<div class="form-group row">
												<label for="dnsmasq_dns_primary" class="col-md-3 col-form-label">Primary</label>
												<div class="col-md-9">
													<input type="text" class="form-control" id="dnsmasq_dns_primary" placeholder="ex. 1.1.1.1">
												</div>
											</div>
											<div class="form-group row">
												<label for="dnsmasq_dns_secondary" class="col-md-3 col-form-label">Secondary</label>
												<div class="col-md-9">
													<input type="text" class="form-control" id="dnsmasq_dns_secondary" placeholder="ex. 1.0.0.1">
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<select class="form-control" id="public-dns-servers">
												<option value="custom">Custom</option>

												<!--<option disabled="disabled">Popular -----</option>-->
												<option value="cloudflare" data-primary="1.1.1.1" data-secondary="1.0.0.1">Cloudflare</option>
												<option value="google" data-primary="8.8.8.8" data-secondary="8.8.4.4">Google Public DNS</option>
												<option value="opendns" data-primary="208.67.222.222" data-secondary="208.67.220.220">OpenDNS</option>
												<option value="opendns-family" data-primary="208.67.222.123" data-secondary="208.67.220.123">OpenDNS Family Shield</option>

												<!--
												<option disabled="disabled">Other -----</option>
												<option value="comodo" data-primary="8.26.56.26" data-secondary="8.20.247.20">Comodo Secure DNS</option>
												<option value="dyn" data-primary="216.146.35.35" data-secondary="216.146.36.36">Dyn</option>
												<option value="opendns-family" data-primary="208.67.222.123" data-secondary="208.67.220.123">OpenDNS Family Shield</option>
												<option value="quad9" data-primary="9.9.9.9" data-secondary="149.112.112.112">Quad9</option>
												<option value="verisign" data-primary="64.6.64.6" data-secondary="64.6.65.6">Verisign DNS</option>
												-->
											</select>
										</div>
									</div>
									<br>
									<div class="form-group row">
										<label for="dnsmasq_router_ip_address" class="col-md-2 col-form-label">PiLanshare IP address</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_router_ip_address" placeholder="Loading ..." disabled="disabled">
										</div>
									</div>
									<div class="form-group row">
										<label for="dnsmasq_router_domain_name" class="col-md-2 col-form-label">PiLanshare domain name</label>
										<div class="col-md-10">
											<input type="text" class="form-control" id="dnsmasq_router_domain_name" placeholder="ex. pilanshare.local">
										</div>
									</div>

									<br>
									<hr>

									<h5>Address Binding</h5>
									<br>

									<div class="form-group row">
										<div class="col-md-5">MAC Address / Hostname</div>
										<div class="col-md-5">IP Address</div>
										<div class="col-md-2"></div>
									</div>
									<div id="binds-wrapper">
										<div id="bind-dummy" class="form-group row" style="display: none;">
											<div class="col-md-5">
												<input type="text" class="form-control" placeholder="ex. 00:11:22:33:44:55">
											</div>
											<div class="col-md-5">
												<input type="text" class="form-control" placeholder="ex. 192.168.3.123">
											</div>
											<div class="col-md-2">
												<button type="button" class="btn btn-outline-danger" style="width: 100%">
													<i class="fas fa-trash-alt"></i> Remove
												</button>
											</div>
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-5"></div>
										<div class="col-md-5"></div>
										<div class="col-md-2">
											<button id="bind-add" type="button" class="btn btn-outline-success" style="width: 100%">
												<i class="fas fa-plus"></i> Add
											</button>
										</div>
									</div>

									<br>
									<hr>
									<br>

									<div class="form-group row" style="text-align: right;">
										<div class="col-12">
											<button id="apply" type="button" class="btn btn-outline-success" style="width: 100px;">
												<i class="far fa-save"></i> Apply
											</button>
											<button id="reset" type="button" class="btn btn-outline-danger" style="width: 100px;">
												<i class="fas fa-redo"></i> Reset
											</button>
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

		<!-- Applying Modal -->
		<div class="modal fade" id="applyModal" tabindex="-1" role="dialog" aria-labelledby="applyModalLabel" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="applyModalLabel">Applying Settings</h5>
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
		<script src="js/jquery.min.js"></script>
		<script src="js/popper.min.js"></script>
		<!-- Bootstrap -->
		<script src="js/bootstrap.min.js"></script>
		<!-- jsNotify Scripts -->
		<script src="js/jsnotify.js"></script>
		<!-- Script -->
		<?php if (isset($api_token)) {
			echo '<script type="text/javascript">const API_TOKEN = ' . json_encode($api_token) . ";</script>\n";
		}?>
		<script src="js/lanshare.js"></script>
	</body>
</html>

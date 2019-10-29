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
	$menu = 'system';
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

						<!-- System Info -->
						<div class="col-12 col-md-6">
							<div class="card">
								<h6 class="card-header">
									<i class="fas fa-server"></i> Server
								</h6>
								<div class="table-responsive">
									<table class="table table-full-card">
										<tbody>
											<tr>
												<td>Hostname</td>
												<td><?=htmlspecialchars(gethostname());?></td>
											</tr>
											<tr>
												<td>Date</td>
												<td><?=htmlspecialchars(date('Y/m/d H:i:s P'));?></td>
											</tr>
											<!--<tr>
												<td>Timezone</td>
												<td><?=htmlspecialchars(date('e'));?></td>
											</tr>-->
											<tr>
												<td>Uptime</td>
												<td><?=htmlspecialchars(system_uptime());?></td>
											</tr>
											<tr>
												<td>Temperature</td>
												<td><?=htmlspecialchars(system_temp_celsius() . '');?>°C</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Programs Versions -->
						<div class="col-12 col-md-6">
							<div class="card">
								<h6 class="card-header">
									<i class="fas fa-code-branch"></i> Versions
								</h6>
								<div class="table-responsive">
									<table class="table table-full-card">
										<tbody>
											<tr>
												<td>System</td>
												<td><?=htmlspecialchars(system_uname());?></td>
											</tr>
											<tr>
												<td>Iptables</td>
												<td><?=htmlspecialchars(system_iptables_version());?></td>
											</tr>
											<tr>
												<td>DNSmasq</td>
												<td><?=htmlspecialchars(system_qnsmasq_version());?></td>
											</tr>
											<tr>
												<td>PiLanShare Daemon</td>
												<td><?=htmlspecialchars(system_pilanshare_version());?></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Actions -->
						<div class="col-12">
							<div class="card">
								<h6 class="card-header">
									<i class="fas fa-bolt"></i> Actions
								</h6>
								<div class="card-body">
									<button id="action-reboot" type="button" class="btn btn-outline-danger">Reboot</button>
									<button id="action-shutdown" type="button" class="btn btn-outline-danger">Shutdown</button>
								</div>
							</div>
						</div>

						<?php if (APP_DEBUG) { ?>
						<!-- API info -->
						<div class="col-12">
							<div class="card">
								<h6 class="card-header">
									<i class="fas fa-terminal"></i> DEBUG - API
								</h6>
								<div class="card-body">
									
									<?php if (isset($api_token)) { ?>
										Your API token is <code><?=$api_token;?></code><br>
										<br>
										Example API links:<br>
										<ul>
											<li><a href="ajax.php?version&token=<?=$api_token;?>" target="_blank">ajax.php?version&token=<?=$api_token;?></a></li>
											<li><a href="ajax.php?interfaces&token=<?=$api_token;?>" target="_blank">ajax.php?interfaces&token=<?=$api_token;?></a></li>
											<li><a href="ajax.php?dnsmasq-leases&token=<?=$api_token;?>" target="_blank">ajax.php?dnsmasq-leases&token=<?=$api_token;?></a></li>
										</ul>
									<?php } else { ?>
										There is no API token.<br>
										<br>
										Example API links:<br>
										<ul>
											<li><a href="ajax.php?version" target="_blank">ajax.php?version</a></li>
											<li><a href="ajax.php?interfaces" target="_blank">ajax.php?interfaces</a></li>
											<li><a href="ajax.php?dnsmasq-leases" target="_blank">ajax.php?dnsmasq-leases</a></li>
										</ul>
									<?php }?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<!-- Footer --><?php include(dirname(__FILE__) . '/includes/footer.php');?>
		</div>

		<!-- Action Loading Modal -->
		<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="actionModalTitle"></h5>
					</div>
					<div class="modal-body">
						<div id="actionModalBody"></div>
						<div id="actionModalProgress" class="progress">
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
		<!-- jsNotify Scripts -->
		<script src="js/jsnotify.js"></script>
		<!-- Script -->
		<?php if (isset($api_token)) {
			echo '<script type="text/javascript">const API_TOKEN = ' . json_encode($api_token) . ";</script>\n";
		}?>
		<script type="text/javascript">
			// Link function
			let link = function (x) {
				let link = 'ajax.php';
				let front = '?';
				for (let i in x) {
					link += front + i + (x[i] !== null ? '=' + x[i] : '');
					if (front === '?') front = '&';
				}
				return link + (API_TOKEN ? front + 'token=' + API_TOKEN : '');
			}

			// Function to check connection with the server
			let check_api = function (callback, cooldown, postpone) {
				if (!cooldown) cooldown = 5 * 1000;
				if (postpone) {
					return setTimeout(function() {
						check_api(callback, cooldown);
					}, postpone);
				}
				$.ajax({url: link({'version':null}), dataType: 'json'})
				.done((json) => {
					callback(json);
				})
				.fail(() => {
					setTimeout(function() {
						check_api(callback, cooldown);
					}, cooldown);
				});
			}

			// Reboot system command
			$('#action-reboot').click(() => {
				bootbox.confirm({
					message: '<div class="text-center" style="padding: 20px 0;">Are you sure you want to reboot the system?</div>',
					buttons: {
						confirm: {label: 'Yes, reboot it!', className: 'btn-danger'},
						cancel: {label: 'No...', className: 'btn-success'}
					},
					centerVertical: true,
					backdrop: true,
					callback: function (result) {
						if (!result) return;
						$('#actionModalTitle').text('Reboot');
						$('#actionModalBody').text('Connecting ...');
						$('#actionModalProgress').show();
						$('#actionModal').modal('show');
						$.ajax({url: link({'system':null, 'reboot':null}), dataType: 'json'})
						.done((json) => {
							// Check for errors
							if (json.error) {
								$('#actionModal').modal('hide');
								jsNotify.danger(json.error, {time2live : 10*1000});
								return;
							}
							// Wait connection
							$('#actionModalBody').text('Waiting system to reboot...');
							check_api(function() {
								$('#actionModal').modal('hide');
								document.location.href = document.location.href;
							}, 5 * 1000, 20 * 1000);
						})
						.fail(() => {
							// Show error
							$('#actionModal').modal('hide');
							jsNotify.danger('Connection failed!', {time2live : 10*1000});
						});
						return;
					}
				});
				return;
			});

			// Shutdown system command
			$('#action-shutdown').click(() => {
				bootbox.confirm({
					message: '<div class="text-center" style="padding: 20px 0;">Are you sure you want to shutdown the system?</div>',
					buttons: {
						confirm: {label: 'Yes, shut it down!', className: 'btn-danger'},
						cancel: {label: 'No...', className: 'btn-success'}
					},
					centerVertical: true,
					backdrop: true,
					callback: function (result) {
						if (!result) return;
						$('#actionModalTitle').text('Shutdown');
						$('#actionModalBody').text('Connecting ...');
						$('#actionModalProgress').show();
						$('#actionModal').modal('show');
						$.ajax({url: link({'system':null, 'shutdown':null}), dataType: 'json'})
						.done((json) => {
							// Check for errors
							if (json.error) {
								$('#actionModal').modal('hide');
								jsNotify.danger(json.error, {time2live : 10*1000});
								return;
							}
							// Wait connection
							$('#actionModalBody').text(json.message);
							check_api(function() {
								$('#actionModal').modal('hide');
								document.location.href = document.location.href;
							}, 60 * 1000, 60 * 1000);
						})
						.fail(() => {
							// Show error
							$('#actionModal').modal('hide');
							jsNotify.danger('Connection failed!', {time2live : 20*1000});
						});
						return;
					}
				});
				return;
			});
		</script>
	</body>
</html>

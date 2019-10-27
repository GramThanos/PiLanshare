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
	$menu = 'devices';
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
		<!-- DataTables -->
		<link rel="stylesheet" href="css/datatables.min.css">
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

						<!-- Error message -->
						<div class="col-12" id="error" style="display: none;">
							<div class="card">
								<h6 class="card-header">Error</h6>
								<div class="card-body" style="min-height: 200px;text-align: center;">
									<br><br><span>...</span><br><br>
								</div>
							</div>
						</div>

						<!-- Dnsmasq Card -->
						<div class="col-12" id="dnsmasq-card">
							<div class="card">
								<h6 class="card-header">DNSMASQ Leases</h6>
								<div class="table-responsive">
									<table class="table table-full-card" id="dnsmasq-table">
										<thead>
											<tr>
												<td><small>IP Address</small></td>
												<td><small>MAC Address</small></td>
												<td><small>Hostname</small></td>
												<td><small>Vendor</small></td>
												<td><small>Client ID</small></td>
												<td><small>Lease</small></td>
											</tr>
										</thead>
										<tbody id="dnsmasq-table-body">
											<tr>
												<td colspan="6" style="text-align: center;"><i class="fas fa-circle-notch fa-spin"></i> Loading ...</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<!-- Arp Card -->
						<div class="col-12" id="arp-card">
							<div class="card">
								<h6 class="card-header">ARP Devices</h6>
								<div class="table-responsive">
									<table class="table table-full-card" id="arp-table">
										<thead>
											<tr>
												<td><small>IP Address</small></td>
												<td><small>MAC Address</small></td>
												<td><small>Hostname</small></td>
												<td><small>Vendor</small></td>
												<td><small>Interface</small></td>
											</tr>
										</thead>
										<tbody id="arp-table-body">
											<tr>
												<td colspan="5" style="text-align: center;"><i class="fas fa-circle-notch fa-spin"></i> Loading ...</td>
											</tr>
										</tbody>
									</table>
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
		<!-- DataTables -->
		<script src="js/datatables.min.js"></script>
		<!-- jsNotify Scripts -->
		<script src="js/jsnotify.js"></script>
		<!-- Script -->
		<?php if (isset($api_token)) {
			echo '<script type="text/javascript">const API_TOKEN = ' . json_encode($api_token) . ";</script>\n";
		}?>
		<script type="text/javascript">
			function sort_IP (a, b) {
				a = a.split( '.' );
				b = b.split( '.' );
				for (let i = 0; i < a.length; i++) {
					if ((a[i] = parseInt(a[i], 10)) < (b[i] = parseInt(b[i],10)))
						return -1;
					else if (a[i] > b[i])
						return 1;
				}
				return 0;
			}
			jQuery.fn.dataTableExt.oSort['ip-asc'] = function (a, b) {
				return sort_IP(a, b);
			}
			jQuery.fn.dataTableExt.oSort['ip-desc'] = function (a, b) {
				return sort_IP(a, b) * -1;
			}

			window.addEventListener('load', () => {
				// Show error
				let error = function (message) {
					$('#error').show();
					$('#error').find('span').text(message);
					$('#dnsmasq-card').hide();
					$('#arp-card').hide();
				}

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

				$.ajax({url: link({'dnsmasq-leases':null}), dataType: 'json'})
				.done((json) => {
					// Check for errors
					if (json.error) return error(json.error);

					// If no devices
					if (json.length === 0) {
						$('#dnsmasq-table-body').find('td').text('No devices.');
					}
					// Show devices
					else {
						$('#dnsmasq-table-body').empty();
						//json.sort(function (a, b) {
						//	return sort_IP(a.ip_address, b.ip_address);
						//});
						for (let i = 0; i < json.length; i++) {
							$('#dnsmasq-table-body').append(
								$('<tr></tr>')
								//.append($('<td></td>').text(i + 1))
								.append($('<td></td>').text(json[i].ip_address))
								.append($('<td></td>').text(json[i].mac_address))
								.append($('<td></td>').text(json[i].hostname))
								.append($('<td></td>').append($('<small></small>').text(json[i].vendor === null ? '' : json[i].vendor)))
								.append($('<td></td>').text(json[i]['client-id']))
								.append($('<td></td>').text(json[i].lease))
							);
						}
						$('#dnsmasq-table').DataTable({'order': [[0, 'asc']], 'aoColumnDefs': [{'sType':'ip','aTargets':[0]}]});
					}
				})
				.fail(() => {
					// Show error
					error('Connection failed!');
				});

				$.ajax({url: link({'arp':null}), dataType: 'json'})
				.done((json) => {
					// Check for errors
					if (json.error) return error(json.error);

					// If no devices
					if (json.length === 0) {
						$('#arp-table-body').find('td').text('No devices.');
					}
					// Show devices
					else {
						$('#arp-table-body').empty();
						//json.sort(function (a, b) {
						//	return sort_IP(a.ip_address, b.ip_address);
						//});
						for (let i = 0; i < json.length; i++) {
							$('#arp-table-body').append(
								$('<tr></tr>')
								//.append($('<td></td>').text(i + 1))
								//.append($('<td></td>').text(json[i].mask))
								.append($('<td></td>').text(json[i].ip_address))
								.append($('<td></td>').text(json[i].hw_address))
								.append($('<td></td>').text(json[i].hostname))
								.append($('<td></td>').append($('<small></small>').text(json[i].vendor === null ? '' : json[i].vendor)))
								//.append($('<td></td>').text(json[i].hw_type + ' ' + json[i].flags))
								//.append($('<td></td>').text(json[i].flags))
								//.append($('<td></td>').text(json[i].hw_type))
								.append($('<td></td>').text(json[i].device))
							);
						}
						$('#arp-table').DataTable({'order': [[0, 'asc']], 'aoColumnDefs': [{'sType':'ip','aTargets':[0]}]});
					}
				})
				.fail(() => {
					// Show error
					error('Connection failed!');
				});
			}, false);
		</script>
	</body>
</html>

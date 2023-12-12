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
	$menu = 'queries';
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
						<div class="col-12" id="queries">
							<div class="card">
								<h6 class="card-header">DNSMASQ Queries</h6>
								<div class="table-responsive">
									<table class="table table-full-card" id="queries-table">
										<thead>
											<tr>
												<td><small>Domain</small></td>
												<td><small>Type</small></td>
												<td><small>Device</small></td>
												<td><small>Date</small></td>
											</tr>
										</thead>
										<tbody id="queries-table-body">
											<tr>
												<td colspan="4" style="text-align: center;"><i class="fas fa-circle-notch fa-spin"></i> Loading ...</td>
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
		<script src="js/jquery.min.js"></script>
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
					$('#queries').hide();
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

				$.ajax({url: link({'dnsmasq-queries':null,'max':1000}), dataType: 'json'})
				.done((json) => {
					// Check for errors
					if (json.error) return error(json.error);

					// If no queries
					if (json.length === 0) {
						$('#queries-table-body').find('td').text('No queries.');
					}
					// Show queries
					else {
						$('#queries-table-body').empty();
						for (let i = json.length - 1; i >= 0; i--) {
							$('#queries-table-body').append(
								$('<tr></tr>')
								//.append($('<td></td>').text(json.length - i))
								.append($('<td></td>').text(json[i].domain))
								.append($('<td></td>').text(json[i].type))
								.append($('<td></td>').text(json[i].device))
								.append($('<td></td>').text(json[i].date))
							);
						}
						$('#queries-table').DataTable({'order': [[3, 'desc']], 'aoColumnDefs': [{'sType':'ip','aTargets':[2]}]});
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

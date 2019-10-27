<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Login
 */

	// Info
	include(dirname(__FILE__).'/includes/config.php');

	// If login is disabled
	if (!APP_LOGIN) {
		header('Location: index.php');
		exit();
	}

	// Messages
	$error = '';
	$success = '';

	// Session
	include(APP_ROOT_PATH . '/includes/session.php');
	if (session_isLoggedIn()) {
		if (isset($_REQUEST['logout'])) {
			session_logOut();
			$success = 'You were logged out.';
		}
		else {
			header('Location: index.php');
			exit();
		}
	}

	// Login post
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Validate username
		$username = false;
		if (!isset($_POST['username'])) {
			$error .= 'No valid username.<br>';
		}
		else {
			$username = $_POST['username'];
		}

		// Validate password
		$password = false;
		if (!isset($_POST['password'])) {
			$error .= 'No valid password.<br>';
		}
		else {
			$password = $_POST['password'];
		}

		// Check if all data valid
		if ($username && $password) {
			include(APP_ROOT_PATH . '/includes/auth.php');
			$user = authenticate($username, $password);

			if (!$user) {
				$error .= 'Login failed.';
			}
			else {
				$remember = isset($_POST['remember_me']);
				session_logIn($user, $remember);
				if (session_isLoggedIn()) {
					header('Location: index.php');
					exit();
				}
			}
		}
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

		<!-- Top Bar -->
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="container">
				<a class="navbar-brand" href="./"><img src="images/logo-16.png" style="margin-bottom: 4px;"> <?=htmlspecialchars(APP_NAME);?></a>
			</div>
		</nav>

		<div class="container login">
			<div class="row login-form">
				<div class="col col-12 col-sm-6 col-lg-8 text-center d-none d-sm-block bg-logo">
				</div>
				<div class="col col-12 col-sm-6 col-lg-4">
					<!-- Login Form -->
					<form method="POST" action="login.php">
						<div class="form-group">
							<label for="username"><i class="fas fa-user"></i> Username</label>
							<input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
						</div>
						<div class="form-group">
							<label for="password"><i class="fas fa-unlock"></i> Password</label>
							<input type="password" class="form-control" id="password" name="password" placeholder="Password">
						</div>
						<div class="form-group form-check">
							<input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
							<label class="form-check-label" for="remember_me">Remember me</label>
						</div>
						<button type="submit" class="btn btn-dark">Login</button>
						<div style="clear: both;"></div>
						<?php if(strlen($error) > 0) { ?>
						<div class="alert alert-danger alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<?=$error;?>
						</div>
						<?php } ?>
						<?php if(strlen($success) > 0) { ?>
						<div class="alert alert-success alert-dismissible" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<?=$success;?>
						</div>
						<?php } ?>
					</form>
				</div>
			</div>

			<!-- Footer -->
			<div class="row footer">
				<div class="col col-12">
					<i class="fab fa-github"></i>
					<a href="<?=APP_WEBSITE;?>" target="_blank"><?=htmlspecialchars(APP_NAME);?></a>
				</div>
				<div class="col col-12">
					<i class="fas fa-mug-hot"></i>
					Created by <a href="https://github.com/GramThanos" target="_blank">GramThanos</a>
				</div>
			</div>
		</div>

		<!-- jQuery & Popper -->
		<script src="js/jquery-3.4.1.min.js"></script>
		<script src="js/popper.min.js"></script>
		<!-- Bootstrap -->
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>

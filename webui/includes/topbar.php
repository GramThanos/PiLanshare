
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="container">
				<a class="navbar-brand" href="./"><img src="images/logo-16.png" style="margin-bottom: 4px;"> <?=htmlspecialchars(APP_NAME);?></a>

				<?php if (APP_LOGIN) { ?>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navbar" aria-controls="main-navbar" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="main-navbar">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item">
							<a class="nav-link">
								<i class="fas fa-user"></i> <?=htmlspecialchars(session_get_userUsername());?>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="login.php?logout">
								<i class="fas fa-sign-out-alt"></i> Logout
							</a>
						</li>
					</ul>
				</div>
				<?php } ?>

			</div>
		</nav>

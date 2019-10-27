<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Menu
 */

if (!isset($menu)) $menu = ''
?>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'dashboard' ? ' active' : '');?>" href="index.php">
								<i class="fas fa-th-large"></i> Dashboard
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'lanshare' ? ' active' : '');?>" href="lanshare.php">
								<i class="fas fa-project-diagram"></i> LAN Share
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'devices' ? ' active' : '');?>" href="devices.php">
								<i class="fas fa-server"></i> Devices
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'queries' ? ' active' : '');?>" href="queries.php">
								<i class="fas fa-map-marker"></i> Queries
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'netstats' ? ' active' : '');?>" href="netstats.php">
								<i class="fas fa-network-wired"></i> Net Stats
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?=($menu == 'system' ? ' active' : '');?>" href="system.php">
								<i class="fab fa-raspberry-pi"></i> System
							</a>
						</li>

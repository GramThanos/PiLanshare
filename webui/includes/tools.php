<?php
/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Tools
 */

// Get system uptime
function system_uptime() {
	// Get server uptime
	$str   = @file_get_contents('/proc/uptime');
	$num   = floatval($str);
	$secs  = fmod($num, 60); $num = intdiv($num, 60);
	$mins  = $num % 60;      $num = intdiv($num, 60);
	$hours = $num % 24;      $num = intdiv($num, 24);
	$days  = $num;
	$uptime = ($days > 0 ? $days . ' days ' : '') . ($hours > 0 ? $hours . ' hours ' : '') . ($mins > 0 ? $mins . ' minutes ' : '') . floor($secs) .' seconds';
	return $uptime;
}
function system_uptime_short() {
	// Get server uptime
	$str   = @file_get_contents('/proc/uptime');
	$num   = floatval($str);
	$secs  = fmod($num, 60); $num = intdiv($num, 60);
	$mins  = $num % 60;      $num = intdiv($num, 60);
	$hours = $num % 24;      $num = intdiv($num, 24);
	$days  = $num;
	$uptime = ($days > 0 ? $days . 'd ' : '') . ($hours > 0 ? $hours . 'h ' : '') . ($mins > 0 ? $mins . 'm ' : '') . floor($secs) .'s';
	return $uptime;
}

// Get system uname
function system_uname() {
	$uname = posix_uname();
	return $uname['sysname'] . ' ' . $uname['release'];
}

// Get versions
function system_iptables_version() {
	$output = shell_exec('iptables --version');
	preg_match('/(\d+\.)?(\d+\.)?\d+(-([a-zA-Z]+\.)?[a-zA-Z]+)?/', $output, $match);
	return ($match ? 'v' . $match[0] : 'N/A');
}
function system_qnsmasq_version() {
	$output = shell_exec('dnsmasq --version');
	preg_match('/(\d+\.)?(\d+\.)?\d+(-([a-zA-Z]+\.)?[a-zA-Z]+)?/', $output, $match);
	return ($match ? 'v' . $match[0] : 'N/A');
}
function system_pilanshare_version() {
	$output = shell_exec('cat /etc/pilanshare/version');
	preg_match('/(\d+\.)?(\d+\.)?\d+(-([a-zA-Z]+\.)?[a-zA-Z]+)?/', $output, $match);
	return ($match ? 'v' . $match[0] : 'N/A');
}

// Get temperature
function system_temp_celsius() {
	$temp = @file_get_contents('/sys/class/thermal/thermal_zone0/temp');
	$temp = floatval($temp) / 1000;
	return round($temp, 1);
}

// MAC address lookup
function mac_address_lookup($mac) {
	if (!file_exists(IEEE_OUI_TXT)) return null;
	$mac = explode(':', strtoupper($mac));
	$output = shell_exec('grep "' . $mac[0] . '-' . $mac[1] . '-' . $mac[2] . '   (hex)"  "' . IEEE_OUI_TXT . '"');
	preg_match('/..-..-..\s+\(hex\)\s+(.+)/', $output, $matches);
	if ($matches) {
		return trim($matches[1]);
	}
	else {
		return null;
	}
}

// Bytes to readable text
function bytes_to_readable_text ($bytes) {
	if (abs($bytes) < 1024) return $bytes . ' B';
	$units = array('kB','MB','GB','TB','PB','EB','ZB','YB');
	$u = -1;
	do {
		$bytes /= 1024;
		++$u;
	} while(abs($bytes) >= 1024 && $u < count($units) - 1);
	return round($bytes, 1) . '' . $units[$u];
}

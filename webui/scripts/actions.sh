#!/bin/sh

#
# PiLanshare WebUI v0.1-beta
# https://github.com/GramThanos/PiLanshare
#
# Actions Shell Script
#

case $1 in
	system-reboot)
		reboot &> /dev/null
		echo "System will now reboot..."
		break
		;;

	system-shutdown)
		shutdown -h now &> /dev/null
		echo "System will now shutdown..."
		break
		;;

	lanshare-init)
		/usr/local/sbin/lan-share.sh
		echo "Lanshare restarted!"
		break
		;;

	dnsmasq-start)
		systemctl start dnsmasq
		echo "Dnsmasq started!"
		break
		;;

	dnsmasq-stop)
		systemctl stop dnsmasq
		echo "Dnsmasq stopped!"
		break
		;;

	help)
		echo "Commands: system-reboot, lanshare-init, dnsmasq-start, dnsmasq-stop, help"
		break
		;;

	version)
		echo "WebUI Actions Script v0.1-beta"
		break
		;;

	*)
		echo "INVALID"
		break
		;;

esac


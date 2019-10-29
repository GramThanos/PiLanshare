![GramThanos](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/icon.png) ![version](https://img.shields.io/badge/PiLanshare-v0.3.0--beta-yellow.svg?style=flat-square)


# PiLanshare
Share your Raspberry's WiFi to Ethernet

![PiLanshare](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_netstats.png)

___


### Setup

#### Install from the web
Install PiLanshare Daemon and WebUI

```cmd
wget https://raw.githubusercontent.com/GramThanos/PiLanshare/v0.3.0-beta/install.py
sudo python3 ./install.py -v
rm ./install.py
```

The default installation paths are, for the daemon `/etc/pilanshare` and for the WebUI `/var/www/html/pilanshare`. The installation script does not install or configure any webserver. It was tested on nginx 1.10.3 and PHP 7.0.33.

You can also download the ieee oui data, so that the WebUI can find the vendor name from the MAC address
```cmd
sudo wget -O /var/www/html/pilanshare/includes/oui.txt http://standards-oui.ieee.org/oui/oui.txt
```

#### Install by clonning the git repo
Install latest development version of PiLanshare Daemon and WebUI

```cmd
sudo apt install python3-distutils
git clone https://github.com/GramThanos/PiLanshare.git
cd ./PiLanshare
sudo python3 ./install.py -v
```

### Configuration

Apart from the WebUI configuration, you can configure the PiLanshare daemon by creating a `pilanshare.ini` file at your Raspberry's boot partition (located at `/boot/`).

Example `pilanshare.ini`
```ini
[DAEMON]
log_level = info

[IPTABLES]
enable = True
interface_source = wlan0
interface_target = eth0
ip_address = 192.168.3.1
netmask = 255.255.255.0

[DNSMASQ]
enable = True
interface = eth0
dhcp_start = 192.168.3.20
dhcp_end = 192.168.3.255
dhcp_netmask = 255.255.255.0
dhcp_broadcast = 192.168.3.255
dhcp_lease_time = 12h
router_ip_address = 192.168.3.1
router_domain_name = pilanshare.local

[DNSMASQ_BINDS]
00:01:02:03:04:05 = 192.168.3.10
02:04:06:08:0A:0C = 192.168.3.11
```

___


### More Images

![preview image - login page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_login.png)
![preview image - dashboard page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_dashboard.png)
![preview image - lanshare page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_lanshare.png)
![preview image - devices page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_devices.png)
![preview image - queries page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_queries.png)
![preview image - net stats page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_netstats.png)
![preview image - system page](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_system.png)


___


### What's next?

- Improve installation script
  - Implement WebUI installation

___


### Contribute to the project

Leave your feedback or to express your thoughts!

You can [open an issue](https://github.com/GramThanos/PiLanshare/issues) or [send me a mail](mailto:gramthanos@gmail.com)


___


### Powered By

This project was made possible by:

Raspberry Pi, Qnsmasq, iptables, Python, PHP, Bootstrap, jQuery, Bootbox, DataTables, Font Awesome, Chart.js, Quicksand Font

___


### License

This project is under [The MIT license](https://opensource.org/licenses/MIT).
I do although appreciate attribution.
The libraries used on the WebUI page have their own licenses.

Copyright (c) 2019 Grammatopoulos Athanasios-Vasileios

___

[![GramThanos](https://avatars2.githubusercontent.com/u/14858959?s=42&v=4)](https://github.com/GramThanos)

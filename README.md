![GramThanos](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/icon.png) ![version](https://img.shields.io/badge/PiLanshare-v0.2--beta-yellow.svg?style=flat-square)


# PiLanshare
Share your Raspberry's WiFi to Ethernet

![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_default_dnsmasq.png)


___


### Set up

*This is the first version of this guide thus it may have errors, help us to improve it. Please open an issue for any problem*

To start with, you should have a Raspberry Pi running Raspbian (Stretch for now).

All the commands bellow should run on the Raspberry Pi.

#### Install dnsmasq

First you will have to install dnsmasq by running
```shell
sudo apt install dnsmasq
```

#### Create Config

You will now have to crate a config file on the boot partition of your Raspberry's SD card (so that edit it even if the rasberry pi is turned off)

You may edit it according to your needs.

Create the config file:
```shell
sudo nano /boot/lan-share
```

Example `lan-share` config contents (lines starting with `#` are comments)
```shell
# Configure a LAN share
#
# Example config
#ip_address="192.168.2.1"
#netmask="255.255.255.0"
#dhcp_range_start="192.168.2.2"
#dhcp_range_end="192.168.2.100"
#dhcp_range_netmask="255.255.255.0"
#dhcp_range_broadcast=" 192.168.2.255"
#dhcp_time="12h"
#dhcp_binds="aa:bb:cc:dd:ee:ff,192.168.2.50"
#eth="eth0"
#wlan="wlan0"

ip_address="192.168.2.1"
dhcp_range_start="192.168.2.2"
dhcp_range_end="192.168.2.100"
dhcp_range_netmask="255.255.255.0"
dhcp_range_broadcast=" 192.168.2.255"
```

#### Install the lan-share.sh script

Download the `lan-share.sh` script from github and move it to the local sbin
```shell
# Download the script
wget https://raw.githubusercontent.com/GramThanos/PiLanshare/master/script/lan-share.sh

# Move it to the sbin
sudo mv ./lan-share.sh /usr/local/sbin/lan-share.sh

# Make it executable
sudo chmod +x /usr/local/sbin/lan-share.sh

# Make it only root accessible
sudo chown root:root /usr/local/sbin/lan-share.sh
sudo chmod 700 /home/pi/lanshare-actions.sh
```

The `lan-share.sh` is based on the [stack-overflow question](https://raspberrypi.stackexchange.com/questions/48307/sharing-the-pis-wifi-connection-through-the-ethernet-port)'s answers.


#### Running the script at boot

There are many ways to run a script at boot, for me the easiest way is to call it by editing the `rc.local`.

Thus, open `rc.local` for edit:
```shell
sudo nano /etc/rc.local
```

And add these lines just before the `exit 0` command:

```shell
# Setup lan-share
sudo bash /usr/local/sbin/lan-share.sh &
```

#### Install the WebUI

Assuming that you have a working webserver with PHP support, running on your Raspberry Pi,

for example Apache2 with PHP
```shell
sudo apt-get install apache2
sudo apt-get install php7.0 libapache2-mod-php7.0
```

You can get the `webui` folder from this project and add it on you webserver.

In this examples we will set it up as the default page of Apache2.

Download this project
```shell
# Download project
wget -O PiLanshare.zip https://github.com/GramThanos/PiLanshare/archive/master.zip

# Extract it
unzip PiLanshare.zip

# Copy WebUI to default Apache2 html folder
cp -r ./PiLanshare-master/webui/* /var/www/html/

# Clean up
rm PiLanshare.zip
rm -r PiLanshare-master
```

Now the webui is on the `/var/www/html/`.

We should now give permissions to the PHP to run the WebUI's actions script by running:

```shell
sudo visudo
```

And adding at the bottom the line (edit it if the actions script is somewhere else or if your webserver runs under an other user group)
```
%www-data ALL=NOPASSWD: /var/www/html/scripts/actions.sh
```

This gives permission the the `www-data` user group (the PHP runs under that group) to run the `actions.sh` script.

You may also need to edit the `define('ACTIONS_SCRIPT_PATH', APP_ROOT_PATH . '/scripts/actions.sh');` of the `<path-to-webui>/includes/config.php` to point to the correct path of the `actions.sh` script.

Finally, change the permissions of the `actions.sh` script, so that only root can edit it.
```shell
sudo chown root:root /var/www/html/scripts/actions.sh
sudo chmod 755 /var/www/html/scripts/actions.sh
```

You can configure the WebUI by editing the `<path-to-webui>/includes/config.php` file.
The example login credentials are username `admin` and password `admin`.

For now the system only supports credentials on the configuration. Example multiple login users list:
```php
define('APP_LOGIN_TYPE_LIST', array(
	// Username		Password in SHA256
	'admin'		=>	'8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918' // admin - admin
	'test'		=>	'9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08' // test - test
));
```

___


### More Images

![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_login.png)
![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_default_interfaces.png)
![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_default_tools.png)
![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_default_action.png)
![preview image](https://raw.githubusercontent.com/GramThanos/PiLanshare/master/preview/webui_default_iwconfig.png)


___


### What's next?

- Create installation scripts
- Improve the WebUI
- Add more configuration options


___


### Contribute to the project

Leave your feedback or to express your thoughts!

You can [open an issue](https://github.com/GramThanos/PiLanshare/issues) or [send me a mail](mailto:gramthanos@gmail.com)


___


### Powered By

This project was made possible by:

Raspberry Pi, Qnsmasq, Google Fonts, Apache2, PHP, Bootstrap, jQuery, Bootbox

___


### License

This project is under [The MIT license](https://opensource.org/licenses/MIT).
I do although appreciate attribution.

Copyright (c) 2019 Grammatopoulos Athanasios-Vasileios

___

[![GramThanos](https://avatars2.githubusercontent.com/u/14858959?s=42&v=4)](https://github.com/GramThanos)
[![DinoDevs](https://avatars1.githubusercontent.com/u/17518066?s=42&v=4)](https://github.com/DinoDevs)

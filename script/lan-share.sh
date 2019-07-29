#!/bin/bash

#
# PiLanshare v0.1-beta
# https://github.com/GramThanos/PiLanshare
#
# LanShare Shell Script
#

# Share Wifi with Eth device
#
#
# This script is created to work with Raspbian Stretch
# but it can be used with most of the distributions
# by making few changes.
#
# Make sure you have already installed `dnsmasq`
# Please modify the variables according to your need
# Don't forget to change the name of network interface
# Check them with `ifconfig`

if [ ! -f /boot/lan-share ]; then
    echo "No lan-share config"
    exit 1
fi

# Default configuration
ip_address="192.168.2.1"
netmask="255.255.255.0"
dhcp_range_start="192.168.2.2"
dhcp_range_end="192.168.2.100"
dhcp_range_netmask="255.255.255.0"
dhcp_range_broadcast=" 192.168.2.255"
dhcp_time="12h"
dhcp_binds=""
eth="eth0"
wlan="wlan0"

# Echo settings
echo -e "Passing network from \"$wlan\" to \"$eth\""

# Load Config
. /boot/lan-share

# DHCP Binds
dhcp_hosts=""
dhcp_binds=$(echo $dhcp_binds | tr ";" "\n")
for bind in $dhcp_binds
do
    #echo "$dhcp bind $bind"
    dhcp_hosts="$dhcp_hosts\ndhcp-host=$bind"
done

# Start network wait
sudo systemctl start network-online.target &> /dev/null

# Setup iptables
sudo iptables -F
sudo iptables -t nat -F
sudo iptables -t nat -A POSTROUTING -o $wlan -j MASQUERADE
sudo iptables -A FORWARD -i $wlan -o $eth -m state --state RELATED,ESTABLISHED -j ACCEPT
sudo iptables -A FORWARD -i $eth -o $wlan -j ACCEPT

# Enable ip forward
sudo sh -c "echo 1 > /proc/sys/net/ipv4/ip_forward"

# Configure net
sudo ifconfig $eth $ip_address netmask $netmask

# Remove default route created by dhcpcd
sudo ip route del 0/0 dev $eth &> /dev/null

# Configure dnsmasq
sudo systemctl stop dnsmasq
sudo rm -rf /etc/dnsmasq.d/* &> /dev/null

echo -e "interface=$eth\n\
bind-interfaces\n\
server=8.8.8.8\n\
server=8.8.4.4\n\
domain-needed\n\
bogus-priv\n\
dhcp-range=$dhcp_range_start,$dhcp_range_end,$dhcp_range_netmask,$dhcp_range_broadcast,$dhcp_time\n\
$dhcp_hosts" > /tmp/custom-dnsmasq.conf

sudo cp /tmp/custom-dnsmasq.conf /etc/dnsmasq.d/custom-dnsmasq.conf
sudo rm /tmp/custom-dnsmasq.conf
sudo systemctl start dnsmasq

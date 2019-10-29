#!/usr/bin/env python3
import signal
import socket
import sys
import os
import stat
import logging
import json
import netifaces
import re
import configparser
import subprocess
import threading
import datetime



''' Configuration
-----------------------------------'''

# Static variables
NAME						= 'PiLanShare'
TAG							= 'PiLanShare'
VERSION						= 'v0.3.1-beta'
DAEMON_PATH					= os.path.dirname(os.path.realpath(__file__))
#SOCKET_FILE_PATH			= '/tmp/pilanshare.sock'
SOCKET_FILE_PATH			= os.path.join(DAEMON_PATH, 'daemon.sock')
CONFIG_DEFAULT_FILE_PATH	= os.path.join(DAEMON_PATH, 'default.ini')
CONFIG_SAVED_FILE_PATH		= os.path.join(DAEMON_PATH, 'config.ini')
CONFIG_FILE_PATH			= '/boot/pilanshare.ini'
CONFIG_RENAMED_FILE_PATH	= '/boot/pilanshare.loaded.ini'
DNSMASQ_LEASES_FILE_PATH	= '/var/lib/misc/dnsmasq.leases'
DNSMASQ_LOG_FILE_PATH		= '/var/log/dnsmasq.log'

# Global variables
sock = None
config = None
ACTIVE =  True
LOGO_PRINT =  True
DAEMON_LOGGING_LEVEL = logging.INFO


''' Main Function
-----------------------------------'''
def main():
	global sock

	# Init configuration
	init_configuration()

	# Print Daemon Info
	logging.info(NAME + ' ' + VERSION)

	# Save Configuration
	config_save()

	# Print Logo
	print_logo()

	# Init network
	if ACTIVE:
		if config_check_iptables():
			pilanshare_run_iptables__config()
		else:
			logging.info('Configuration of iptables is disabled')
		if config_check_dnsmasq():
			config_check_dnsmasq_binds()
			pilanshare_run_dnsmasq__config()
		else:
			logging.info('Configuration of dnsmasq is disabled')

	# If socket API is enabled
	if boolean_is_true(config.get('DAEMON', 'socket_api')):
		# Handle daemon terminate
		signal.signal(signal.SIGINT, handler_daemon_terminate)

		# Setup daemon socket
		sock = setup_daemon_socket()
		handler_daemon_socket(sock)

# Init configuration function
def init_configuration():
	global config, ACTIVE, LOGO_PRINT, DAEMON_LOGGING_LEVEL

	# Load configuration file
	config = load_configuration()

	# If clear configuration
	if boolean_is_true(config.get('DAEMON', 'config_clear')):
		# Clear saved configuration
		if os.path.isfile(CONFIG_SAVED_FILE_PATH):
			os.unlink(CONFIG_SAVED_FILE_PATH)
		# Reload configuration
		config = load_configuration()
	config.remove_option('DAEMON', 'config_clear')

	# Configuration
	ACTIVE = (True if boolean_is_true(config.get('DAEMON', 'enabled')) else False)
	LOGO_PRINT = (True if boolean_is_true(config.get('DAEMON', 'daemon_print_logo')) else False)
	DAEMON_LOGGING_LEVEL = config.get('DAEMON', 'log_level').lower()
	DAEMON_LOGGING_LEVEL = (
		logging.DEBUG if DAEMON_LOGGING_LEVEL == 'debug' else
		logging.INFO if DAEMON_LOGGING_LEVEL == 'info' else
		logging.WARNING if DAEMON_LOGGING_LEVEL == 'warning' else
		logging.ERROR if DAEMON_LOGGING_LEVEL == 'error' else
		logging.CRITICAL if DAEMON_LOGGING_LEVEL == 'critical' else
		logging.INFO
	)

	# Set up logging
	logging.basicConfig(
		level=DAEMON_LOGGING_LEVEL,
		format='[' + TAG + ']' + '[%(levelname)s] %(message)s'
	)
	#logging.debug('This is a debug message')
	#logging.info('This is an info message')
	#logging.warning('This is a warning message')
	#logging.error('This is an error message')
	#logging.critical('This is a critical message')

# Load configuration function
def load_configuration():
	#config = configparser.SafeConfigParser(delimiters=('='))
	config = configparser.ConfigParser(delimiters=('='))
	config.read(CONFIG_DEFAULT_FILE_PATH)
	if os.path.isfile(CONFIG_SAVED_FILE_PATH):
		config.read(CONFIG_SAVED_FILE_PATH)
	if os.path.isfile(CONFIG_FILE_PATH):
		config.read(CONFIG_FILE_PATH)
		if boolean_is_true(config.get('DAEMON', 'config_save')):
			if os.path.isfile(CONFIG_RENAMED_FILE_PATH):
				os.unlink(CONFIG_RENAMED_FILE_PATH)
			os.rename(CONFIG_FILE_PATH, CONFIG_RENAMED_FILE_PATH)
	return config


''' Logo Functions
-----------------------------------'''

# Print Logo
def print_logo():
	if not LOGO_PRINT:
		return
	logo = ('' +
		'BBBBBBBBBBBBBBBBB\n' +
		'BBB   BBBBB   BBB\n' +
		'BB BBB  B  BBB BB\n' +
		'B BGggBB BBGggB B\n' +
		'B BGggggBGggggB B\n' +
		'BB BGggBBBGggB BB\n' +
		'BBB BBBBRBBBB BBB\n' +
		'BBB BRRrrrrrB BBB\n' +
		'BB BRRrrrrrrrB BB\n' +
		'BB BRrrrrrrrrB BB\n' +
		'B BRBBBBBBBBBrB B\n' +
		'B BRBWBBBgByBrB B\n' +
		'B BRBBBBBBBBBrB B\n' +
		'BB BRrrrrrrrrB BB\n' +
		'BB BRRrrrrrrrB BB\n' +
		'BBB BRRrrrrrB BBB\n' +
		'BBBB BBRrrBB BBBB\n' +
		'BBBBB  BBB  BBBBB\n' +
		'BBBBBBB   BBBBBBB\n' +
		'BBBBBBBBBBBBBBBBB')
	logo_console = ''
	for c in logo:
		if c == ' ':
			logo_console += '\33[100m' + '  ' + '\33[0m';
		elif c == 'B':
			logo_console += '\33[40m' + '  ' + '\33[0m';
		elif c == 'R':
			logo_console += '\33[41m' + '  ' + '\33[0m';
		elif c == 'r':
			logo_console += '\33[101m' + '  ' + '\33[0m';
		elif c == 'G':
			logo_console += '\33[42m' + '  ' + '\33[0m';
		elif c == 'g':
			logo_console += '\33[102m' + '  ' + '\33[0m';
		elif c == 'W':
			logo_console += '\33[47m' + '  ' + '\33[0m';
		elif c == 'Y':
			logo_console += '\33[43m' + '  ' + '\33[0m';
		elif c == 'y':
			logo_console += '\33[103m' + '  ' + '\33[0m';
		elif c == '\n':
			logo_console += '\n';
		else:
			logo_console += '??';
	print(logo_console)



''' Configuration functions
-----------------------------------'''

# Save configuration
def config_save():
	if boolean_is_true(config.get('DAEMON', 'config_save')):
		with open(CONFIG_SAVED_FILE_PATH, 'w') as file:
			config.write(file)
			file.close()

# Check configuration IPTABLES
def config_check_iptables():
	if not validate_hostname(config.get('IPTABLES', 'interface_source')):
		logging.error('Config is invalid (IPTABLES > interface_source)')
		config.set('IPTABLES', 'enable', 'False')
	if not validate_hostname(config.get('IPTABLES', 'interface_target')):
		logging.error('Config is invalid (IPTABLES > interface_target)')
		config.set('IPTABLES', 'enable', 'False')
	if not validate_ip_address(config.get('IPTABLES', 'netmask')):
		logging.error('Config is invalid (IPTABLES > netmask)')
		config.set('IPTABLES', 'enable', 'False')
	return boolean_is_true(config.get('IPTABLES', 'enable'))

# Check configuration DNSMASQ
def config_check_dnsmasq():
	if not validate_hostname(config.get('DNSMASQ', 'interface')):
		logging.error('Config is invalid (DNSMASQ > interface)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dns_primary')):
		logging.error('Config is invalid (DNSMASQ > dns_primary)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dns_secondary')):
		logging.error('Config is invalid (DNSMASQ > dns_secondary)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dhcp_start')):
		logging.error('Config is invalid (DNSMASQ > dhcp_start)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dhcp_end')):
		logging.error('Config is invalid (DNSMASQ > dhcp_end)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dhcp_netmask')):
		logging.error('Config is invalid (DNSMASQ > dhcp_netmask)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'dhcp_broadcast')):
		logging.error('Config is invalid (DNSMASQ > dhcp_broadcast)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_lease_time(config.get('DNSMASQ', 'dhcp_lease_time')):
		logging.error('Config is invalid (DNSMASQ > dhcp_lease_time)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_ip_address(config.get('DNSMASQ', 'router_ip_address')):
		logging.error('Config is invalid (DNSMASQ > router_ip_address)')
		config.set('DNSMASQ', 'enable', 'False')
	if not validate_domain_name(config.get('DNSMASQ', 'router_domain_name')):
		logging.error('Config is invalid (DNSMASQ > router_domain_name)')
		config.set('DNSMASQ', 'enable', 'False')
	return boolean_is_true(config.get('DNSMASQ', 'enable'))

# Check configuration DNSMASQ-BINDS
def config_check_dnsmasq_binds():
	binds_items = config.items('DNSMASQ_BINDS')
	count = 0
	errors = 0
	for name, ip_address in binds_items:
		count += 1
		if not ((validate_hostname(name) or validate_mac_address(name)) and validate_ip_address(ip_address)):
			errors += 1
			logging.error('Config is invalid (DNSMASQ_BINDS > item ' + str(count) + ')')
			#logging.error('                  ( ' + name + ' = ' + ip_address + ' )')
			config.remove_option('DNSMASQ_BINDS', name)
	return (errors > 0)

# Get configuration DNSMASQ-BINDS
def config_get_dnsmasq_binds():
	binds = {}
	binds_items = config.items('DNSMASQ_BINDS')
	for name, ip_address in binds_items:
		if (validate_hostname(name) or validate_mac_address(name)) and validate_ip_address(ip_address):
			binds[name] = ip_address
	return binds



''' Validate functions
-----------------------------------'''

def validate_ip_address(value):
	if re.match(r"^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$", value):
		return True
	return False

def validate_hostname(value):
	if re.match(r"^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$", value):
		return True
	return False

def validate_mac_address(value):
	if re.match(r"^([0-9A-Fa-f]{2}:){5}([0-9A-Fa-f]{2})$", value):
		return True
	return False

def validate_lease_time(value):
	if re.match(r"^\d+(s|m|h)$", value):
		return True
	return False

def validate_domain_name(value):
	if re.match(r"^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$", value):
		return True
	return False



''' Boolean Check functions
-----------------------------------'''

def boolean_is_true(value):
	return (value.lower() == 'true')

def boolean_is_false(value):
	return (value.lower() != 'true')



''' Handlers
-----------------------------------'''

# Handle daemon termination
def handler_daemon_terminate(sig, frame):
	logging.info('Terminating daemon...')
	if sock:
		sock.close()
	if os.path.exists(SOCKET_FILE_PATH):
		os.unlink(SOCKET_FILE_PATH)
	sys.exit(0)

# Setup daemon socket
def setup_daemon_socket():
	# Make sure the socket does not already exist
	try:
		os.unlink(SOCKET_FILE_PATH)
	except OSError:
		if os.path.exists(SOCKET_FILE_PATH):
			raise
	# Set up a socket
	sock = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
	logging.info('Setting up a socket on %s' % SOCKET_FILE_PATH)
	sock.bind(SOCKET_FILE_PATH)
	os.chmod(SOCKET_FILE_PATH, stat.S_IRUSR | stat.S_IWUSR | stat.S_IRGRP | stat.S_IWGRP | stat.S_IROTH | stat.S_IWOTH)
	logging.debug('Socket is ready')
	# Return socket
	return sock

# Handle daemon socket
def handler_daemon_socket(sock):
	logging.info('Daemon is now listening for socket connections')
	# Listen for incoming connections
	sock.listen(1)
	# Wait for a connections
	while True:
		logging.debug('Waiting for a socket connection')
		connection, client_address = sock.accept()
		try:
			logging.debug('Handling socket connection')
			# Get request data
			data = b''
			while True:
				part = connection.recv(4096)
				data += part
				if len(part) < 4096:
					break
			# Parse request data
			request = data.decode('utf-8')
			# Send response data
			logging.debug('Sending data back to the client')
			connection.sendall(handle_socket_request(request))
		finally:
			# Clean up the connection
			connection.close()

# Handle socket request
def handle_socket_request(request):
	# Parse JSON response
	try:
		request = json.loads(request)
		# Call actions handler
		response = handle_socket_action(request)
	except ValueError:
		response = {"error": "Invalid request."}
	# Return response
	#return json.dumps(response).encode();
	return json.dumps(response, indent=4).encode()

# Handle socket request
def handle_socket_action(request):
	# Version Command
	if request['action'] == 'version':
		return pilanshare_action_get_version(request)
	# Interfaces Command
	if request['action'] == 'interfaces':
		return pilanshare_action_get_interfaces(request)
	# Interfaces Stats
	if request['action'] == 'interfaces-stats':
		return pilanshare_action_get_interfaces_stats(request)
	# Arp
	if request['action'] == 'arp':
		return pilanshare_action_get_arp(request)
	# Lanshare
	if request['action'] == 'lanshare':
		return pilanshare_action_lanshare(request)
	# DNSmasq
	if request['action'] == 'dnsmasq_leases':
		return pilanshare_action_get_dnsmasq_leases(request)
	if request['action'] == 'dnsmasq_leases_count':
		return pilanshare_action_get_dnsmasq_leases_count(request)
	if request['action'] == 'dnsmasq_queries':
		return pilanshare_action_get_dnsmasq_queries(request)
	if request['action'] == 'dnsmasq_queries_count':
		return pilanshare_action_get_dnsmasq_queries_count(request)
	if request['action'] == 'dnsmasq_queries_cached':
		return pilanshare_action_get_dnsmasq_queries_cached(request)
	if request['action'] == 'dnsmasq_queries_cached_count':
		return pilanshare_action_get_dnsmasq_queries_cached_count(request)
	# System
	if request['action'] == 'system':
		return pilanshare_action_do_system(request)
	# Command
	if request['action'] == 'command':
		return pilanshare_action_run_command(request)
	# Request Error
	return {"error": "Unknown request."}



''' Actions
-----------------------------------'''

# Get Version
def pilanshare_action_get_version(request):
	logging.debug('Action get version ...')
	logging.debug('Daemon info: ' + NAME + ' ' + VERSION)
	return {
		"message": NAME + ' ' + VERSION,
		"version" : VERSION
	}

# Get Interfaces
def pilanshare_action_get_interfaces(request):
	logging.debug('Action get interfaces ...')
	interfaces = {}
	# For each interface
	count = 0
	for name in netifaces.interfaces():
		count += 1
		# Get info
		interface = netifaces.ifaddresses(name)
		interfaces[name] = {}
		if interface[netifaces.AF_LINK]:
			interfaces[name]['LINK'] = interface[netifaces.AF_LINK]
		if interface[netifaces.AF_INET]:
			interfaces[name]['INET'] = interface[netifaces.AF_INET]
		if interface[netifaces.AF_INET6]:
			interfaces[name]['INET6'] = interface[netifaces.AF_INET6]
	logging.debug('Found info for ' + str(count) + ' interfaces')
	return interfaces

# Get Interfaces Stats
def pilanshare_action_get_interfaces_stats(request):
	logging.debug('Action get interfaces stats ...')
	data = ''
	# Read stats from proc
	try:
		with open('/proc/net/dev', 'r') as file:
			data = file.read()
			file.close()
	except:
		return {"error": "Interfaces stats action failed."}
	# Parse data
	data = re.findall(r"(\S+):\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)\s*(\d+)", data)
	count = 0
	stats = {}
	for interface in data:
		count += 1
		rx = {}
		rx['bytes']			= int(interface[1])
		rx['packets']		= int(interface[2])
		rx['errs']			= int(interface[3])
		rx['drop']			= int(interface[4])
		rx['fifo']			= int(interface[5])
		rx['frame']			= int(interface[6])
		rx['compressed']	= int(interface[7])
		rx['multicast']		= int(interface[8])
		tx = {}
		tx['bytes']			= int(interface[9])
		tx['packets']		= int(interface[10])
		tx['errs']			= int(interface[11])
		tx['drop']			= int(interface[12])
		tx['fifo']			= int(interface[13])
		tx['frame']			= int(interface[14])
		tx['compressed']	= int(interface[15])
		tx['multicast']		= int(interface[16])
		stats[interface[0]] = {
			'rx' : rx,
			'tx' : tx
		}
	logging.debug('Found stats for ' + str(count) + ' interfaces')
	# Return data
	return stats

# Get ARP
def pilanshare_action_get_arp(request):
	logging.debug('Action get arp ...')
	data = ''
	# Read arp from proc
	try:
		with open('/proc/net/arp', 'r') as file:
			data = file.read()
			file.close()
	except:
		return {"error": "ARP action failed."}
	# Parse data
	data = re.findall(r"(\d+\.\d+\.\d+\.\d+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)", data)
	devices = []
	for device in data:
		if device[3] != '00:00:00:00:00:00':
			devices.append({
				'ip_address' : device[0],
				'hw_type' : device[1],
				'flags' : device[2],
				'hw_address' : device[3],
				'mask' : device[4],
				'device' : device[5]
			})
	logging.debug('Found data for ' + str(len(devices)) + ' devices')
	# Return data
	return devices

# Get dnsmasq leases
def pilanshare_action_get_dnsmasq_leases(request):
	logging.debug('Action get dnsmasq leases ...')
	data = ''
	# Read dnsmasq leases from file
	try:
		with open(DNSMASQ_LEASES_FILE_PATH, 'r') as file:
			data = file.read()
			file.close()
	except:
		return {"error": "dnsmasq leases action failed."}
	# Parse data
	data = data.splitlines()
	devices = []
	for device in data:
		if len(device) > 4:
			device = device.split(' ')
			if len(device):
				devices.append({
					'lease'       : device[0], # Expiration Time
					'mac_address' : device[1], # Link Address
					'ip_address'  : device[2], # IP Address
					'hostname'    : device[3], # Hostname 
					'client-id'   : device[4]	 # Client ID
				})
			else:
				devices.append({
					'lease'       : 0,
					'mac_address' : '',
					'ip_address'  : '',
					'hostname'    : 'Unknown',
					'client-id'   : ''
				})
	logging.debug('Found data for ' + str(len(devices)) + ' devices')
	# Return data
	return devices

# Count dnsmasq leases
def pilanshare_action_get_dnsmasq_leases_count(request):
	logging.debug('Action count dnsmasq leases ...')
	data = ''
	# Read dnsmasq leases from file
	try:
		with open(DNSMASQ_LEASES_FILE_PATH, 'r') as file:
			data = file.read()
			file.close()
	except:
		return {"error": "dnsmasq leases count action failed."}
	# Parse data
	data = data.splitlines()
	devices = 0
	for device in data:
		if len(device) > 4:
			devices += 1
	logging.debug('Found ' + str(devices) + ' devices')
	# Return data
	return {"count" : devices}

# Get dnsmasq queries
def pilanshare_action_get_dnsmasq_queries(request):
	logging.debug('Action get dnsmasq queries ...')
	# Read dnsmasq queries from file
	result = None
	if ('max' in request) and isinstance(request['max'], int) and request['max'] > 0:
		result = subprocess.run(
			'sudo grep -P " query\\[\\w+\\] \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '" | tail -n ' + str(request['max']),
			shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
		)
	else:
		result = subprocess.run(
			'sudo grep -P " query\\[\\w+\\] \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '"',
			shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
		)
	# If error
	#if result.returncode != 0:
	#	return {"error": "Get dnsmasq queries failed."}
	# Parse results
	data = re.findall(r"(\w+ \d+ \d+:\d+:\d+) [^\]]+\[\d+\]: query\[(\w+)\] (\S+) from (\S+)", result.stdout.decode('utf-8'))
	queries = []
	now = datetime.datetime.now()
	for query in data:
		date = datetime.datetime.strptime(str(now.year) + ' ' + query[0], '%Y %b %d %H:%M:%S');
		if date > now:
			date = datetime.datetime.strptime(str(now.year - 1) + ' ' + query[0], '%Y %b %d %H:%M:%S');
		queries.append({
			'type'         : query[1],
			'domain'       : query[2],
			'device'       : query[3],
			'date'         : str(date)
		})

	logging.debug('Found ' + str(len(queries)) + ' queries')
	# Return data
	return queries

# Count dnsmasq queries
def pilanshare_action_get_dnsmasq_queries_count(request):
	logging.debug('Action count dnsmasq queries ...')
	# Read dnsmasq queries from file
	result = subprocess.run(
		'grep -P -c " query\\[\\w+\\] \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '"',
		shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
	)
	# If error
	#if result.returncode != 0:
	#	return {"error": "Count dnsmasq queries failed."}
	# Parse results
	data = None
	try:
		data = int(result.stdout.decode('utf-8'))
	except ValueError:
		return {"error": "Count dnsmasq queries parsing failed."}
	# Return data
	return {"count": data}

# Get dnsmasq queries cached
def pilanshare_action_get_dnsmasq_queries_cached(request):
	logging.debug('Action get dnsmasq cached queries ...')
	# Read dnsmasq queries from file
	result = None
	if ('max' in request) and isinstance(request['max'], int) and request['max'] > 0:
		result = subprocess.run(
			'grep -P " cached \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '" | tail -n ' + str(request['max']),
			shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
		)
	else:
		result = subprocess.run(
			'grep -P " cached \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '"',
			shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
		)
	# If error
	#if result.returncode != 0:
	#	return {"error": "Get dnsmasq cached queries failed."}
	# Parse results
	data = re.findall(r" cached (\S+) from (\S+)", result.stdout.decode('utf-8'))
	queries = []
	for query in data:
		queries.append({
			'domain'       : query[0],
			'device'       : query[1]
		})

	logging.debug('Found ' + str(len(queries)) + ' cached queries')
	# Return data
	return queries

# Count dnsmasq queries
def pilanshare_action_get_dnsmasq_queries_cached_count(request):
	logging.debug('Action count dnsmasq cached queries ...')
	# Read dnsmasq queries from file
	result = subprocess.run(
		'grep -P -c " cached \\S+ from \\S+" "' + DNSMASQ_LOG_FILE_PATH + '"',
		shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT
	)
	# If error
	#if result.returncode != 0:
	#	return {"error": "Count dnsmasq cached queries failed."}
	# Parse results
	data = None
	try:
		data = int(result.stdout.decode('utf-8'))
	except ValueError:
		return {"error": "Count dnsmasq cached queries parsing failed."}
	# Return data
	return {"count": data}

# Lanshare action
def pilanshare_action_lanshare(request):
	logging.debug('Handling lanshare ...')
	# Network
	if request['method'] == 'get':
		logging.debug('Get lanshare ...')
		result = subprocess.run('ifconfig', shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
		return {
			'iptables' : {
				'enable' :					boolean_is_true(config.get('IPTABLES', 'enable')),
				'interface_source' :		config.get('IPTABLES', 'interface_source'),
				'interface_target' :		config.get('IPTABLES', 'interface_target'),
				'ip_address' :				config.get('IPTABLES', 'ip_address'),
				'netmask' :					config.get('IPTABLES', 'netmask'),
				'enable_ip_forward' :		boolean_is_true(config.get('IPTABLES', 'enable_ip_forward')),
				'remove_default_routes' :	boolean_is_true(config.get('IPTABLES', 'remove_default_routes'))
			},
			'dnsmasq' : {
				'enable' :				boolean_is_true(config.get('DNSMASQ', 'enable')),
				'interface' :			config.get('DNSMASQ', 'interface'),
				'dns_primary' :			config.get('DNSMASQ', 'dns_primary'),
				'dns_secondary' :		config.get('DNSMASQ', 'dns_secondary'),
				'dhcp_start' :			config.get('DNSMASQ', 'dhcp_start'),
				'dhcp_end' :			config.get('DNSMASQ', 'dhcp_end'),
				'dhcp_netmask' :		config.get('DNSMASQ', 'dhcp_netmask'),
				'dhcp_broadcast' :		config.get('DNSMASQ', 'dhcp_broadcast'),
				'dhcp_lease_time' :		config.get('DNSMASQ', 'dhcp_lease_time'),
				'router_ip_address' :	config.get('DNSMASQ', 'router_ip_address'),
				'router_domain_name' :	config.get('DNSMASQ', 'router_domain_name')
			},
			'dnsmasq-binds' : config_get_dnsmasq_binds()
		}

	if request['method'] == 'set':
		logging.debug('Set lanshare ...')
		# Check data
		data = request['data']
		if (
			not validate_ip_address(data['iptables']['ip_address']) or
			not validate_ip_address(data['iptables']['netmask']) or
			not validate_ip_address(data['dnsmasq']['dns_primary']) or
			not validate_ip_address(data['dnsmasq']['dns_secondary']) or
			not validate_ip_address(data['dnsmasq']['dhcp_start']) or
			not validate_ip_address(data['dnsmasq']['dhcp_end']) or
			not validate_ip_address(data['dnsmasq']['dhcp_netmask']) or
			not validate_ip_address(data['dnsmasq']['dhcp_broadcast']) or
			not validate_lease_time(data['dnsmasq']['dhcp_lease_time']) or
			not validate_ip_address(data['dnsmasq']['router_ip_address']) or
			not validate_domain_name(data['dnsmasq']['router_domain_name'])
		) :
			logging.debug('Invalid data detected')
			return {'error' : 'Invalid data were given!'}
		for name in data['dnsmasq-binds']:
			if (not validate_hostname(name) and not validate_mac_address(name)) or not validate_ip_address(data['dnsmasq-binds'][name]):
				logging.debug('Invalid bind data detected')
				return {'error' : 'Invalid data were given!'}
		# Save data
		# Save iptables data
		config.set('IPTABLES', 'enable', ('true' if data['iptables']['enable'] else 'false'))
		config.set('IPTABLES', 'interface_source', data['iptables']['interface_source'])
		config.set('IPTABLES', 'interface_target', data['iptables']['interface_target'])
		config.set('IPTABLES', 'ip_address', data['iptables']['ip_address'])
		config.set('IPTABLES', 'netmask', data['iptables']['netmask'])
		config.set('IPTABLES', 'enable_ip_forward', ('true' if data['iptables']['enable_ip_forward'] else 'false'))
		config.set('IPTABLES', 'remove_default_routes', ('true' if data['iptables']['remove_default_routes'] else 'false'))
		# Save dnsmasq data
		config.set('DNSMASQ', 'enable', ('true' if data['dnsmasq']['enable'] else 'false'))
		config.set('DNSMASQ', 'interface', data['dnsmasq']['interface'])
		config.set('DNSMASQ', 'dns_primary', data['dnsmasq']['dns_primary'])
		config.set('DNSMASQ', 'dns_secondary', data['dnsmasq']['dns_secondary'])
		config.set('DNSMASQ', 'dhcp_start', data['dnsmasq']['dhcp_start'])
		config.set('DNSMASQ', 'dhcp_end', data['dnsmasq']['dhcp_end'])
		config.set('DNSMASQ', 'dhcp_netmask', data['dnsmasq']['dhcp_netmask'])
		config.set('DNSMASQ', 'dhcp_broadcast', data['dnsmasq']['dhcp_broadcast'])
		config.set('DNSMASQ', 'dhcp_lease_time', data['dnsmasq']['dhcp_lease_time'])
		config.set('DNSMASQ', 'router_ip_address', data['dnsmasq']['router_ip_address'])
		config.set('DNSMASQ', 'router_domain_name', data['dnsmasq']['router_domain_name'])
		# Delete old binds
		binds_items = config.items('DNSMASQ_BINDS')
		for name, ip_address in binds_items:
			config.remove_option('DNSMASQ_BINDS', name)
		for name in data['dnsmasq-binds']:
			config.set('DNSMASQ_BINDS', name, data['dnsmasq-binds'][name])
		# Save configuration
		config.set('DAEMON', 'config_save', 'true')
		config_save()
		# Apply configuration
		pilanshare_run_iptables__config()
		pilanshare_run_dnsmasq__config()
		# Return
		return {'message' : 'Success!'}

	# Command not found
	return {"error": "Invalid method."}

# Do system action
def pilanshare_action_run_command(request):
	logging.debug('Running command ...')
	# Network
	if request['command'] == 'ifconfig':
		logging.debug('Executing ifconfig ...')
		result = subprocess.run('ifconfig', shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
		return {'returncode' : result.returncode, 'output': result.stdout.decode('utf-8')}
	if request['command'] == 'iwconfig':
		logging.debug('Executing iwconfig ...')
		result = subprocess.run('iwconfig', shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
		return {'returncode' : result.returncode, 'output': result.stdout.decode('utf-8')}
	# Command not found
	return {"error": "Invalid command."}

# Do system action
def pilanshare_action_do_system(request):
	logging.debug('Running system action ...')
	# System wide
	if request['sub-action'] == 'reboot':
		timer = 5
		threading.Timer(timer, pilanshare_action_run_system, ['reboot']).start()
		return {"message": "System will reboot in " + str(timer) + " seconds."}
	if request['sub-action'] == 'shutdown':
		timer = 5
		threading.Timer(timer, pilanshare_action_run_system, ['shutdown']).start()
		return {"message": "System will shutdown in " + str(timer) + " seconds."}
	# dnsmasq
	if request['sub-action'] == 'dnsmasq-start':
		success = pilanshare_action_run_system('dnsmasq-start')
		return {"message": ('Dnsmasq was started.' if success else 'Failed to start Dnsmasq.')}
	if request['sub-action'] == 'dnsmasq-stop':
		success = pilanshare_action_run_system('dnsmasq-stop')
		return {"message": ('Dnsmasq was stopped.' if success else 'Failed to stop Dnsmasq.')}
	if request['sub-action'] == 'dnsmasq-restart':
		success = pilanshare_action_run_system('dnsmasq-restart')
		return {"message": ('Dnsmasq was restarted.' if success else 'Failed to restart Dnsmasq.')}
	# Sub action not found
	return {"error": "Invalid sub-action."}

# Do system action
def pilanshare_action_run_system(action):
	# System wide
	if action == 'reboot':
		logging.debug('Rebooting system ...')
		subprocess.run('shutdown -r now', shell=True, check=False)
		return True
	if action == 'shutdown':
		logging.debug('Shutting down system ...')
		subprocess.run('shutdown -h now', shell=True, check=False)
		return True
	# dnsmasq
	if action == 'dnsmasq-start':
		logging.debug('Starting dnsmasq ...')
		r = subprocess.run('systemctl start dnsmasq', shell=True, check=False)
		return (r.returncode == 0)
	if action == 'dnsmasq-stop':
		logging.debug('Stopping dnsmasq ...')
		r = subprocess.run('systemctl stop dnsmasq', shell=True, check=False)
		return (r.returncode == 0)
	if action == 'dnsmasq-restart':
		logging.debug('Restarting dnsmasq ...')
		r = subprocess.run('systemctl restart dnsmasq', shell=True, check=False)
		return (r.returncode == 0)
	# Action not found
	return False


# Run iptables forwarding
def pilanshare_run_iptables(source, target, ip_address, netmask, ipForward, removeRoutes):
	logging.info('Running iptables configuration ...')
	logging.debug('\tsource       : ' + str(source))
	logging.debug('\ttarget       : ' + str(target))
	logging.debug('\tip_address   : ' + str(ip_address))
	logging.debug('\tnetmask      : ' + str(netmask))
	logging.debug('\tipForward    : ' + str(ipForward))
	logging.debug('\tremoveRoutes : ' + str(removeRoutes))
	try:
		# Start network wait
		subprocess.check_output('systemctl start network-online.target', shell=True)
		# Setup iptables
		subprocess.check_output('iptables -F', shell=True)
		subprocess.check_output('iptables -t nat -F', shell=True)
		subprocess.check_output('iptables -t nat -A POSTROUTING -o ' + source + ' -j MASQUERADE', shell=True)
		subprocess.check_output('iptables -A FORWARD -i ' + source + ' -o ' + target + ' -m state --state RELATED,ESTABLISHED -j ACCEPT', shell=True)
		subprocess.check_output('iptables -A FORWARD -i ' + target + ' -o ' + source + ' -j ACCEPT', shell=True)
		# Enable IP forward
		if ipForward:
			subprocess.check_output('echo 1 > /proc/sys/net/ipv4/ip_forward', shell=True)
		# Configure net
		subprocess.check_output('ifconfig ' + target + ' ' + ip_address + ' netmask ' + netmask + '', shell=True)
		# Remove default route created by dhcpcd
		if removeRoutes:
			subprocess.run('ip route del 0/0 dev ' + target + '', shell=True, check=False)
	except subprocess.CalledProcessError as e:
		logging.error(e.output)
		return False
	return True

def pilanshare_run_iptables__config():
	if boolean_is_false(config.get('IPTABLES', 'enable')):
		return False
	return pilanshare_run_iptables(
		config.get('IPTABLES', 'interface_source'),
		config.get('IPTABLES', 'interface_target'),
		config.get('IPTABLES', 'ip_address'),
		config.get('IPTABLES', 'netmask'),
		boolean_is_true(config.get('IPTABLES', 'enable_ip_forward')),
		boolean_is_true(config.get('IPTABLES', 'remove_default_routes'))
	)

# Run dnsmasq configuration
def pilanshare_run_dnsmasq(interface, dhcp_start, dhcp_end, dhcp_netmask, dhcp_broadcast, dhcp_lease_time, dns_primary, dns_secondary, router_ip_address, router_domain_name, dhcp_hosts, clear_leases=True):
	logging.info('Running dnsmasq configuration ...')
	logging.debug('\tinterface          : ' + str(interface))
	logging.debug('\tdhcp_start         : ' + str(dhcp_start))
	logging.debug('\tdhcp_end           : ' + str(dhcp_end))
	logging.debug('\tdhcp_netmask       : ' + str(dhcp_netmask))
	logging.debug('\tdhcp_broadcast     : ' + str(dhcp_broadcast))
	logging.debug('\tdhcp_lease_time    : ' + str(dhcp_lease_time))
	logging.debug('\tdns_primary        : ' + str(dns_primary))
	logging.debug('\tdns_secondary      : ' + str(dns_secondary))
	logging.debug('\trouter_ip_address  : ' + str(router_ip_address))
	logging.debug('\trouter_domain_name : ' + str(router_domain_name))
	logging.debug('\tdhcp_hosts         : ' + str(dhcp_hosts))
	logging.debug('\tclear_leases       : ' + str(clear_leases))
	# Generate DNS servers config part
	config_dns_servers = ''
	if dns_primary:
		config_dns_servers += 'server=' + dns_primary + '\n'
	if dns_secondary:
		config_dns_servers += 'server=' + dns_secondary + '\n'
	# Generate DHCP hosts config part
	config_dhcp_hosts = ''
	for name in dhcp_hosts:
		config_dhcp_hosts += 'dhcp-host=' + name + ',' + dhcp_hosts[name] + ',infinite\n'
	# Generate DHCP range config part
	config_dhcp_range = 'dhcp-range=' + dhcp_start + ',' + dhcp_end + ',' + dhcp_netmask + ',' + dhcp_broadcast + ',' + dhcp_lease_time + '\n'
	# Generate router address bind to domain
	config_router_domain_name = '' if not router_domain_name else 'address=/' + router_domain_name + '/' + router_ip_address + '\n'
	# Generate config
	config = ('' +
		'interface=' + interface + '\n' +
		'bind-interfaces\n' +
		config_dns_servers +
		'domain-needed\n' +
		'bogus-priv\n' +
		config_router_domain_name +
		'dhcp-option=option:router,' + router_ip_address + '\n' +
		config_dhcp_range +
		config_dhcp_hosts +
		'log-dhcp\n' +
		'log-queries\n' +
		'log-facility=' + DNSMASQ_LOG_FILE_PATH + '\n')
	# Clear old config
	#if os.path.exists(DNSMASQ_LOG_FILE_PATH):
	#	os.remove(DNSMASQ_LOG_FILE_PATH)
	# Create temporary config
	with open('/tmp/custom-dnsmasq.conf', 'w') as file:
		file.write(config)
	# Stop dnsmasq service
	subprocess.check_output('systemctl stop dnsmasq', shell=True)
	# Clear dnsmasq
	subprocess.check_output('rm -rf /etc/dnsmasq.d/*', shell=True)
	# Copy dnsmasq config
	subprocess.check_output('cp /tmp/custom-dnsmasq.conf /etc/dnsmasq.d/custom-dnsmasq.conf', shell=True)
	# Clear dnsmasq leases
	if os.path.isfile(DNSMASQ_LEASES_FILE_PATH):
		os.unlink(DNSMASQ_LEASES_FILE_PATH)
	# Start dnsmasq service
	subprocess.check_output('systemctl start dnsmasq', shell=True)
	# Remove temporary config
	subprocess.check_output('rm /tmp/custom-dnsmasq.conf', shell=True)
	return True

def pilanshare_run_dnsmasq__config(clear_leases=True):
	if boolean_is_false(config.get('DNSMASQ', 'enable')):
		return False
	return pilanshare_run_dnsmasq(
		config.get('DNSMASQ', 'interface'),
		config.get('DNSMASQ', 'dhcp_start'),
		config.get('DNSMASQ', 'dhcp_end'),
		config.get('DNSMASQ', 'dhcp_netmask'),
		config.get('DNSMASQ', 'dhcp_broadcast'),
		config.get('DNSMASQ', 'dhcp_lease_time'),
		config.get('DNSMASQ', 'dns_primary'),
		config.get('DNSMASQ', 'dns_secondary'),
		config.get('DNSMASQ', 'router_ip_address'),
		config.get('DNSMASQ', 'router_domain_name'),
		config_get_dnsmasq_binds(),
		clear_leases
	)



''' Script start
-----------------------------------'''
if __name__ == "__main__":
	main()

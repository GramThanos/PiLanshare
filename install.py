#!/usr/bin/env python3
import os
import re
import sys
import getopt
import shutil
import logging
import subprocess
import urllib.error
import urllib.request



''' Configuration
-----------------------------------'''

# Info
NAME = 'PiLanShare'
TAG = 'PiLanShare'
VERSION = 'v0.3.0-beta'
AUTHOR = 'GramThanos'
AUTHOR_GITHUB = 'https://github.com/GramThanos'
GITHUB_URL = 'https://github.com/GramThanos/PiLanshare/'

# Default parameters
SCRIPT_PATH = os.path.dirname(os.path.realpath(__file__))
VERBOSE = False
INSTALLATION_PATH = '/etc/pilanshare'
WEBUI_INSTALLATION_PATH = '/var/www/html/pilanshare'
IGNORE_VERSION = False
FORCE_WEBINSTALL = False
WEBINSTALL_DAEMON_URL = 'https://raw.githubusercontent.com/GramThanos/PiLanshare/master/daemon'
WEBINSTALL_WEBUI_URL = 'https://github.com/GramThanos/PiLanshare/releases/download/v0.3.0/PiLanshare_WebUI.zip'
RUN_UNINSTALL = False

# Global Variables
downloaded_content_daemon_py = ''
downloaded_content_default_ini = ''
downloaded_webui_path = ''



''' Main Function
-----------------------------------'''
def main():
	# Parse arguments
	parse_script_arguments()
	#print(
	#	'VERBOSE : ' + str(VERBOSE) + '\n' +
	#	'INSTALLATION_PATH : ' + str(INSTALLATION_PATH) + '\n' +
	#	'IGNORE_VERSION : ' + str(IGNORE_VERSION) + '\n' +
	#	'FORCE_WEBINSTALL : ' + str(FORCE_WEBINSTALL) + '\n' +
	#	'WEBINSTALL_DAEMON_URL : ' + str(WEBINSTALL_DAEMON_URL) + '\n'
	#	'WEBINSTALL_WEBUI_URL : ' + str(WEBINSTALL_WEBUI_URL) + '\n'
	#)
	#sys.exit()

	# Print logo
	print_logo()

	# Set up logging
	logging.basicConfig(
		level=logging.DEBUG if VERBOSE else logging.INFO,
		format='[%(levelname)s] %(message)s'
	)

	# Print Daemon Info
	logging.info(NAME + ' ' + VERSION +' by ' + AUTHOR)
	logging.info(GITHUB_URL)

	if RUN_UNINSTALL:
		logging.info('Uninstall ' + NAME)
		# Remove any installation
		remove_installation();
	else:
		logging.info('Install ' + NAME)
		# Check if admin
		require_admin_rights()
		# Check old installation
		prepare_check_installation()
		# Install dependencies
		install_dependencies()
		# Download files before installation
		prepare_installation()
		# Remove any old installation
		remove_installation();
		# Install PiLanShare
		run_installation()

def parse_script_arguments():
	global VERBOSE, INSTALLATION_PATH, IGNORE_VERSION, FORCE_WEBINSTALL, WEBINSTALL_DAEMON_URL, WEBINSTALL_WEBUI_URL, RUN_UNINSTALL

	try:
		opts, args = getopt.getopt(sys.argv[1:], 'vhiw', ['verbose', 'help', 'install_path=', 'webui_install_path=', 'ignore_version', 'force_webinstall', 'webinstall_daemon_url=', 'webinstall_webui_url=', 'uninstall'])
	except getopt.GetoptError as err:
		print(err)
		show_script_usage()
		sys.exit(2)

	for o, a in opts:
		if o == '-v' or o == '--verbose':
			VERBOSE = True
		elif o in ('-h', '--help'):
			show_script_usage()
			sys.exit()
		elif o in ('-i', '--install_path'):
			INSTALLATION_PATH = a
		elif o in ('-w', '--webui_install_path'):
			WEBUI_INSTALLATION_PATH = a
		elif o in ('--ignore_version'):
			IGNORE_VERSION = True
		elif o in ('--force_webinstall'):
			FORCE_WEBINSTALL = True
		elif o in ('--webinstall_daemon_url'):
			WEBINSTALL_DAEMON_URL = a
		elif o in ('--webinstall_webui_url'):
			WEBINSTALL_WEBUI_URL = a
		elif o in ('--uninstall'):
			RUN_UNINSTALL = True
		else:
			assert False, "Unhandled option"
	# ...

def show_script_usage():
	print(
		'Usage: install.py [ARGUMENTS]...\n'
		'Install ' + NAME + ' ' + VERSION + '\n'
		'Github ' + GITHUB_URL + '\n'
		'\n'
		'Optional arguments.\n'
		'  -v                         Run in verbose mode.\n'
		'  -h, --help                 Prints this message.\n'
		'  -i, --install_path         Path to install daemon.\n'
		'                             Default path ' + INSTALLATION_PATH + '\n'
		'  -w, --webui_install_path   Path to install webui.\n'
		'                             Default path ' + WEBUI_INSTALLATION_PATH + '\n'
		'  --ignore_version           Ignore version mismatch errors.\n'
		'  --force_webinstall         Force install from web (ignore any local files).\n'
		'  --webinstall_daemon_url    URL of the folder with the daemon\'s files.\n'
		'                             Default URL ' + WEBINSTALL_DAEMON_URL + '\n'
		'  --webinstall_webui_url     URL of the WebUI files zip.\n'
		'                             Default URL ' + WEBINSTALL_WEBUI_URL + '\n'
		'  --uninstall                Uninstall daemon.\n'
		'\n'
		'WebUI installation is not scripted yet.\n'
		'\n'
		'By ' + AUTHOR + ' (' + AUTHOR_GITHUB + ')'
	)


''' Logo Functions
-----------------------------------'''

# Print Logo
def print_logo():
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



''' Help Functions
-----------------------------------'''

def throw_error(error):
	if error:
		logging.critical(error)
	logging.critical('Failed!')
	sys.exit(1)

def run_command(command):
	return subprocess.run(command, shell=True, check=False, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)

def run_command_assert(command, error):
	logging.debug(command)
	result = run_command(command)
	if result.returncode != 0:
		throw_error(error)
	return result

def get_url_content(url):
	content = ''
	try:
		req = urllib.request.Request(url)
		with urllib.request.urlopen(req) as response:
			content = response.read()
	except urllib.error.URLError as e:
		return None
	else:
		return content.decode('utf-8')

def query_yes_no(question, default="yes"):
	"""
	https://stackoverflow.com/a/3041990/3709257
	Ask a yes/no question via input() and return their answer.

	"question" is a string that is presented to the user.
	"default" is the presumed answer if the user just hits <Enter>.
		It must be "yes" (the default), "no" or None (meaning
		an answer is required of the user).
	
	The "answer" return value is True for "yes" or False for "no".
	"""
	valid = {"yes": True, "y": True, "ye": True, "no": False, "n": False}
	if default is None:
		prompt = " [y/n] "
	elif default == "yes":
		prompt = " [Y/n] "
	elif default == "no":
		prompt = " [y/N] "
	else:
		raise ValueError("invalid default answer: '%s'" % default)

	while True:
		sys.stdout.write(question + prompt)
		choice = input().lower()
		if default is not None and choice == '':
			return valid[default]
		elif choice in valid:
			return valid[choice]
		else:
			sys.stdout.write("Please respond with 'yes' or 'no' (or 'y' or 'n').\n")



''' Installation Functions
-----------------------------------'''

def require_admin_rights():
	if os.getuid() != 0:
		logging.critical('You need to run the installation with admin rights!')
		logging.critical('Maybe try again with the sudo command in front?')
		throw_error('')

def install_dependencies():
	# Check if netifaces installed
	install_netifaces = False
	try:
		import netifaces
	except ImportError:
		install_netifaces = True
	# Check if iptables is installed
	install_iptables = False
	result = run_command('iptables --version')
	if result.returncode != 0:
		install_iptables = True
	# Check if dnsmasq is installed
	install_dnsmasq = False
	result = run_command('dnsmasq --version')
	if result.returncode != 0:
		install_dnsmasq = True
	# Install if needed
	if install_netifaces or install_iptables or install_dnsmasq:
		logging.info('Installing dependencies ...')
		logging.debug('Updating repositories ...')
		run_command_assert('apt update', 'Failed to run apt update')
		if install_netifaces:
			logging.debug('Installing python3-netifaces ...')
			run_command_assert('apt install python3-netifaces', 'Failed to install netifaces!')
		if install_iptables:
			logging.debug('Installing iptables ...')
			run_command_assert('apt install iptables', 'Failed to install iptables!')
		if install_dnsmasq:
			logging.debug('Installing dnsmasq ...')
			run_command_assert('apt install dnsmasq', 'Failed to install dnsmasq!')
	else:
		logging.info('No dependencies to install.')

def prepare_check_installation():
	# If installation folder exists
	if os.path.isdir(INSTALLATION_PATH):
		# Get installed version
		version_path = os.path.join(INSTALLATION_PATH, 'version')
		version = 'unknown'
		if os.path.isfile(version_path):
			with open(version_path, 'r') as file:
				version = file.read()
				file.close()
			if version == VERSION:
				if not query_yes_no('The ' + VERSION + ' is already installed!\nAre you sure you want to continue?', 'yes'):
					logging.info('Installation was canceled.')
					sys.exit(1)

def prepare_installation():
	global downloaded_content_daemon_py
	global downloaded_content_default_ini
	global downloaded_webui_path
	logging.info('Preparing installation ...')
	# Check if there are daemon files in local path
	local_daemon_py_path = os.path.join(SCRIPT_PATH, 'daemon', 'daemon.py')
	local_default_ini_path = os.path.join(SCRIPT_PATH, 'daemon', 'default.ini')
	if not FORCE_WEBINSTALL and os.path.isfile(local_daemon_py_path) and os.path.isfile(local_default_ini_path):
		logging.debug('Loading local daemon files ...')
		try:
			with open(local_daemon_py_path, 'r') as file:
				downloaded_content_daemon_py = file.read()
				file.close()
			with open(local_default_ini_path, 'r') as file:
				downloaded_content_default_ini = file.read()
				file.close()
		except:
			throw_error('Failed to read local daemon files!')
	# Get script from web
	else:
		# Daemon script
		logging.debug('Downloading daemon script ...')
		downloaded_content_daemon_py = get_url_content(WEBINSTALL_DAEMON_URL + '/daemon.py')
		if downloaded_content_daemon_py == None:
			throw_error('Failed to download daemon script!')
		# Check if version mismatch
		daemon_version = re.findall(r"VERSION\s*=\s*'([^']+)'", downloaded_content_daemon_py)
		if not IGNORE_VERSION and daemon_version[0] != VERSION:
			throw_error('The version of the daemon script does not match with the install script version!')
		# Default configuration
		logging.debug('Downloading default daemon configuration ...')
		downloaded_content_default_ini = get_url_content(WEBINSTALL_DAEMON_URL + '/default.ini')
		if downloaded_content_default_ini == None:
			throw_error('Failed to download default daemon configuration!')

def remove_installation():
	# Stop any installation
	logging.debug('Stopping pilanshare.service ...')
	run_command('systemctl stop pilanshare.service')
	# If installation folder exists
	if os.path.isdir(INSTALLATION_PATH):
		# Print already installed
		version_path = os.path.join(INSTALLATION_PATH, 'version')
		version = 'unknown'
		if os.path.isfile(version_path):
			with open(version_path, 'r') as file:
				version = file.read()
				file.close()
		if not RUN_UNINSTALL:
			logging.warning(NAME + ' ' + version + ' is already installed!')
		else:
			logging.info(NAME + ' ' + version + ' will be removed!')
		# Save configuration
		if not RUN_UNINSTALL:
			custom_config = ''
			config_path = os.path.join(INSTALLATION_PATH, 'config.ini')
			if os.path.isfile(config_path):
				with open(config_path, 'r') as file:
					custom_config = file.read()
					file.close()
				if custom_config:
					logging.info('Custom configuration will be saved.')
		# Remove old installation
		shutil.rmtree(INSTALLATION_PATH, ignore_errors=True)
		if not RUN_UNINSTALL:
			# Create installation folder
			os.mkdir(INSTALLATION_PATH)
			# Save custom configuration
			with open(config_path, 'w') as file:
				file.write(custom_config)
				file.close()
		if not RUN_UNINSTALL:
			logging.warning(NAME + ' ' + version + ' was removed!')
		else:
			logging.info(NAME + ' ' + version + ' was removed!')
	# Uninstall command but no folder was found
	elif RUN_UNINSTALL:
		logging.warning(NAME + ' installation was not found!')

def run_installation():
	logging.info('Installing ...')
	# If installation folder does not exist
	if not os.path.isdir(INSTALLATION_PATH):
		# Create installation folder
		os.mkdir(INSTALLATION_PATH)
	# Print version
	version_path = os.path.join(INSTALLATION_PATH, 'version')
	with open(version_path, 'w') as file:
		file.write(VERSION)
		file.close()
	os.chmod(version_path, 0o644)
	logging.debug('Version file was created.')
	# Save daemon script
	daemon_path = os.path.join(INSTALLATION_PATH, 'daemon.py')
	with open(daemon_path, 'w') as file:
		file.write(downloaded_content_daemon_py)
		file.close()
	os.chmod(daemon_path, 0o700)
	logging.debug('Daemon script was created.')
	# Save default configuration
	config_path = os.path.join(INSTALLATION_PATH, 'default.ini')
	with open(config_path, 'w') as file:
		file.write(downloaded_content_default_ini)
		file.close()
	os.chmod(config_path, 0o644)
	logging.debug('Default configuration was created.')
	# Save service
	service_path = os.path.join('/lib/systemd/system/pilanshare.service')
	with open(service_path, 'w') as file:
		file.write((
			'[Unit]\n' +
			'Description=PiLanshare Daemon\n' +
			'After=network.target\n' +
			'\n' +
			'[Service]\n' +
			'Type=idle\n' +
			'ExecStart=/usr/bin/python3 "' + daemon_path + '"\n' +
			'\n' +
			'[Install]\n' +
			'WantedBy=multi-user.target\n'
		))
		file.close()
	os.chmod(service_path, 0o644)
	logging.debug('Service pilanshare.service was created.')
	# Start service
	run_command('systemctl daemon-reload')
	run_command('systemctl enable pilanshare.service')
	run_command('systemctl start pilanshare.service')
	logging.debug('Service pilanshare.service was started.')
	# Done
	logging.info(NAME + ' ' + VERSION + ' was installed!')



''' Script start
-----------------------------------'''
if __name__ == "__main__":
	main()

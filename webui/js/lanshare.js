/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * Lanshare Script
 */

var LANShare = {

	interfaces : null,

	init : function() {
		this.init_events();

		let config = null;
		let interfaces = null;
		this.get_lanshare_data((data, error) => {
			if (error) {
				$('#loading').find('span:eq(0)').text(error);
				$('#loading').show();
				$('#content').hide();
				return;
			}
			config = data;
			if (interfaces) this.init_ui(config, interfaces);
		});
		this.get_interfaces((data, error) => {
			if (error) {
				$('#loading').find('span:eq(0)').text(error);
				$('#loading').show();
				$('#content').hide();
				return;
			}
			interfaces = data;
			if (config) this.init_ui(config, interfaces);
		});
	},

	init_ui : function(config, interfaces) {
		this.interfaces = interfaces;
		let element;

		// iptables
		element = $('#iptables_interface_source');
		element.empty();
		for (let i = 0; i < interfaces.length; i++) {
			element.append($('<option></option>').text(interfaces[i]).val(interfaces[i]));
		}
		element.val(config.iptables.interface_source);
		if (!interfaces.includes(config.iptables.interface_source)) {
			//console.log('Error!', config.iptables.interface_source, 'not in', interfaces);
			jsNotify.warning('The interface name of the sharing source was not found.', {time2live : 10*1000});
		}

		element = $('#iptables_interface_target');
		element.empty();
		for (let i = 0; i < interfaces.length; i++) {
			element.append($('<option></option>').text(interfaces[i]).val(interfaces[i]));
		}
		element.val(config.iptables.interface_target);
		if (!interfaces.includes(config.iptables.interface_target)) {
			//console.log('Error!', config.iptables.interface_target, 'not in', interfaces);
			jsNotify.warning('The interface name of the sharing target was not found.', {time2live : 10*1000});
		}

		$('#iptables_enable_badge').text(config.iptables.enable ? 'enabled' : 'disabled');
		$('#iptables_enable_badge').removeClass('badge-secondary').addClass(config.iptables.enable ? 'badge-success' : 'badge-danger');
		$('#iptables_enable_' + (config.iptables.enable ? 'true' : 'false')).prop('checked', true);

		$('#iptables_ip_address').val(config.iptables.ip_address);
		$('#iptables_netmask').val(config.iptables.netmask);
		$('#iptables_enable_ip_forward').prop('checked', config.iptables.enable_ip_forward);
		$('#iptables_remove_default_routes').prop('checked', config.iptables.remove_default_routes);
		
		// dnsmasq
		
		$('#dnsmasq_enable_badge').text(config.dnsmasq.enable ? 'enabled' : 'disabled');
		$('#dnsmasq_enable_badge').removeClass('badge-secondary').addClass(config.dnsmasq.enable ? 'badge-success' : 'badge-danger');
		$('#dnsmasq_enable_' + (config.iptables.enable ? 'true' : 'false')).prop('checked', true);

		$('#dnsmasq_interface').val(config.dnsmasq.interface);
		$('#dnsmasq_dns_primary').val(config.dnsmasq.dns_primary);
		$('#dnsmasq_dns_secondary').val(config.dnsmasq.dns_secondary);
		$('#dnsmasq_dhcp_start').val(config.dnsmasq.dhcp_start);
		$('#dnsmasq_dhcp_end').val(config.dnsmasq.dhcp_end);
		$('#dnsmasq_dhcp_netmask').val(config.dnsmasq.dhcp_netmask);
		$('#dnsmasq_dhcp_broadcast').val(config.dnsmasq.dhcp_broadcast);
		$('#dnsmasq_dhcp_lease_time').val(config.dnsmasq.dhcp_lease_time);
		$('#dnsmasq_router_ip_address').val(config.dnsmasq.router_ip_address);
		$('#dnsmasq_router_domain_name').val(config.dnsmasq.router_domain_name);

		// Check DNS
		this.check_dns();

		// dnsmasq binds
		for (let id in config['dnsmasq-binds']) {
			if (config['dnsmasq-binds'].hasOwnProperty(id)) {
				this.add_bind_slot(id, config['dnsmasq-binds'][id]);
			}
		}
		this.add_bind_slot();
	},

	init_events : function() {
		let ip_checks = function(id) {
			$(id).on('change', function() {
				this.value = this.value.trim();
				if (validate.ip_address(this.value)) {
					$(this).removeClass('is-invalid');
				}
				else {
					$(this).addClass('is-invalid');
				}
			});
		}

		$('#iptables_interface_target').on('change', function() {
			$('#dnsmasq_interface').val(this.value);
		});
		$('#iptables_ip_address').on('change', function() {
			if (validate.ip_address(this.value.trim()))
				$('#dnsmasq_router_ip_address').val(this.value.trim());
		});

		ip_checks('#iptables_ip_address');
		ip_checks('#iptables_netmask');
		ip_checks('#dnsmasq_dns_primary');
		ip_checks('#dnsmasq_dns_secondary');
		ip_checks('#dnsmasq_dhcp_start');
		ip_checks('#dnsmasq_dhcp_end');
		ip_checks('#dnsmasq_dhcp_netmask');
		ip_checks('#dnsmasq_dhcp_broadcast');
		ip_checks('#dnsmasq_router_ip_address');

		$('#dnsmasq_dns_primary').on('change', () => {this.check_dns();});
		$('#dnsmasq_dns_secondary').on('change', () => {this.check_dns();});


		$('#public-dns-servers').on('change', function() {
			let option = $(this).find(":selected");
			if (option.data('primary'))
				$('#dnsmasq_dns_primary').val(option.data('primary'));
			if (option.data('secondary'))
				$('#dnsmasq_dns_secondary').val(option.data('secondary'));
		});

		$('#bind-add').click(() => {
			this.add_bind_slot();
			window.scrollTo(0,document.body.scrollHeight);
		});
		$('#apply').click(() => {
			this.apply();
		});
		$('#reset').click(() => {
			this.reload();
		});
	},

	add_bind_slot : function(id=false, ip=false) {
		let slot = $('#bind-dummy').clone().removeAttr('id').addClass('bind-slot').show();
		// Init values
		if (id) slot.find('input:eq(0)').val(id);
		if (ip) slot.find('input:eq(1)').val(ip);
		// Add checks
		slot.find('input:eq(0)').on('change', function() {
			this.value = this.value.trim();
			if (validate.hostname(this.value) || validate.mac_address(this.value)) {
				$(this).removeClass('is-invalid');
			}
			else {
				$(this).addClass('is-invalid');
			}
		});
		slot.find('input:eq(1)').on('change', function() {
			this.value = this.value.trim();
			if (validate.ip_address(this.value)) {
				$(this).removeClass('is-invalid');
			}
			else {
				$(this).addClass('is-invalid');
			}
		});
		slot.find('button:eq(0)').on('click', function() {
			slot.remove()
		});
		// Insert
		$('#binds-wrapper').append(slot);
	},

	get_interfaces : function(callback) {
		$.ajax({url: this.link({'interfaces':null}), dataType: 'json'})
		.done((json) => {
			// Check for errors
			if (json.error) return callback(false, json.error);

			let interfaces = [];
			for (let name in json) {
				if (json.hasOwnProperty(name)) {
					interfaces.push(name);
				}
			}

			return callback(interfaces, false);
		})
		.fail(() => {
			// Show error
			 return callback(false, 'Connection failed!');
		});
	},

	get_lanshare_data : function(callback) {
		$.ajax({url: this.link({'lanshare':null}), dataType: 'json'})
		.done((json) => {
			// Check for errors
			if (json.error) return callback(false, json.error);
			return callback(json, false);
		})
		.fail(() => {
			// Show error
			 return callback(false, 'Connection failed!');
		});
	},

	check_dns : function() {
		let primary = $('#dnsmasq_dns_primary').val();
		let secondary = $('#dnsmasq_dns_secondary').val();

		let value = 'custom';
		let public = $('#public-dns-servers').find('option');
		for (var i = public.length - 1; i >= 0; i--) {
			if (public[i].dataset.primary == primary && public[i].dataset.secondary == secondary) {
				value = public[i].value;
				break;
			}
		}
		$('#public-dns-servers').val(value);
	},

	reload : function() {
		document.location.href = 'lanshare.php';
	},

	apply : function() {
		let config = {
			'iptables' : {
				'enable' :					$('#iptables_enable_true').is(':checked'),
				'interface_source' :		$('#iptables_interface_source').val(),
				'interface_target' :		$('#iptables_interface_target').val(),
				'ip_address' :				$('#iptables_ip_address').val(),
				'netmask' :					$('#iptables_netmask').val(),
				'enable_ip_forward' :		$('#iptables_enable_ip_forward').is(':checked'),
				'remove_default_routes' :	$('#iptables_remove_default_routes').is(':checked')
			},
			'dnsmasq' : {
				'enable' :					$('#dnsmasq_enable_true').is(':checked'),
				'interface' :				$('#dnsmasq_interface').val(),
				'dns_primary' :				$('#dnsmasq_dns_primary').val(),
				'dns_secondary' :			$('#dnsmasq_dns_secondary').val(),
				'dhcp_start' :				$('#dnsmasq_dhcp_start').val(),
				'dhcp_end' :				$('#dnsmasq_dhcp_end').val(),
				'dhcp_netmask' :			$('#dnsmasq_dhcp_netmask').val(),
				'dhcp_broadcast' :			$('#dnsmasq_dhcp_broadcast').val(),
				'dhcp_lease_time' :			$('#dnsmasq_dhcp_lease_time').val(),
				'router_ip_address' :		$('#dnsmasq_router_ip_address').val(),
				'router_domain_name' :		$('#dnsmasq_router_domain_name').val()
			},
			'dnsmasq-binds' : {}
		};

		let slots = $('#binds-wrapper').find('.bind-slot');
		for (let i = 0; i < slots.length; i++) {
			let id = $(slots[i]).find('input:eq(0)').val();
			let ip = $(slots[i]).find('input:eq(1)').val();
			if (id.length != 0 && ip.length != 0) {
				config['dnsmasq-binds'][id] = ip;
			}
		}
		
		// Run checks
		if (
			!this.interfaces.includes(config.iptables.interface_source) ||
			!this.interfaces.includes(config.iptables.interface_target) ||
			!validate.ip_address(config.iptables.ip_address) ||
			!validate.ip_address(config.iptables.netmask) ||
			!this.interfaces.includes(config.dnsmasq.interface) ||
			!validate.ip_address(config.dnsmasq.dns_primary) ||
			!validate.ip_address(config.dnsmasq.dns_secondary) ||
			!validate.ip_address(config.dnsmasq.dhcp_start) ||
			!validate.ip_address(config.dnsmasq.dhcp_end) ||
			!validate.ip_address(config.dnsmasq.dhcp_netmask) ||
			!validate.ip_address(config.dnsmasq.dhcp_broadcast) ||
			!validate.lease_time(config.dnsmasq.dhcp_lease_time) ||
			!validate.ip_address(config.dnsmasq.router_ip_address) ||
			!validate.domain_name(config.dnsmasq.router_domain_name)
		) {
			jsNotify.danger('Please correct the invalid values first.', {time2live : 10*1000});
			return;
		}

		for (let id in config['dnsmasq-binds']) {
			if (config['dnsmasq-binds'].hasOwnProperty(id)) {
				if ((!validate.hostname(id) && !validate.mac_address(id)) || !validate.ip_address(config['dnsmasq-binds'][id])) {
					jsNotify.danger('Please correct the invalid values first.', {time2live : 10*1000});
					return;
				}
			}
		}

		// Post data
		$('#applyModal').modal('show');
		setTimeout(() => {
			$.ajax({
				type: 'POST',
				url: this.link({'lanshare':null}),
				data : JSON.stringify(config),
				contentType: 'application/json',
				dataType: 'json'
			})
			.done((json) => {
				// Check for errors
				if (json.error) {
					$('#applyModal').modal('hide');
					jsNotify.danger(json.error, {time2live : 10*1000});
					return;
				}
				this.reload();
			})
			.fail(() => {
				// Show error
				$('#applyModal').modal('hide');
				jsNotify.danger('Connection failed!', {time2live : 10*1000});
			});
		}, 500);
	},

	link : function(x){
		let link = 'ajax.php';
		let front = '?';
		for (let i in x) {
			link += front + i + (x[i] !== null ? '=' + x[i] : '');
			if (front === '?') front = '&';
		}
		return link + (API_TOKEN ? front + 'token=' + API_TOKEN : '');
	}
}

// Validations
var validate = {
	ip_address : function(value) {
		return (/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/).test(value);
	},
	hostname : function(value) {
		return (/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/).test(value);
	},
	mac_address : function(value) {
		return (/^([0-9A-Fa-f]{2}:){5}([0-9A-Fa-f]{2})$/).test(value);
	},
	lease_time : function(value) {
		return (/^\d+(s|m|h)$/).test(value);
	},
	domain_name : function(value) {
		return (/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/).test(value);
	}
};

window.addEventListener('load', () => {
	LANShare.init();
}, false);

/*
 * PiLanshare WebUI
 * https://github.com/GramThanos/PiLanshare
 *
 * Stats Script
 */

var StatsCharts = {
	seconds_step : 5,
	chart_history : 24,

	interfaces : {},

	init : function() {
		$.ajax({url: this.link({'interfaces-stats':null}), dataType: 'json'})
		.done((json) => {
			// Check for errors
			if (json.error) {
				// Show error
				$('#loading').find('.card-header').text('Error');
				$('#loading').find('span').text(json.error);
				return;
			}

			// Calculate total
			var total = {
				tx : {fifo:0, multicast:0, errs:0, drop:0, packets:0, bytes:0, frame:0, compressed:0},
				rx : {fifo:0, multicast:0, errs:0, drop:0, packets:0, bytes:0, frame:0, compressed:0}
			};
			for (let name in json) {
				if (json.hasOwnProperty(name)) {
					this.add_stats(total.tx, json[name].tx);
					this.add_stats(total.rx, json[name].rx);
				}
			}

			// Init interfaces
			this.init_interface('Total', total);
			for (let name in json) {
				if (json.hasOwnProperty(name)) {
					this.init_interface(name, json[name]);
				}
			}

			// Start Intervals
			this.init_intervals();
			// Get IP addresses
			this.get_ip_addresses();
			// Show UI
			this.show();
		})
		.fail(() => {
			// Show error
			$('#loading').find('.card-header').text('Error');
			$('#loading').find('span').text('Connection failed!');
			return;
		});
	},

	get_ip_addresses : function() {
		// Get interfaces info
		$.ajax({url: this.link({'interfaces':null}), dataType: 'json'}).done((json) => {
			// On error cancel
			if (json.error) return;
			// For each interface saved
			for (let name in this.interfaces) {
				// Check if interface on json
				if (this.interfaces.hasOwnProperty(name) && json.hasOwnProperty(name)) {
					// Check if it has IPv4
					if (json[name].hasOwnProperty('INET')) {
						// Get all addresses
						let addresses = [];
						for (let i = 0; i < json[name]['INET'].length; i++) {
							if (json[name]['INET'][i].hasOwnProperty('addr')) {
								addresses.push(json[name]['INET'][i]['addr']);
							}
						}
						// If there is any address
						if (addresses.length > 0) {
							// Display the address
							this.interfaces[name].wrapper.find('.card-header').append(
								$('<small></small>').css({'float':'right', 'margin':'2px 0'}).text(addresses.join(', '))
							)
						}
					}
				}
			}
		});
	},

	show : function() {
		$('#loading').hide();
		for (let name in this.interfaces) {
			if (this.interfaces.hasOwnProperty(name)) {
				this.interfaces[name].wrapper.show();
			}
		}
	},
	hide : function() {
		for (let name in this.interfaces) {
			if (this.interfaces.hasOwnProperty(name)) {
				this.interfaces[name].wrapper.hide();
			}
		}
		$('#loading').show();
	},

	init_interface : function(name, stats) {
		// Generate a wrapper for this interface
		var wrapper = $('#interface-template').clone();
		wrapper.find('.card-header').text(name);
		$('#stats-wrapper').append(wrapper);
		// Create chart canvas
		var canvas = document.createElement('canvas');
		// Create chart data
		var data = {
			labels: [],
			datasets: [
				{label: 'Traffic Out', backgroundColor: 'rgb(255, 99, 132)', borderColor: 'rgb(255, 99, 132)', data: [], fill: false, lineTension: 0, spanGaps: false},
				{label: 'Traffic In', backgroundColor: 'rgb(54, 162, 235)', borderColor: 'rgb(54, 162, 235)', data: [], fill: false, lineTension: 0, spanGaps: false}
			]
		};
		// Populate with null data except the last
		for (let i = this.chart_history; i > 1; i--) {
			data.labels.push((i * this.seconds_step) + 's');
			data.datasets[0].data.push(null);
			data.datasets[1].data.push(null);
		}
		data.labels.push((1 * this.seconds_step) + 's');
		data.datasets[0].data.push(0);
		data.datasets[1].data.push(0);
		// Create Chart
		var chart = new window.Chart(canvas.getContext('2d'), {
			type: 'line',
			data: data,
			options: {
				title: {
					display: false,
					text: name + ' Interface'
				},
				elements: {
					point:{
						radius: 0
					}
				},
				layout: {
					padding: {
						left: 0,
						right: 30,
						top: 0,
						bottom: 0
					}
				},
				legend : {
					position : 'bottom'
				},
				tooltips: {
					enabled: false,
					mode: 'index',
					intersect: false
				},
				responsive: true,
				scales: {
					xAxes: [{
						stacked: true,
						scaleLabel: {
							display: true,
							labelString: 'Seconds ago'
						}
					}],
					yAxes: [{
						stacked: true,
						scaleLabel: {
							display: true,
							labelString: 'per second'
						},
						ticks: {
							suggestedMax : 10,
							suggestedMin : 0,
							callback: (label, index, labels) => {
								return this.readableSize(label);
							}
						}
					}]
				}
			}
		});
		chart.update();
		// Add canvas to ui
		wrapper.find('.chart-wrapper').append(canvas);
		// Save interface on interfaces
		this.interfaces[name] = {
			wrapper : wrapper,
			stats : stats,
			data : data,
			chart : chart
		};
		// Update UI stats
		this.update_interface_ui(name, stats);
	},

	interval : null,
	init_intervals : function() {
		this.interval = setInterval(() => {
			this.update();
		}, this.seconds_step * 1000);
	},

	update : function() {
		$.ajax({url: this.link({'interfaces-stats':null}), dataType: 'json'})
		.done((json) => {
			// Check for errors
			if (json.error) {
				// Show error
				$('#loading').find('.card-header').text('Error');
				$('#loading').find('span').text(json.error);
				clearInterval(this.interval);
				this.hide();
				return;
			}

			// Generate total stats
			var total = {
				tx : {fifo:0, multicast:0, errs:0, drop:0, packets:0, bytes:0, frame:0, compressed:0},
				rx : {fifo:0, multicast:0, errs:0, drop:0, packets:0, bytes:0, frame:0, compressed:0}
			};
			for (let name in json) {
				if (json.hasOwnProperty(name)) {
					this.add_stats(total.tx, json[name].tx);
					this.add_stats(total.rx, json[name].rx);
				}
			}

			// Update UI
			this.update_chart('Total', total);
			this.update_interface_ui('Total', total);
			for (let name in json) {
				if (json.hasOwnProperty(name)) {
					this.update_chart(name, json[name]);
					this.update_interface_ui(name, json[name]);
				}
			}
		})
		.fail(() => {
			for (let name in this.interfaces) {
				if (this.interfaces.hasOwnProperty(name)) {
					this.update_chart(name, null);
				}
			}
			this.update_chart('Total', null);
		});
	},

	update_chart : function(name, stats) {
		if (!this.interfaces.hasOwnProperty(name)) return;
		var obj = this.interfaces[name];
		this.update_chart_data(obj, stats);
		obj.chart.update();
	},

	update_chart_data : function(obj, stats) {
		if (stats) {
			obj.data.datasets[0].data.push((stats.tx.bytes - obj.stats.tx.bytes) / this.seconds_step);
			obj.data.datasets[0].data.shift();
			obj.data.datasets[1].data.push((stats.rx.bytes - obj.stats.rx.bytes) / this.seconds_step);
			obj.data.datasets[1].data.shift();
			obj.stats = stats;
		}
		else {
			obj.data.datasets[0].data.push(null);
			obj.data.datasets[0].data.shift();
			obj.data.datasets[1].data.push(null);
			obj.data.datasets[1].data.shift();
		}
	},

	update_interface_ui : function(name, stats) {
		if (!this.interfaces.hasOwnProperty(name)) return;
		var wrapper = this.interfaces[name].wrapper;
		wrapper.find('td:eq(4)').text(stats.rx.packets.toLocaleString());
		wrapper.find('td:eq(5)').text(stats.tx.packets.toLocaleString());
		wrapper.find('td:eq(7)').text(this.readableSize(stats.rx.bytes));
		wrapper.find('td:eq(8)').text(this.readableSize(stats.tx.bytes));
		wrapper.find('td:eq(10)').text(stats.rx.errs.toLocaleString());
		wrapper.find('td:eq(11)').text(stats.tx.errs.toLocaleString());

		if (stats.rx.bytes < 0 || stats.tx.bytes < 0) {
			console.log('Negative bytes', name, stats);
		}
	},

	add_stats : function(a, b) {
		for (let name in a) {
			if (a.hasOwnProperty(name) && b.hasOwnProperty(name)) {
				a[name] += b[name];
			}
		}
	},

	readableSize : function(bytes) {
		if (Math.abs(bytes) < 1024) return bytes + ' B';
		var units = ['kB','MB','GB','TB','PB','EB','ZB','YB'];
		var u = -1;
		do {
			bytes /= 1024;
			++u;
		} while(Math.abs(bytes) >= 1024 && u < units.length - 1);
		return bytes.toFixed(1) + '' + units[u];
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
};

window.addEventListener('load', () => {
	StatsCharts.init();
}, false);

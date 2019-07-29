/*
 * PiLanshare WebUI v0.1-beta
 * https://github.com/GramThanos/PiLanshare
 *
 * WebUI Handle Actions
 */

// Lock to avoid running multiple commands at a time
var lock = false;

// Function to execute command
var execute_command = function (command, callback) {
	if (lock) return;
	lock = true;

	$('#action-name').text(command);

	$.ajax({
		method: 'POST',
		url: 'index.php',
		data: {action: command}
	}).done(function(msg) {
		lock = false;
		setTimeout(function() {
			callback(msg);
		}, 500);
	}).fail(function() {
		lock = false;
		setTimeout(function() {
			callback(false);
		}, 500);
	});
};

// Function to check connection with the server
var check_connection = function (callback, cooldown, postpone) {
	if (!cooldown) cooldown = 5 * 1000;
	if (postpone) {
		return setTimeout(function() {
			check_connection(callback, cooldown);
		}, postpone);
	}
	execute_command('ping', function(result) {
		if (!result) {
			setTimeout(function() {
				check_connection(callback, cooldown);
			}, cooldown);
		}
		else {
			callback(result);
		}
	});
}


// Handle buttons

$('#tools-lanshare-restart').click(function() {
	$('#commandModal').modal('show');
	execute_command('lanshare-init', function(result) {
		check_connection(function(result) {
			$('#commandModal').modal('hide');
			console.log(result);
			document.location.href = document.location.href;
		}, 5 * 1000);
		console.log(result);
	});
});

$('#tools-system-reboot').click(function() {
	bootbox.confirm({
		message: '<div class="text-center" style="padding: 20px 0;">Are you sure you want to reboot the system?</div>',
		buttons: {
			confirm: {label: 'Yes, reboot it!', className: 'btn-danger'},
			cancel: {label: 'No...', className: 'btn-success'}
		},
		centerVertical: true,
		backdrop: true,
		callback: function (result) {
			if (!result) return;
			
			$('#commandModal').modal('show');
			execute_command('system-reboot', function(result) {
				check_connection(function(result) {
					$('#commandModal').modal('hide');
					console.log(result);
					document.location.href = document.location.href;
				}, 5 * 1000, 20 * 1000);
				console.log(result);
			});
		}
	});
	return;
});

$('#tools-system-shutdown').click(function() {
	bootbox.confirm({
		message: '<div class="text-center" style="padding: 20px 0;">Are you sure you want to shutdown the system?</div>',
		buttons: {
			confirm: {label: 'Yes, shut it down!', className: 'btn-danger'},
			cancel: {label: 'No...', className: 'btn-success'}
		},
		centerVertical: true,
		backdrop: true,
		callback: function (result) {
			if (!result) return;
			
			$('#commandModal').modal('show');
			execute_command('system-shutdown', function(result) {
				check_connection(function(result) {
					$('#commandModal').modal('hide');
					console.log(result);
					document.location.href = document.location.href;
				}, 30 * 1000, 30* 1000);
				console.log(result);
			});
		}
	});
	return;
});

$('#tools-dnsmasq-start').click(function() {
	$('#commandModal').modal('show');
	execute_command('dnsmasq-start', function(result) {
		$('#commandModal').modal('hide');
		console.log(result);
	});
});

$('#tools-dnsmasq-stop').click(function() {
	$('#commandModal').modal('show');
	execute_command('dnsmasq-stop', function(result) {
		$('#commandModal').modal('hide');
		console.log(result);
	});
});

$('#tools-actions-version').click(function() {
	$('#commandModal').modal('show');
	execute_command('version', function(result) {
		$('#commandModal').modal('hide');
		alert(result);
	});
});


// Remember tabs on refresh
$('.nav-tabs').each(function(index, navtab) {
	console.log(navtab.id);
	if (!navtab.id) return;
	var openTab = localStorage.getItem('nav-tabs-#' + navtab.id) || false;
	if (openTab) $('a[href="' + openTab + '"]').tab('show');

	console.log($(navtab).find('a[data-toggle=\'tab\']'));
	$(navtab).find('a[data-toggle=\'tab\']').on('click', function (e) {
		e.preventDefault();
		var tabId = this.getAttribute('href');
		localStorage.setItem('nav-tabs-#' + navtab.id, tabId);
		console.log('nav-tabs-#' + navtab.id + ' = ' + tabId);
		$(this).tab('show');
		return false;
	});
});

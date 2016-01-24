<?php

function check($args) {
	global $argv;

	// Command line parser
	$parser = new Console_CommandLine(array(
		'name'			=> $argv[0].' '.$argv[1],
		'description'	=> 'Check disk usage',
		'version'		=> '0.0.1',
		// 'force_posix'	=> true
	));

	$parser->addOption('rrd_file', array(
		'short_name'	=> '-f',
		// 'long_name'		=> '--rrd_file',
		'description'	=> 'RRD file',
		'action'		=> 'StoreString'
	));

	// $parser->addOption('datasource', array(
	// 	'short_name'	=> '-d',
	// 	// 'long_name'		=> '--datasource',
	// 	'description'	=> 'Disk available RRD datasource',
	// 	'action'		=> 'StoreString'
	// ));

	// $parser->addOption('datasource', array(
	// 	'short_name'	=> '-u',
	// 	// 'long_name'		=> '--datasource',
	// 	'description'	=> 'Disk used RRD datasource',
	// 	'action'		=> 'StoreString'
	// ));

	$parser->addOption('warning', array(
		'short_name'	=> '-w',
		// 'long_name'		=> '--warning',
		'description'	=> 'Warning value (%, integer value, default: 20)',
		'action'		=> 'StoreFloat',
		'default'		=> 20
	));

	$parser->addOption('critical', array(
		'short_name'	=> '-c',
		// 'long_name'		=> '--critical',
		'description'	=> 'Critical value (%, integer value, default: 10)',
		'action'		=> 'StoreFloat',
		'default'		=> 10
	));

	try {
		$result = $parser->parse(count($args), $args);

		$rrd_file = $result->options['rrd_file'];
		if (empty($rrd_file)) {
			echo 'No rrd_file specified'."\n";
			return NAGIOS_UNKNOWN;
		}

		if (!file_exists($rrd_file)) {
			echo 'File '.$rrd_file.' doesn\'t seem to exist' ."\n";
			return NAGIOS_UNKNOWN;
		}

		$v = new RRDValue($rrd_file);

		// $datasource = $result->options['datasource'];
		// if (empty($datasource)) {
		// 	echo 'No datasource specified'."\n";
		// 	echo 'Available datasources: '.implode(', ', $v->getDatasources())."\n";
		// 	return NAGIOS_UNKNOWN;
		// }

		$disk_available_datasource = 'DISKFREE_available';
		$disk_used_datasource = 'DISKFREE_used';

		$disk_available_value = $v->get($disk_available_datasource);
		$disk_used_value = $v->get($disk_used_datasource);

		if (is_nan($disk_available_value)) {
			echo 'Can\'t read disk available RRD value'."\n";
			return NAGIOS_UNKNOWN;
		}
		if (is_nan($disk_used_value)) {
			echo 'Can\'t read disk free RRD value'."\n";
			return NAGIOS_UNKNOWN;
		}

		$percentage_available = $disk_available_value / ($disk_available_value + $disk_used_value);
		$percentage_available = round($percentage_available * 100);

		$disk_available_value_mb = round($disk_available_value / 1024 / 1024 / 1024, 1);

		if ($percentage_available < $result->options['critical']) {
			echo "DISK CRITICAL - free space: $disk_available_value_mb GB ($percentage_available%)";
			return NAGIOS_CRITICAL;
		} else if ($percentage_available < $result->options['warning']) {
			echo "DISK WARNING - free space: $disk_available_value_mb GB ($percentage_available%)";
			return NAGIOS_WARNING;
		} else {
			echo "DISK OK - free space: $disk_available_value_mb GB ($percentage_available%)";
			return NAGIOS_OK;
		}
	} catch (Exception $exc) {
		$parser->displayError($exc->getMessage());
		return NAGIOS_UNKNOWN;
	}
}

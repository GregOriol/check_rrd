<?php

/*

 - value is equal to expected
 - value is lower/higher than expected
 - value is within % of expected (below/above ?)
 - value is 

 - value is last value
 - value is avg/min/max of last 1h

 - expected is a number
 - expected is value from last day/week/month (avg/min/max)
 - expected is value from multiple last x

*/

require_once(__DIR__.'/vendor/autoload.php');

require_once(__DIR__.'/classes/RRDValue.php');

require_once(__DIR__.'/inc/nagios.php');
require_once(__DIR__.'/inc/array_funcs.php');

// Command line parser
$parser = new Console_CommandLine(array(
	'description'	=> 'Check RRD',
	'version'		=> '0.0.1',
	'force_posix'	=> true
));

$parser->addArgument('config', array(
	'description'	=> 'Config name',
	'optional'		=> false
));

try {
	$result = $parser->parse();

	// Getting config
	$config = $result->args['config'];
	if (empty($config)) {
		echo 'No config specified'."\n";
		exit(NAGIOS_UNKNOWN);
	}

	$config_file = __DIR__.'/configs/'.$config.'.php';
	if (!file_exists($config_file)) {
		echo 'Config specified doesn\'t exist'."\n";
		exit(NAGIOS_UNKNOWN);
	}
	require_once($config_file);

	$args = $argv;
	array_shift($args);
	array_shift($args);
	array_unshift($args, $config);
	$r = check($args);

	if (in_array($r, array(NAGIOS_OK, NAGIOS_WARNING, NAGIOS_CRITICAL, NAGIOS_UNKNOWN))) {
		exit($r);
	} else {
		exit(NAGIOS_UNKNOWN);
	}
	

	exit(NAGIOS_UNKNOWN);
} catch (Exception $exc) {
	$parser->displayError($exc->getMessage());
	exit(NAGIOS_UNKNOWN);
}

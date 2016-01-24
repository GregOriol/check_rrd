<?php

class RRDValue {
	protected $rrd_file = null;

	public function __construct($rrd_file) {
		$this->rrd_file = $rrd_file;
	}

	public function getDatasources() {
		$data = rrd_lastupdate($this->rrd_file);

		if (empty($data) || !isset($data['ds_navm'])) {
			return array();
		}

		return $data['ds_navm'];
	}

	public function getLast($datasource) {
		$data = rrd_lastupdate($this->rrd_file);

		if (empty($data) || !isset($data['ds_navm']) || !isset($data['data'])) {
			return NAN;
		}

		$found = -1;
		for ($i = 0; $i < count($data['ds_navm']); $i++) {
			if ($data['ds_navm'][$i] == $datasource) {
				$found = $i;
			}
		}

		if ($found == -1) { // not found
			return NAN;
		}

		if (count($data['data']) < $found) {
			return NAN;
		}

		return $data['data'][$found];
	}

	public function get($datasource, $end = 'now', $start = 'end-1h', $resolution = 3600, $cf = 'AVERAGE') {
		// if ($end == 'now-best') {
		// 	$end = (int)(time() / $resolution) * $resolution;
		// }

		$data = rrd_fetch($this->rrd_file, array($cf,
			'--resolution', $resolution,
			'--start', $start,
			'--end', $end
		));

		if (!isset($data['data']) || !isset($data['data'][$datasource]) || empty($data['data'][$datasource])) {
			return NAN;
		}

		$func = $this->_cf_func($cf);
		if ($func) {
			return $func($data['data'][$datasource]);
		} else {
			return $data['data'][$datasource][0];
		}
	}

	protected function _cf_func($cf) {
		switch ($cf) {
			case 'MAX':
				$func = 'array_max';
				break;
			case 'MIN':
				$func = 'array_min';
				break;
			case 'AVERAGE':
				$func = 'array_avg';
				break;
			default:
				$func = null;
		}

		if (function_exists($func)) {
			return $func;
		} else {
			return null;
		}
	}
}

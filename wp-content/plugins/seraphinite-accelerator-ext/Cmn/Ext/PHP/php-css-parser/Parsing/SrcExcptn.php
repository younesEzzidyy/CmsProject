<?php

namespace seraph_accel\Sabberworm\CSS\Parsing;

class SrcExcptn extends \Exception {
	private $severity;	// Settings::ParseErr...
	private $iPos;

	public function __construct($severity, $sMessage, $iPos = null) {
		$this->severity = $severity;
		$this->iPos = $iPos;
		parent::__construct($sMessage);
	}

	public function getSeverity() {
		return $this->severity;
	}

	public function setSeverity($severity) {
		$this->severity = $severity;
	}

	public function getPos() {
		return $this->iPos;
	}
}
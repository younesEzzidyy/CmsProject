<?php

namespace seraph_accel\Sabberworm\CSS\Parsing;

/**
* Thrown if the CSS parsers encounters a token it did not expect
*/
class UnexpectedTokenException extends SrcExcptn {
	private $sExpected;
	private $sFound;
	// Possible values: literal, identifier, count, expression, search
	private $sMatchType;

	public function __construct($severity, $sExpected, $sFound, $sMatchType = 'literal', $iPos = null) {
		if ($sFound !== null)
			$sFound = UnexpectedTokenException::maskVal($sFound);

		$this->sExpected = $sExpected;
		$this->sFound = $sFound;
		$this->sMatchType = $sMatchType;
		if($this->sMatchType === 'search') {
			$sMessage = "Search for '" . UnexpectedTokenException::maskVal($sExpected) . "' returned no results, context: '{" . ( string )$sFound . "}'.";
		} else if($this->sMatchType === 'count') {
			$sMessage = "Next token was expected to have {$sExpected} chars, context: '" . ( string )$sFound . "'.";
		} else if($this->sMatchType === 'identifier') {
			$sMessage = "Identifier expected, got '" . ( string )$sFound . "'.";
		} else if($this->sMatchType === 'custom') {
			$sMessage = $sExpected;
			if($sFound !== null)
				$sMessage .= '\'' . $sFound . '\'';
			$sMessage .= '.';
		} else {
			$sMessage = "Token '" . UnexpectedTokenException::maskVal($sExpected) . "' ({$sMatchType}) not found, got '" . ( string )$sFound . "'.";
		}

		parent::__construct($severity, $sMessage, $iPos);
	}

	static function maskVal($v) {
		return str_replace(array("\r", "\n", "\t", "\v", "'"), array('\\r', '\\n', '\\t', '\\v', '\\\''), $v);
	}
}
<?php

namespace seraph_accel\Sabberworm\CSS\Parsing;

/**
* Thrown if the CSS parsers attempts to print something invalid
*/
class OutputException extends SrcExcptn {
	public function __construct($sMessage, $iLineNo = 0) {
		parent::__construct(Settings::ParseErrHigh, $sMessage, $iLineNo);
	}
}
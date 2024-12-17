<?php

namespace seraph_accel\Sabberworm\CSS\Value;

class CalcRuleValueList extends RuleValueList {
	public function __construct($iPos = 0) {
		parent::__construct(array(), ',', $iPos);
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$oOutputFormat->getFormatter()->implode($sResult, ' ', $this->aComponents);
	}

}

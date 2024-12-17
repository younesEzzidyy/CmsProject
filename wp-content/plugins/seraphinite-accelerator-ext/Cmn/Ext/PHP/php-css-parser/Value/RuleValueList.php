<?php

namespace seraph_accel\Sabberworm\CSS\Value;

class RuleValueList extends ValueList {
	public function __construct($sSeparator = ',', $iPos = 0) {
		parent::__construct(array(), $sSeparator, $iPos);
	}
}
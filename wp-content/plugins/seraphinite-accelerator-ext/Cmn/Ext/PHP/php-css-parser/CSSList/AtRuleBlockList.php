<?php

namespace seraph_accel\Sabberworm\CSS\CSSList;

use seraph_accel\Sabberworm\CSS\RuleSet\AtRuleSet;

/**
 * A BlockList constructed by an unknown @-rule. @media rules are rendered into AtRuleBlockList objects.
 */
class AtRuleBlockList extends AtRuleSet {

	public function __construct($sType, $sArgs = '', $iPos = 0) {
		parent::__construct($sType, $sArgs, $iPos);
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= $oOutputFormat->sBeforeAtRuleBlock;
		parent::render($sResult, $oOutputFormat);
		$sResult .= $oOutputFormat->sAfterAtRuleBlock;
	}

	public function isRootList() {
		return false;
	}

}
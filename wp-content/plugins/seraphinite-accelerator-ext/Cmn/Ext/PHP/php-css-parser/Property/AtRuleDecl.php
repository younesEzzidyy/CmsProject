<?php

namespace seraph_accel\Sabberworm\CSS\Property;

use seraph_accel\Sabberworm\CSS\Renderable;

/**
 * AtRuleDecl represents an any at @name rule without ruleset.
*/
class AtRuleDecl extends Renderable implements AtRule {
	private $sType;
	private $sArgs;
	private $iPos;
	protected $aComments;

	public function __construct($sType, $sArgs = null, $iPos = 0) {
		$this->sType = $sType;
		$this->sArgs = $sArgs;
		$this->iPos = $iPos;
		$this->aComments = array();
	}

	public function atRuleName() {
		return $this->sType;
	}

	public function atRuleArgs() {
		return $this->sArgs;
	}

	public function setAtRuleArgs($sArgs) {
		$this->sArgs = $sArgs;
	}

	public function getPos() {
		return $this->iPos;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '@';
		$sResult .= $this->sType;
		if ($this->sArgs) {
			$sResult .= ' ';
			$sResult .= $this->sArgs;
		}
		$sResult .= ';';
	}

	public function addComments(array $aComments) {
		$this->aComments = array_merge($this->aComments, $aComments);
	}

	public function getComments() {
		return $this->aComments;
	}

	public function setComments(array $aComments) {
		$this->aComments = $aComments;
	}
}
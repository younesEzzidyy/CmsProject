<?php

namespace seraph_accel\Sabberworm\CSS\Value;

class CSSFunction extends ValueList {

	protected $sName;

	public function __construct($sName, $aArguments, $sSeparator = ',', $iPos = 0) {
		if($aArguments instanceof RuleValueList) {
			$sSeparator = $aArguments->getListSeparator();
			$aArguments = $aArguments->getListComponents();
		}
		$this->sName = $sName;
		$this->iPos = $iPos;
		parent::__construct($aArguments, $sSeparator, $iPos);
	}

	public function getName() {
		return $this->sName;
	}

	public function setName($sName) {
		$this->sName = $sName;
	}

	public function getArguments() {
		return $this->aComponents;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= $this->sName;
		$sResult .= '(';
		parent::render($sResult, $oOutputFormat);
		$sResult .= ')';
	}

}

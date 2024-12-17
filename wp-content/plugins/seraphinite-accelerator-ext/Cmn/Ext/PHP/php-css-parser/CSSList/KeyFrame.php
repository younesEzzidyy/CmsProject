<?php

namespace seraph_accel\Sabberworm\CSS\CSSList;

use seraph_accel\Sabberworm\CSS\Property\AtRule;

class KeyFrame extends CSSList implements AtRule {

	private $vendorKeyFrame;
	private $animationName;

	public function __construct($iPos = 0) {
		parent::__construct($iPos);
		$this->vendorKeyFrame = null;
		$this->animationName  = null;
	}

	public function setVendorKeyFrame($vendorKeyFrame) {
		$this->vendorKeyFrame = $vendorKeyFrame;
	}

	public function getVendorKeyFrame() {
		return $this->vendorKeyFrame;
	}

	public function setAnimationName($animationName) {
		$this->animationName = $animationName;
	}

	public function getAnimationName() {
		return $this->animationName;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '@';
		$sResult .= $this->vendorKeyFrame;
		$sResult .= ' ';
		$sResult .= $this->animationName;
		$sResult .= $oOutputFormat->spaceBeforeOpeningBrace();
		$sResult .= '{';
		parent::render($sResult, $oOutputFormat);
		$sResult .= '}';
	}

	public function isRootList() {
		return false;
	}

	public function atRuleName() {
		return $this->vendorKeyFrame;
	}

	public function atRuleArgs() {
		return $this->animationName;
	}
}

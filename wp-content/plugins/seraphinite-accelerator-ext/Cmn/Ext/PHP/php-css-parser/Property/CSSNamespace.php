<?php

namespace seraph_accel\Sabberworm\CSS\Property;

use seraph_accel\Sabberworm\CSS\Renderable;

/**
* CSSNamespace represents an @namespace rule.
*/
class CSSNamespace extends Renderable implements AtRule {
	private $mUrl;
	private $sPrefix;
	private $iPos;
	protected $aComments;

	public function __construct($mUrl, $sPrefix = null, $iPos = 0) {
		$this->mUrl = $mUrl;
		$this->sPrefix = $sPrefix;
		$this->iPos = $iPos;
		$this->aComments = array();
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '@namespace ';
		if ($this->sPrefix !== null) {
			$sResult .= $this->sPrefix;
			$sResult .= ' ';
		}
		$this->mUrl->render($sResult, $oOutputFormat);
		$sResult .= ';';
	}

	public function getUrl() {
		return $this->mUrl;
	}

	public function getPrefix() {
		return $this->sPrefix;
	}

	public function setUrl($mUrl) {
		$this->mUrl = $mUrl;
	}

	public function setPrefix($sPrefix) {
		$this->sPrefix = $sPrefix;
	}

	public function atRuleName() {
		return 'namespace';
	}

	public function atRuleArgs() {
		$aResult = array($this->mUrl);
		if($this->sPrefix) {
			array_unshift($aResult, $this->sPrefix);
		}
		return $aResult;
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
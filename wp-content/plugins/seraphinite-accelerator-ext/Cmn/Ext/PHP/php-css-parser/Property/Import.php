<?php

namespace seraph_accel\Sabberworm\CSS\Property;

use seraph_accel\Sabberworm\CSS\Renderable;
use seraph_accel\Sabberworm\CSS\Value\URL;

/**
* Class representing an @import rule.
*/
class Import extends Renderable implements AtRule {
	private $oLocation;
	private $sMediaQuery;
	protected $iPos;
	protected $aComments;

	public function __construct(URL $oLocation, $sMediaQuery, $iPos = 0) {
		$this->oLocation = $oLocation;
		$this->sMediaQuery = $sMediaQuery;
		$this->iPos = $iPos;
		$this->aComments = array();
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	public function setLocation($oLocation) {
			$this->oLocation = $oLocation;
	}

	public function getLocation() {
			return $this->oLocation;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '@import ';
		$this->oLocation->render($sResult, $oOutputFormat);
		if ($this->sMediaQuery !== null) {
			$sResult .= ' ';
			$sResult .= $this->sMediaQuery;
		}
		$sResult .= ';';
	}

	public function atRuleName() {
		return 'import';
	}

	public function atRuleArgs() {
		$aResult = array($this->oLocation);
		if($this->sMediaQuery) {
			array_push($aResult, $this->sMediaQuery);
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
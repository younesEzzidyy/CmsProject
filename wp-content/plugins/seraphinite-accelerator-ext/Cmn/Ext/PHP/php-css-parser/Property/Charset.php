<?php

namespace seraph_accel\Sabberworm\CSS\Property;

use seraph_accel\Sabberworm\CSS\Renderable;

/**
 * Class representing an @charset rule.
 * The following restrictions apply:
 * • May not be found in any CSSList other than the Document.
 * • May only appear at the very top of a Document’s contents.
 * • Must not appear more than once.
 */
class Charset extends Renderable implements AtRule {

	private $sCharset;
	protected $iPos;
	protected $aComments;

	public function __construct($sCharset, $iPos = 0) {
		$this->sCharset = $sCharset;
		$this->iPos = $iPos;
		$this->aComments = array();
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	public function setCharset($sCharset) {
		$this->sCharset = $sCharset;
	}

	public function getCharset() {
		return $this->sCharset;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '@charset ';
		$this->sCharset->render($sResult, $oOutputFormat);
		$sResult .= ';';
	}

	public function atRuleName() {
		return 'charset';
	}

	public function atRuleArgs() {
		return $this->sCharset;
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
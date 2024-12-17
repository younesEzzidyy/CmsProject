<?php

namespace seraph_accel\Sabberworm\CSS\Comment;

use seraph_accel\Sabberworm\CSS\Renderable;

class Comment extends Renderable {
	protected $iPos;
	protected $sComment;

	public function __construct($sComment = '', $iPos = 0) {
		$this->sComment = $sComment;
		$this->iPos = $iPos;
	}

	/**
	 * @return string
	 */
	public function getComment() {
		return $this->sComment;
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	/**
	 * @return string
	 */
	public function setComment($sComment) {
		$this->sComment = $sComment;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	/**
	 * @return string
	 */
	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= '/*';
		$sResult .= $this->sComment;
		$sResult .= '*/';
	}

}

<?php

namespace seraph_accel\Sabberworm\CSS;

class Renderable {
	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
	}

	public function getPos() {
		return 0;
	}

	public function renderWhole(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat)
	{
		$sResult = ''; $this->render($sResult, $oOutputFormat);
		return $sResult;
	}
}
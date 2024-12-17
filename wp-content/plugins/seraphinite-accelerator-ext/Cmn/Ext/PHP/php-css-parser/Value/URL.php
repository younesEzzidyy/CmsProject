<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;

class URL extends PrimitiveValue {

	private $oURL;
	private $replacer;

	public function __construct(CSSString $oURL, $iPos = 0) {
		parent::__construct($iPos);
		$this->oURL = $oURL;
	}

	public static function parse(ParserState $oParserState) {
		$bUseUrl = $oParserState->comes('url', true);
		if ($bUseUrl) {
			$oParserState->consume(3, false);
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('(', false);
		}
		$oParserState->consumeWhiteSpace();
		
		$oURL = CSSString::parse($oParserState, true);

		$mimeType = null; $encoding = null;
		if ($bUseUrl && \seraph_accel\Ui::IsSrcAttrData($oURL->getString()))
			\seraph_accel\Ui::GetSrcAttrData($oURL->getString(), $mimeType, $encoding);
		if( !( $mimeType == 'image/svg+xml' && $encoding != 'base64' ) )
			$oURL->setQuote(null);

		$oResult = new URL($oURL, $oParserState->currentPos());
		if ($bUseUrl) {
			$oParserState->consumeWhiteSpace();
			$oParserState->consume(')', false);
		}
		return $oResult;
	}

	public function setURL(CSSString $oURL) {
		$this->oURL = $oURL;
	}

	public function getURL() {
		return $this->oURL;
	}

	public function setReplacer($replacer) {
		$this->replacer = $replacer;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		if (is_string($this->replacer))
			$sResult .= $this->replacer;
		else {
			$sResult .= 'url(';
			$this->oURL->render($sResult, $oOutputFormat);
			$sResult .= ')';
		}
	}

}
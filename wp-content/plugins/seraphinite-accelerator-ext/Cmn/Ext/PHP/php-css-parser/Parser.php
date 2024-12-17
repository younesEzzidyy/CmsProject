<?php

namespace seraph_accel\Sabberworm\CSS;

use seraph_accel\Sabberworm\CSS\CSSList\Document;
use seraph_accel\Sabberworm\CSS\Parsing\ParserState;

/**
 * Parser class parses CSS from text into a data structure.
 */
class Parser {
	private $oParserState;

	/**
	 * Parser constructor.
	 * Note that that iLineNo starts from 1 and not 0
	 *
	 * @param $sText
	 * @param Settings|null $oParserSettings
	 */
	public function __construct($sText, Settings $oParserSettings = null) {
		if ($oParserSettings === null) {
			$oParserSettings = Settings::create();
		}
		$this->oParserState = new ParserState($sText, $oParserSettings);
	}

	public function setCharset($sCharset) {
		$this->oParserState->setCharset($sCharset);
	}

	public function getCharset() {
		$this->oParserState->getCharset();
	}

	public function parse() {
		return Document::parse($this->oParserState);
	}

}

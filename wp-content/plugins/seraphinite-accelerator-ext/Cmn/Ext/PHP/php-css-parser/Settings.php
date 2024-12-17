<?php

namespace seraph_accel\Sabberworm\CSS;

use seraph_accel\Sabberworm\CSS\Rule\Rule;

/**
 * Parser settings class.
 *
 * Configure parser behaviour here.
 */
class Settings {
	const ParseErrHigh = 1;		// Can lead to mess up CSS
	const ParseErrMed = 2;		// Can lead to mess up CSS
	const ParseErrLow = 3;		// Can lead to miss some declarations but doesnt corrupt general structure

	/**
	* Multi-byte string support. If true (mbstring extension must be enabled), will use (slower) mb_strlen, mb_convert_case, mb_substr and mb_strpos functions. Otherwise, the normal (ASCII-Only) functions will be used.
	*/
	public $bMultibyteSupport;

	/**
	* The default charset for the CSS if no `@charset` rule is found. Defaults to utf-8.
	*/
	public $sDefaultCharset = 'utf-8';

	/**
	* Lenient parsing. When used (which is true by default), the parser will not choke on unexpected tokens but simply ignore them.
	*/
	public $bLenientParsing = Settings::ParseErrHigh | Settings::ParseErrMed;

	/**
	 * Keep comments
	 */
	public $bKeepComments = true;

	public $cbExceptionTracer = null;

	private function __construct() {
		$this->bMultibyteSupport = extension_loaded('mbstring');
	}

	public static function create() {
		return new Settings();
	}

	public function withMultibyteSupport($bMultibyteSupport = true) {
		$this->bMultibyteSupport = $bMultibyteSupport;
		return $this;
	}

	public function withDefaultCharset($sDefaultCharset) {
		$this->sDefaultCharset = $sDefaultCharset;
		return $this;
	}

	public function withLenientParsing($bLenientParsing = Settings::ParseErrHigh | Settings::ParseErrMed) {
		$this->bLenientParsing = $bLenientParsing;
		return $this;
	}

	public function withKeepComments($b) {
		$this->bKeepComments = $b;
		return $this;
	}

	public function beStrict() {
		return $this->withLenientParsing(false);
	}
}
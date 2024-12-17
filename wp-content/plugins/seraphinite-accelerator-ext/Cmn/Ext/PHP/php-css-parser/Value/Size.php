<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Settings;

class Size extends PrimitiveValue {

	/*
	const ABSOLUTE_SIZE_UNITS = 'px/cm/mm/mozmm/in/pt/pc/vh/vw/vm/vmin/vmax/rem'; //vh/vw/vm(ax)/vmin/rem are absolute insofar as they don’t scale to the immediate parent (only the viewport)
	const RELATIVE_SIZE_UNITS = '%/em/ex/ch/fr/cqi';
	const NON_SIZE_UNITS = 'deg/grad/rad/s/ms/turn/turns/Hz/kHz';

	private static $SIZE_UNITS = null;
	*/

	private $size;
	private $fSize;
	private $sUnit;
	private $bIsColorComponent;

	public function __construct($size, $sUnit = null, $bIsColorComponent = false, $iPos = 0) {
		parent::__construct($iPos);
		$this->size = $size;
		$this->fSize = (!is_string($size) || (strlen($size) <= (10 - 1 + (substr($size, 0, 1) == '-' ? 1 : 0)))) ? floatval($size) : null;
		$this->sUnit = $sUnit;
		$this->bIsColorComponent = $bIsColorComponent;
	}

	public static function parse(ParserState $oParserState, $bIsColorComponent = false) {
		$iPos = $oParserState->currentPos();

		$sSize = $oParserState->peek();
		if ($sSize === '-') {
			$oParserState->consume(1, false);
		}
		else {
			if ($sSize === '+')
				$oParserState->consume(1, false);
			$sSize = '';
		}

		$sSize .= $oParserState->consumeExpression('@\\G[\\.\\d]*@S', true, false);
		$sUnit = strtolower($oParserState->consumeExpression('@\\G[\\%a-z]+@iS', true, false));
		return new Size($sSize, $sUnit ? $sUnit : null, $bIsColorComponent, $iPos);
	}

	public function setUnit($sUnit) {
		$this->sUnit = $sUnit;
	}

	public function getUnit() {
		return $this->sUnit;
	}

	public function setSize($fSize) {
		$this->fSize = floatval($fSize);
	}

	public function getSize() {
		return $this->fSize === null ? $this->size : $this->fSize;
	}

	public function isColorComponent() {
		return $this->bIsColorComponent;
	}

	/*
	private static function getSizeUnits() {
		if(self::$SIZE_UNITS === null) {
			self::$SIZE_UNITS = array();
			foreach (explode('/', Size::ABSOLUTE_SIZE_UNITS.'/'.Size::RELATIVE_SIZE_UNITS.'/'.Size::NON_SIZE_UNITS) as $val) {
				$iSize = strlen($val);
				if(!isset(self::$SIZE_UNITS[$iSize])) {
					self::$SIZE_UNITS[$iSize] = array();
				}
				self::$SIZE_UNITS[$iSize][strtolower($val)] = $val;
			}

			krsort(self::$SIZE_UNITS, SORT_NUMERIC);
		}

		return self::$SIZE_UNITS;
	}
	*/

	/**
	 * Returns whether the number stored in this Size really represents a size (as in a length of something on screen).
	 * @return false if the unit an angle, a duration, a frequency or the number is a component in a Color object.
	 */
	/*public function isSize() {
		if (in_array($this->sUnit, explode('/', self::NON_SIZE_UNITS))) {
			return false;
		}
		return !$this->isColorComponent();
	}

	public function isRelative() {
		if (in_array($this->sUnit, explode('/', self::RELATIVE_SIZE_UNITS))) {
			return true;
		}
		if ($this->sUnit === null && $this->fSize != 0) {
			return true;
		}
		return false;
	}*/

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		if($this->fSize === null){
			$sResult .= $this->size . $this->sUnit;
			return;
		}

		$l = localeconv();
		$sPoint = preg_quote($l['decimal_point'], '/');
		$sResult .= preg_replace(array("/$sPoint/", "/^(-?)0\./"), array('.', '$1.'), $this->fSize) . ($this->sUnit === null ? '' : $this->sUnit);
	}

}

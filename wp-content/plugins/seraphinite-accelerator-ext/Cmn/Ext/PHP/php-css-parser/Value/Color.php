<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;

class Color extends CSSFunction {

	protected $bIsExpr;

	public function __construct($aColor, $iPos = 0, $bIsExpr = false, $sSeparator = ',') {
		parent::__construct(implode('', array_keys($aColor)), $aColor, $sSeparator, $iPos);
		$this->bIsExpr = $bIsExpr;
	}

	public static function parse(ParserState $oParserState) {
		$aColor = array();
		$bIsExpr = false;
		$sSeparator = ',';
		$nSeps = 0;
		if ($oParserState->comes('#')) {
			$oParserState->consume(1, false);
			$sValue = $oParserState->parseIdentifier();
			if (strlen($sValue) === 3) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2];
			} else if (strlen($sValue) === 4) {
				$sValue = $sValue[0] . $sValue[0] . $sValue[1] . $sValue[1] . $sValue[2] . $sValue[2] . $sValue[3] . $sValue[3];
			}

			if (strlen($sValue) === 8) {
				$aColor = array(
					'r' => new Size(intval($sValue[0] . $sValue[1], 16), null, true, $oParserState->currentPos()),
					'g' => new Size(intval($sValue[2] . $sValue[3], 16), null, true, $oParserState->currentPos()),
					'b' => new Size(intval($sValue[4] . $sValue[5], 16), null, true, $oParserState->currentPos()),
					'a' => new Size(round(self::mapRange(intval($sValue[6] . $sValue[7], 16), 0, 255, 0, 1), 2), null, true, $oParserState->currentPos())
				);
			} else if (strlen($sValue) === 6) {
				$aColor = array(
					'r' => new Size(intval(@$sValue[0] . @$sValue[1], 16), null, true, $oParserState->currentPos()),
					'g' => new Size(intval(@$sValue[2] . @$sValue[3], 16), null, true, $oParserState->currentPos()),
					'b' => new Size(intval(@$sValue[4] . @$sValue[5], 16), null, true, $oParserState->currentPos())
				);
			} else {
				return '#' . $sValue;
			}
		} else {
			$sColorMode = strtolower($oParserState->parseIdentifier());
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('(', false);
			for ($i = 0; ; ++$i) {
				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes(')'))
					break;

				$szLen = $oParserState->currentPos();
				if( $oParserState->comes('var(', true) )
				{
					$sz = Value::parseIdentifierOrFunction($oParserState);
					$bIsExpr = true;
				}
				else if( Value::shouldParseCalcFunction($oParserState) )
				{
					$sz = CalcFunction::parse($oParserState);
					$bIsExpr = true;
				}
				else
				{
					$sz = Size::parse($oParserState, true);
					if(is_string($sz->getSize()))
						$bIsExpr = true;
				}
				$szLen = $oParserState->currentPos() - $szLen;

				if ($i < 4)
				{
					if ($i == strlen($sColorMode))
						$sColorMode .= 'a';
					$aColor[$sColorMode[$i]] = $sz;
				}

				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes(')'))
					break;

				if ($oParserState->comesExpression('@\\G[\\,\\/]@'))
				{
					$nSeps++;
					$sSeparator = $oParserState->consume(1);
				}
				else if (!$szLen)
					$oParserState->consume(1, false);
				else
					$nSeps++;
			}
			$oParserState->consume(')', false);
		}

		if ($nSeps > 1)
			$sSeparator = ',';

		$clr = new Color($aColor, $oParserState->currentPos(), $bIsExpr, $sSeparator);
		if (strlen($clr->getName()) < 3)
			$clr->setName($sColorMode);
		return $clr;
	}

	private static function mapRange($fVal, $fFromMin, $fFromMax, $fToMin, $fToMax) {
		$fFromRange = $fFromMax - $fFromMin;
		$fToRange = $fToMax - $fToMin;
		$fMultiplier = $fToRange / $fFromRange;
		$fNewVal = $fVal - $fFromMin;
		$fNewVal *= $fMultiplier;
		return $fNewVal + $fToMin;
	}

	public function getColor() {
		return $this->aComponents;
	}

	public function setColor($aColor) {
		$this->setName(implode('', array_keys($aColor)));
		$this->aComponents = $aColor;
	}

	public function getColorDescription() {
		return $this->getName();
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		// Shorthand RGB color values
		if(!$this->bIsExpr && $oOutputFormat->getRGBHashNotation() && implode('', array_keys($this->aComponents)) === 'rgb') {
			$sResultTmp = sprintf(
				'%02x%02x%02x',
				$this->aComponents['r']->getSize(),
				$this->aComponents['g']->getSize(),
				$this->aComponents['b']->getSize()
			);

			$sResult .= '#';
			if (($sResultTmp[0] == $sResultTmp[1]) && ($sResultTmp[2] == $sResultTmp[3]) && ($sResultTmp[4] == $sResultTmp[5]))
				$sResult .= "$sResultTmp[0]$sResultTmp[2]$sResultTmp[4]";
			else
				$sResult .= $sResultTmp;
		}
		else {
			parent::render($sResult, $oOutputFormat);
		}
	}
}

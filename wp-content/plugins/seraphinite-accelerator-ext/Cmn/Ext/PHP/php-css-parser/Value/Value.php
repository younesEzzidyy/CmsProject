<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Renderable;
use seraph_accel\Sabberworm\CSS\Settings;

abstract class Value extends Renderable {
	protected $iPos;

	public function __construct($iPos = 0) {
		$this->iPos = $iPos;
	}

	public static function parseValue(ParserState $oParserState, $aListDelimiters = array(), $bAnyId = false) {
		$aStack = array();
		$oParserState->consumeWhiteSpace();
		//Build a list of delimiters and parsed values
		while (!$oParserState->isEnd() && !$oParserState->comesExpression('@\\G[\\}\\;\\!\\)\\\\]@S')) {
			$oParserState->consumeShityScope();

			if (count($aStack) > 0) {
				$bFoundDelimiter = false;
				foreach ($aListDelimiters as $sDelimiter) {
					if ($oParserState->comes($sDelimiter)) {
						array_push($aStack, $oParserState->consume(strlen($sDelimiter)));
						$oParserState->consumeWhiteSpace();
						$bFoundDelimiter = true;
						break;
					}
				}
				if (!$bFoundDelimiter) {
					//Whitespace was the list delimiter
					array_push($aStack, ' ');
				}
			}
			array_push($aStack, self::parsePrimitiveValue($oParserState, $bAnyId));
			$oParserState->consumeWhiteSpace();
		}
		//Convert the list to list objects
		foreach ($aListDelimiters as $sDelimiter) {
			if (count($aStack) === 1) {
				return $aStack[0];
			}
			$iStartPosition = null;
			while (($iStartPosition = array_search($sDelimiter, $aStack, true)) !== false) {
				$iLength = 2; //Number of elements to be joined
				for ($i = $iStartPosition + 2; $i < count($aStack); $i+=2, ++$iLength) {
					if ($sDelimiter !== $aStack[$i]) {
						break;
					}
				}
				$oList = new RuleValueList($sDelimiter, $oParserState->currentPos());
				for ($i = $iStartPosition - 1; $i - $iStartPosition + 1 < $iLength * 2; $i+=2) {
					$oList->addListComponent($aStack[$i]);
				}
				array_splice($aStack, $iStartPosition - 1, $iLength * 2 - 1, array($oList));
			}
		}
		if (!isset($aStack[0])) {
			throw new UnexpectedTokenException(Settings::ParseErrHigh, " {$oParserState->peek()} ", $oParserState->peek(1, -1) . $oParserState->peek(2), 'literal', $oParserState->currentPos());
		}
		return $aStack[0];
	}

	public static function parseIdentifierOrFunction(ParserState $oParserState, $bIgnoreCase = false, $bAnyId = false) {
		if ($bAnyId)
			$sResult = trim($oParserState->consumeUntil('\\,\\(\\)\\{\\}\\;\\@', false, false, true));
		else {
			$sResult = $oParserState->parseIdentifier();
			if ($bIgnoreCase)
				$sResult = strtolower($sResult);
		}

		if ($oParserState->comes('(')) {
			$oParserState->consume(1, false);
			$oParserState->consumeWhiteSpace();
			$aArguments = $oParserState->comes(')') ? array() : Value::parseValue($oParserState, array('=', ' ', ','));
			$sResult = new CSSFunction($sResult, $aArguments, ',', $oParserState->currentPos());
			$oParserState->consume(')', false);
		}

		return $sResult;
	}

	public static function shouldParseCalcFunction(ParserState $oParserState) {
		return $oParserState->comesExpression(
			'@\\G(?:' .
				'calc\\(' . '|' . '\\-webkit\\-calc\\(' . '|' . '\\-moz\\-calc\\(' . '|' .
				'clamp\\(' . '|' . '\\-webkit\\-clamp\\(' . '|' . '\\-moz\\-clamp\\(' . '|' .
				'min\\(' . '|' . 'max\\(' . '|' . 'minmax\\(' . '|' .
				'repeat\\(' . '|' .
				'acos\\(' . '|' . 'asin\\(' . '|' . 'atan\\(' . '|' . 'atan2\\(' . '|' . 'sin\\(' . '|' . 'sqrt\\(' . '|' . 'tan\\(' . '|' . 'cos\\(' . '|' . 'exp\\(' . '|' . 'hypot\\(' . '|' . 'log\\(' . '|' .
				'abs\\(' . '|' . 'mod\\(' . '|' . 'pow\\(' . '|' . 'rem\\(' . '|' . 'round\\(' . '|' . 'sign\\(' .
			')@iS');
	}

	public static function parsePrimitiveValue(ParserState $oParserState, $bAnyId = false) {
		$oValue = null;
		$oParserState->consumeWhiteSpace();
		if ($oParserState->comesExpression('@\\G(?:\\-\\.|\\-|\\+|\\.)?\\d@S')) {
			$oValue = Size::parse($oParserState);
		} else if ($oParserState->comesExpression('@\\G(?:\\#|rgb|hsl)@iS')) {
			$oValue = Color::parse($oParserState);
		} else if ($oParserState->comes('url', true)) {
			$oValue = URL::parse($oParserState);
		} else if (self::shouldParseCalcFunction($oParserState)) {
			$oValue = CalcFunction::parse($oParserState);
		} else if ($oParserState->comesExpression('@\\G[\'"]@S')) {
			$oValue = CSSString::parse($oParserState);
		} else if ($oParserState->comes('progid:')) {
			$oValue = self::_parseMicrosoftFilter($oParserState);
		} else if ($oParserState->comes('[')) {
			$oValue = LineName::parse($oParserState);
		} else if ($oParserState->comes('U+')) {
			$oValue = self::_parseUnicodeRangeValue($oParserState);
		} else {
			$oValue = self::parseIdentifierOrFunction($oParserState, false, $bAnyId);
		}
		$oParserState->consumeWhiteSpace();
		return $oValue;
	}

	private static function _parseMicrosoftFilter(ParserState $oParserState) {
		$sFunction = $oParserState->consumeUntil('\\(', false, true);
		$aArguments = Value::parseValue($oParserState, array(',', '='));
		$oParserState->consume(')', false);
		return new CSSFunction($sFunction, $aArguments, ',', $oParserState->currentPos());
	}

	private static function _parseUnicodeRangeValue(ParserState $oParserState) {
		$iCodepointMaxLenth = 6; // Code points outside BMP can use up to six digits
		$sRange = '';
		$oParserState->consume(2, false);
		do {
			if ($oParserState->comes('-'))
				$iCodepointMaxLenth = 13; // Max length is 2 six digit code points + the dash(-) between them
			$sRange .= $oParserState->consume(1);
		} while (strlen($sRange) < $iCodepointMaxLenth && $oParserState->comesExpression('@\\G[A-Fa-f0-9\\?-]@S'));
		return 'U+' . $sRange;
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	//Methods are commented out because re-declaring them here is a fatal error in PHP < 5.3.9
	//public abstract function __toString();
	//public abstract function render(\seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat);
}

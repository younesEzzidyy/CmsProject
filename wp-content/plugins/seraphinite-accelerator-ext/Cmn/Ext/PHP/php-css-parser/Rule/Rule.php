<?php

namespace seraph_accel\Sabberworm\CSS\Rule;

use seraph_accel\Sabberworm\CSS\Comment\Commentable;
use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Renderable;
use seraph_accel\Sabberworm\CSS\Value\RuleValueList;
use seraph_accel\Sabberworm\CSS\Value\Value;
use seraph_accel\Sabberworm\CSS\Settings;

/**
 * RuleSets contains Rule objects which always have a key and a value.
 * In CSS, Rules are expressed as follows: 'key: value[0][0] value[0][1], value[1][0] value[1][1];'
 */
class Rule extends Renderable implements Commentable {

	private $sRule;
	private $mValue;
	private $bIsImportant;
	private $aIeHack;
	protected $iPos;
	protected $aComments;

	public function __construct($sRule, $iPos = 0) {
		$this->sRule = $sRule;
		$this->mValue = null;
		$this->bIsImportant = false;
		$this->aIeHack = array();
		$this->iPos = $iPos;
		$this->aComments = array();
	}

	public static function consumeSemicolons(ParserState $oParserState) {
		$oParserState->consumeExpression('@\\G\\;+@S', false, false);
	}

	public static function parse(ParserState $oParserState, array $aComments) {
		$iPos = $oParserState->currentPos();
		$sIeHackPrefix = $oParserState->consumeExpression('@\\G[\\*_]@S', true, false);
		$bVarPrefix = $oParserState->comes('--');

		$oRule = new Rule($oParserState->parseIdentifier(false), $iPos);
		if( !strlen( $oRule->sRule ) )
			throw new UnexpectedTokenException( Settings::ParseErrMed, $oRule->sRule, $oParserState->peek(5), 'identifier', $oParserState->currentPos() );
		$oRule->setIeHackPrefix($sIeHackPrefix);
		$oRule->setComments($aComments);
		$oRule->addComments($oParserState->consumeWhiteSpace());
		$oParserState->consume(':', false);
		$oParserState->consumeWhiteSpace();
		if ($oParserState->comesExpression('@\\G(?:\\;|\\}|\\!important)@')) {
			throw new UnexpectedTokenException(Settings::ParseErrLow, 'Empty value', null, 'custom', $oParserState->currentPos());
		}

		$oValue = Value::parseValue($oParserState, self::listDelimiterForRule($oRule->getRule()), $bVarPrefix);
		$oRule->setValue($oValue);
		if ($oParserState->comes('!')) {
			$oParserState->consume(1, false);
			$oParserState->consumeWhiteSpace();
			$oParserState->consume('important', false);
			$oRule->setIsImportant(true);
		}
		$oParserState->consumeWhiteSpace();
		if ($oParserState->getSettings()->bLenientParsing & Settings::ParseErrMed) {
			while ($oParserState->comes('\\')) {
				$oParserState->consume(1, false);
				$oRule->addIeHack($oParserState->consume());
				$oParserState->consumeWhiteSpace();
			}
		}
		$oParserState->consumeWhiteSpace();
		Rule::consumeSemicolons($oParserState);
		$oParserState->consumeWhiteSpace();

		return $oRule;
	}

	private static function listDelimiterForRule($sRule) {
		//if ($sRule=='font-family') {	// https://developer.mozilla.org/en-US/docs/Web/CSS/font-family
		//    return array(',', ' ');
		//}
		if (preg_match('/^font($|-)/', $sRule)) {
			return array(',', '/', ' ');
		}
		return array(',', ' ', '/');
	}

	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	public function setRule($sRule) {
		$this->sRule = $sRule;
	}

	public function getRule() {
		return $this->sRule;
	}

	public function getValue() {
		return $this->mValue;
	}

	public function setValue($mValue) {
		$this->mValue = $mValue;
	}

	/**
	 *	@deprecated Old-Style 2-dimensional array given. Retained for (some) backwards-compatibility. Use setValue() instead and wrapp the value inside a RuleValueList if necessary.
	 */
	public function setValues($aSpaceSeparatedValues) {
		$oSpaceSeparatedList = null;
		if (count($aSpaceSeparatedValues) > 1) {
			$oSpaceSeparatedList = new RuleValueList(' ', $this->iPos);
		}
		foreach ($aSpaceSeparatedValues as $aCommaSeparatedValues) {
			$oCommaSeparatedList = null;
			if (count($aCommaSeparatedValues) > 1) {
				$oCommaSeparatedList = new RuleValueList(',', $this->iPos);
			}
			foreach ($aCommaSeparatedValues as $mValue) {
				if (!$oSpaceSeparatedList && !$oCommaSeparatedList) {
					$this->mValue = $mValue;
					return $mValue;
				}
				if ($oCommaSeparatedList) {
					$oCommaSeparatedList->addListComponent($mValue);
				} else {
					$oSpaceSeparatedList->addListComponent($mValue);
				}
			}
			if (!$oSpaceSeparatedList) {
				$this->mValue = $oCommaSeparatedList;
				return $oCommaSeparatedList;
			} else {
				$oSpaceSeparatedList->addListComponent($oCommaSeparatedList);
			}
		}
		$this->mValue = $oSpaceSeparatedList;
		return $oSpaceSeparatedList;
	}

	/**
	 *	@deprecated Old-Style 2-dimensional array returned. Retained for (some) backwards-compatibility. Use getValue() instead and check for the existance of a (nested set of) ValueList object(s).
	 */
	public function getValues() {
		if (!$this->mValue instanceof RuleValueList) {
			return array(array($this->mValue));
		}
		if ($this->mValue->getListSeparator() === ',') {
			return array($this->mValue->getListComponents());
		}
		$aResult = array();
		foreach ($this->mValue->getListComponents() as $mValue) {
			if (!$mValue instanceof RuleValueList || $mValue->getListSeparator() !== ',') {
				$aResult[] = array($mValue);
				continue;
			}
			if ($this->mValue->getListSeparator() === ' ' || count($aResult) === 0) {
				$aResult[] = array();
			}
			foreach ($mValue->getListComponents() as $mValue) {
				$aResult[count($aResult) - 1][] = $mValue;
			}
		}
		return $aResult;
	}

	/**
	 * Adds a value to the existing value. Value will be appended if a RuleValueList exists of the given type. Otherwise, the existing value will be wrapped by one.
	 */
	public function addValue($mValue, $sType = ' ') {
		if (!is_array($mValue)) {
			$mValue = array($mValue);
		}
		if (!$this->mValue instanceof RuleValueList || $this->mValue->getListSeparator() !== $sType) {
			$mCurrentValue = $this->mValue;
			$this->mValue = new RuleValueList($sType, $this->iPos);
			if ($mCurrentValue) {
				$this->mValue->addListComponent($mCurrentValue);
			}
		}
		foreach ($mValue as $mValueItem) {
			$this->mValue->addListComponent($mValueItem);
		}
	}

	public function setIeHackPrefix(string $sPrefix) {
		$this->sRule = $sPrefix . $this->sRule;
	}

	public function addIeHack($iModifier) {
		$this->aIeHack[] = $iModifier;
	}

	public function setIeHack(array $aModifiers) {
		$this->aIeHack = $aModifiers;
	}

	public function getIeHack() {
		return $this->aIeHack;
	}

	public function setIsImportant($bIsImportant) {
		$this->bIsImportant = $bIsImportant;
	}

	public function getIsImportant() {
		return $this->bIsImportant;
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$sResult .= $this->sRule;
		$sResult .= ':';
		$sResult .= $oOutputFormat->spaceAfterRuleName();
		if ($this->mValue instanceof Value) { //Can also be a ValueList
			$this->mValue->render($sResult, $oOutputFormat);
		} else {
			$sResult .= $this->mValue;
		}
		if ($this->bIsImportant) {
			$sResult .= $oOutputFormat->space('BeforeImportant');
			$sResult .= '!important';
		}
		if (!empty($this->aIeHack)) {
			$sResult .= ' \\';
			$sResult .= implode('\\', $this->aIeHack);
		}
		//$sResult .= ';';
	}

	/**
	 * @param array $aComments Array of comments.
	 */
	public function addComments(array $aComments) {
		$this->aComments = array_merge($this->aComments, $aComments);
	}

	/**
	 * @return array
	 */
	public function getComments() {
		return $this->aComments;
	}

	/**
	 * @param array $aComments Array containing Comment objects.
	 */
	public function setComments(array $aComments) {
		$this->aComments = $aComments;
	}

}

<?php

namespace seraph_accel\Sabberworm\CSS\Value;

use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Settings;

class CalcFunction extends CSSFunction {
	const T_OPERAND  = 1;
	const T_OPERATOR = 2;

	const OPERATORS_EXPR_SET = '\\+\\-\\*\\/';

	public static function parse(ParserState $oParserState) {
		$sFunction = trim($oParserState->consumeUntil('\\(', false, true));
		$oList = new RuleValueList(',', $oParserState->currentPos());

		for (;;) {
			$oCalcList = new CalcRuleValueList($oParserState->currentPos());
			$iNestingLevel = 0;
			$iLastComponentType = NULL;
			for (;;) {
				$oParserState->consumeWhiteSpace();
				if ($oParserState->comes('(')) {
					$iNestingLevel++;
					$oCalcList->addListComponent($oParserState->consume(1));
					continue;
				} else if ($oParserState->comes(')')) {
					$iNestingLevel--;
					if ($iNestingLevel < 0)
						break;
					$oCalcList->addListComponent($oParserState->consume(1));
					continue;
				} else if ($oParserState->comes(',') && !$iNestingLevel) {
					break;
				}
				if ($iLastComponentType != CalcFunction::T_OPERAND) {
					$oVal = Value::parsePrimitiveValue($oParserState);
					$oCalcList->addListComponent($oVal);
					$iLastComponentType = CalcFunction::T_OPERAND;
				} else {
					$operator = $oParserState->peekExpression('@\\G[' . CalcFunction::OPERATORS_EXPR_SET . ']@S');
					if ($operator) {
						if (($operator === '-' || $operator === '+') && (!$oParserState->comes(' ', false, -1) || !$oParserState->comes(' ', false, 1)))
							throw new UnexpectedTokenException(Settings::ParseErrHigh, " {$oParserState->peek()} ", $oParserState->peek(1, -1) . $oParserState->peek(2), 'literal', $oParserState->currentPos());

						$oParserState->consume(1, false);
						$oCalcList->addListComponent($operator);
						$iLastComponentType = CalcFunction::T_OPERATOR;
					} else {
						throw new UnexpectedTokenException(Settings::ParseErrHigh,
							sprintf(
								'Next token was expected to be an operand of type %s. Instead "%s" was found.',
								str_replace('\\', '', CalcFunction::OPERATORS_EXPR_SET),
								$oVal
							),
							'',
							'custom',
							$oParserState->currentPos()
						);
					}
				}
			}
			$oList->addListComponent($oCalcList);

			if (!$oParserState->comes(','))
				break;
			$oParserState->consume(1, false);
		}

		$oParserState->consume(')', false);
		return new CalcFunction($sFunction, $oList, ',', $oParserState->currentPos());
	}

}

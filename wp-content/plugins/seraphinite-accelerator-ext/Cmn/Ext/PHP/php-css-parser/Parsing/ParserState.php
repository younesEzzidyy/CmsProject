<?php

namespace seraph_accel\Sabberworm\CSS\Parsing;

use seraph_accel\Sabberworm\CSS\Comment\Comment;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Settings;

class ParserState {
	private $oParserSettings;

	private $sText;

	private $iCurrentPosition;
	private $sCharset;
	private $iLength;

	public function __construct($sText, Settings $oParserSettings) {
		$this->oParserSettings = $oParserSettings;
		$this->setText($sText);
	}

	public function getText() {
		return $this->sText;
	}

	public function setText($sText) {
		$this->sText = $sText;
		$this->iCurrentPosition = 0;
		$this->setCharset($this->oParserSettings->sDefaultCharset);
	}

	public function setCharset($sCharset) {
		$this->sCharset = $sCharset;
		$this->iLength = strlen($this->sText);
	}

	public function getCharset() {
		$this->oParserHelper->getCharset();
		return $this->sCharset;
	}

	public function currentLineCharNo($iPosition = null) {
		if ($iPosition === null)
			$iPosition = $this->iCurrentPosition;

		$lineCharNo = new LineCharNo();
		if ($iPosition > 0) {
			$lineCharNo->iLineNo = substr_count($this->sText, "\n", 0, $iPosition) + 1;

			$nLastLf = strrpos($this->sText, "\n", -($this->iLength - ($iPosition - 1)));
			if ($nLastLf === false)
				$nLastLf = -1;
			$lineCharNo->iLineCharNo = $iPosition - ( $nLastLf + 1 ) + 1;
		}
		else {
			$lineCharNo->iLineNo = 1;
			$lineCharNo->iLineCharNo = 1;
		}

		return $lineCharNo;
	}

	public function currentPos() {
		return $this->iCurrentPosition;
	}

	public function setCurrentPos($iCurrentPosition) {
		return $this->iCurrentPosition = $iCurrentPosition;
	}

	public function backtrack($iAmount) {
		$this->iCurrentPosition -= $iAmount;
	}

	public function getSettings() {
		return $this->oParserSettings;
	}

	public function parseIdentifier($critical = true) {
		$sResult = '';

		while (!$this->isEnd()) {
			if (preg_match('@\\G(?:\\xE2\\x80\\x94|[a-zA-Z0-9\\-_\\xA1-\\xFF])+@S', $this->sText, $m, 0, $this->iCurrentPosition)) {
				$sResult .= $m[0];
				$this->iCurrentPosition += strlen($m[0]);
			}

			if (!$this->comes('\\'))
				break;

			// Non-strings can contain \0 or \9 which is an IE hack supported in lenient parsing
			if ($this->comes('\\0') || $this->comes('\\9'))
				break;

			$sResult .= $this->consumeEscCharacter();
		}

		if( $critical && !strlen( $sResult ) )
		{
			$this->consume(1, false);
			throw new UnexpectedTokenException( Settings::ParseErrMed, $sResult, $this->peek(5), 'identifier', $this->currentPos() );
		}

		return $sResult;
	}

	public function consumeEscCharacter() {
		$this->consume(1, false);

		if ($this->comes("\r") || $this->comes("\n")) {
			$this->consumeSimpleWhiteSpace();
			return '';
		}

		if ($this->comes(' '))
			return '\\';

		$sUnicode = $this->consumeExpression('@\\G[0-9a-fA-F]{0,6}@S', true, false);
		if (!strlen($sUnicode))
			return $this->consume(1);

		if (strlen($sUnicode) < 6) {
			//Consume whitespace after incomplete unicode escape
			if ($this->comesExpression('@\\G\\s@S')) {
				if ($this->comes("\r\n"))
					$this->consume(2, false);
				else
					$this->consume(1, false);
			}
		}
		$iUnicode = intval($sUnicode, 16);
		$sUtf32 = "";
		for ($i = 0; $i < 4; ++$i) {
			$sUtf32 .= chr($iUnicode & 0xff);
			$iUnicode = $iUnicode >> 8;
		}

		if (function_exists('mb_convert_encoding')) {
			try { $res = mb_convert_encoding($sUtf32, $this->sCharset, 'utf-32le'); } catch(Exception $e) { $res = false; }
			if ($res !== false)
				return $res;
		}

		if (function_exists('iconv')) {
			$res = iconv('utf-32le', $this->sCharset, $sUtf32);
			if ($res !== false)
				return $res;
		}

		return "\xFF" . $sUnicode;
	}

	public function consumeSimpleWhiteSpace($ret = false) {
		return $this->consumeExpression('@\\G\\s*@S', $ret, false);
	}

	public function consumeWhiteSpace($retComments = true) {
		$comments = $retComments ? array() : false;
		do {
			$this->consumeSimpleWhiteSpace();

			if($this->oParserSettings->bLenientParsing & Settings::ParseErrMed) {
				try {
					$oComment = $this->consumeComment($this->oParserSettings->bKeepComments);
				} catch(UnexpectedTokenException $e) {
					if ($e->getSeverity() < Settings::ParseErrMed)
						$e->setSeverity(Settings::ParseErrMed);
					$this->traceException($e);
					// When we can’t find the end of a comment, we assume the document is finished.
					$this->iCurrentPosition = $this->iLength;
					return;
				}
			} else {
				$oComment = $this->consumeComment($this->oParserSettings->bKeepComments);
			}
			if ($oComment !== false) {
				if (!$retComments)
					$comments = true;
				else if ($this->oParserSettings->bKeepComments)
					$comments[] = $oComment;
			}
		} while($oComment !== false);
		return $comments;
	}

	public function consumeShityScope()
	{
		$this->consumeWhiteSpace();

		if (!$this->comes('{'))
			return;

		if (!($this->getSettings()->bLenientParsing & Settings::ParseErrMed))
			throw new UnexpectedTokenException(Settings::ParseErrHigh, 'Unexpected \'{\'', null, 'custom', $this->currentPos());

		$this->consume(1, false);

		$nShityScope = 1;
		while( $nShityScope )
		{
			if ($this->comes('{'))
			{
				$this->consume(1, false);
				$nShityScope++;
				continue;
			}
			else if ($this->comes('}'))
			{
				$this->consume(1, false);
				$nShityScope--;
				continue;
			}

			if (!$this->consumeWhiteSpace(false))
				$this->consume(1, false);
		}
	}

	public function comes($sString, $bCaseInsensitive = false, $iOffset = 0) {
		$iOffset += $this->iCurrentPosition;
		//if ($sString === '')
		//    return $iOffset >= $this->iLength;
		return $iOffset >= 0 ? substr_compare($this->sText, $sString, $iOffset, strlen($sString), $bCaseInsensitive) === 0 : false;
	}

	/*
	public function comesArr(array $aString, $bCaseInsensitive = false, $iOffset = 0) {
		foreach ($aString as $s)
			if ($this->comes($s, $bCaseInsensitive, $iOffset))
				return true;
		return false;
	}
	 */

	public function comesExpression($sExpression) {
		$m = null;
		return !!preg_match($sExpression, $this->sText, $m, PREG_OFFSET_CAPTURE, $this->iCurrentPosition);
	}

	public function comesSimpleWhiteSpace() {
		$m = null;
		return !!preg_match('@\\G\\s+@S', $this->sText, $m, PREG_OFFSET_CAPTURE, $this->iCurrentPosition);
	}

	public function peek($iLength = 1, $iOffset = 0) {
		$iOffset += $this->iCurrentPosition;
		if ($iOffset >= $this->iLength)
			return '';
		return $iOffset >= 0 ? substr($this->sText, $iOffset, $iLength) : '';
	}

	public function peekExpression($sExpression) {
		$m = null;
		if (!preg_match($sExpression, $this->sText, $m, 0, $this->iCurrentPosition))
			return '';
		return isset($m[1]) ? $m[1] : $m[0];
	}

	public function consume($mValue = 1, $ret = true) {
		if (is_string($mValue)) {
			$iLength = strlen($mValue);
			if (substr_compare($this->sText, $mValue, $this->iCurrentPosition, $iLength, true) !== 0) {
				throw new UnexpectedTokenException(Settings::ParseErrHigh, $mValue, $this->peek(max($iLength, 5)), 'literal', $this->currentPos());
			}
			$this->iCurrentPosition += $iLength;
			return $ret ? $mValue : null;
		}

		if ($this->iCurrentPosition + $mValue > $this->iLength) {
			throw new UnexpectedTokenException(Settings::ParseErrHigh, $mValue, $this->peek(5), 'count', $this->currentPos());
		}
		$sResult = $ret ? substr($this->sText, $this->iCurrentPosition, $mValue) : null;
		$this->iCurrentPosition += $mValue;
		return $sResult;
	}

	public function consumeExpression($mExpression, $ret = true, $critical = true) {
		$m = null;
		if (!preg_match($mExpression, $this->sText, $m, PREG_OFFSET_CAPTURE, $this->iCurrentPosition)) {
			if( !$critical )
				return '';
			throw new UnexpectedTokenException(Settings::ParseErrHigh, $mExpression, $this->peek(5), 'expression', $this->currentPos());
		}

		if (isset($m[1]))
			$mGrp = &$m[1];
		else
			$mGrp = &$m[0];

		$this->iCurrentPosition = $mGrp[1];
		return $this->consume(strlen($mGrp[0]), $ret);
	}

	/**
	 * @return false|Comment
	 */
	public function consumeComment($ret = true) {
		if (!$this->comes('/*'))
			return false;

		$iPos = $this->iCurrentPosition + 2;
		$iPosEnd = strpos($this->sText, '*/', $iPos);
		if( $iPosEnd === false )
		{
		    $iPosEnd = $this->iLength;
		    $this->iCurrentPosition = $iPosEnd;
		}
		else
		    $this->iCurrentPosition = $iPosEnd + 2;

		return $ret ? new Comment(substr($this->sText, $iPos, $iPosEnd - $iPos), $iPos - 2) : true;
	}

	public function isEnd() {
		return $this->iCurrentPosition >= $this->iLength;
	}

	public function consumeUntil($sPatternSymbols, $bIncludeEnd = false, $consumeEnd = false, $consumeQuoted = false, array &$comments = array()) {
		$out = '';
		$start = $this->iCurrentPosition;

		$sExpr = '@(?:\\/\\*|(?:\\G|[^\\\\])([' . ( $consumeQuoted ? '\'"' : '' ) . $sPatternSymbols . ']))@S';

		while (!$this->isEnd())
		{
			if (!preg_match($sExpr, $this->sText, $m, PREG_OFFSET_CAPTURE, $this->iCurrentPosition))
				break;

			if (isset($m[1]))
				$mGrp = &$m[1];
			else
				$mGrp = &$m[0];

			$iPosEnd = $mGrp[1];
			$out .= substr($this->sText, $this->iCurrentPosition, $iPosEnd - $this->iCurrentPosition);
			$this->iCurrentPosition = $iPosEnd;

			if ($consumeQuoted && ($mGrp[0] === '"' || $mGrp[0] === '\''))
			{
				$out .= $mGrp[0];
				$this->iCurrentPosition++;

				if (preg_match('@[^\\\\]\\' . $mGrp[0] . '@S', $this->sText, $m, PREG_OFFSET_CAPTURE, $this->iCurrentPosition - 1))
					$iPosEnd = $m[0][1] + strlen($m[0][0]);
				else
					$iPosEnd = $this->iLength;
				$out .= substr($this->sText, $this->iCurrentPosition, $iPosEnd - $this->iCurrentPosition);
				$this->iCurrentPosition = $iPosEnd;
				continue;
			}

			if ($mGrp[0] === '/*')
			{
				if (($comment = $this->consumeComment($this->oParserSettings->bKeepComments)) && $this->oParserSettings->bKeepComments)
					$comments[] = $comment;
				continue;
			}

			if ($bIncludeEnd)
			{
				$this->iCurrentPosition += strlen($mGrp[0]);
				$out .= $mGrp[0];
			}
			else if ($consumeEnd)
				$this->iCurrentPosition += strlen($mGrp[0]);

			return $out;
		}

		if( $this->iCurrentPosition == $start )
			$this->iCurrentPosition = $this->iLength;
		throw new UnexpectedTokenException(Settings::ParseErrHigh, 'One of "' . preg_replace('@\\\\([^nrtv])@', '$1', $sPatternSymbols) . '" symbol(s)', $this->peek(5), 'search', $start);
	}

	public function substrReplace($replacement, $start, $length) {
		$this->sText = substr_replace($this->sText, $replacement, $start, $length);
	}

	public function isTraceEnabled() {
		return !!$this->oParserSettings->cbExceptionTracer;
	}

	public function traceException($e) {
		if($this->oParserSettings->cbExceptionTracer)
			call_user_func($this->oParserSettings->cbExceptionTracer, $e);
	}
}

class LineCharNo
{
	public $iLineNo;
	public $iLineCharNo;

	public function __toString() {
		return ( string )$this->iLineNo . ':' . ( string )$this->iLineCharNo;
	}
}

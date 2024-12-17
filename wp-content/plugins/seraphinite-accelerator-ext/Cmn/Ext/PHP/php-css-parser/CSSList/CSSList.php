<?php

namespace seraph_accel\Sabberworm\CSS\CSSList;

use seraph_accel\Sabberworm\CSS\Comment\Commentable;
use seraph_accel\Sabberworm\CSS\Parsing\ParserState;
use seraph_accel\Sabberworm\CSS\Parsing\SrcExcptn;
use seraph_accel\Sabberworm\CSS\Parsing\UnexpectedTokenException;
use seraph_accel\Sabberworm\CSS\Property\AtRule;
use seraph_accel\Sabberworm\CSS\Property\Charset;
use seraph_accel\Sabberworm\CSS\Property\CSSNamespace;
use seraph_accel\Sabberworm\CSS\Property\AtRuleDecl;
use seraph_accel\Sabberworm\CSS\Property\Import;
use seraph_accel\Sabberworm\CSS\Property\Selector;
use seraph_accel\Sabberworm\CSS\Renderable;
use seraph_accel\Sabberworm\CSS\RuleSet\AtRuleSet;
use seraph_accel\Sabberworm\CSS\RuleSet\DeclarationBlock;
use seraph_accel\Sabberworm\CSS\RuleSet\RuleSet;
use seraph_accel\Sabberworm\CSS\Value\CSSString;
use seraph_accel\Sabberworm\CSS\Value\URL;
use seraph_accel\Sabberworm\CSS\Value\Value;
use seraph_accel\Sabberworm\CSS\Settings;

/**
 * A CSSList is the most generic container available. Its contents include RuleSet as well as other CSSList objects.
 * Also, it may contain Import and Charset objects stemming from @-rules.
 */
abstract class CSSList extends Renderable implements Commentable {

	protected $aComments;
	protected $aContents;
	protected $iPos;

	public function __construct($iPos = 0) {
		$this->aComments = array();
		$this->aContents = array();
		$this->iPos = $iPos;
	}

	public static function parseList(ParserState $oParserState, CSSList $oList) {
		$bIsRoot = $oList instanceof Document;
		if(is_string($oParserState)) {
			$oParserState = new ParserState($oParserState);
		}
		$bLenientParsing = $oParserState->getSettings()->bLenientParsing;
		for(;;) {
			$comments = $oParserState->consumeWhiteSpace();
			$oListItem = null;
			if(!$oParserState->isEnd()) {
				if($bLenientParsing & Settings::ParseErrHigh) {
					try {
						$oListItem = self::parseListItem($oParserState, $oList);
					} catch (SrcExcptn $e) {
						$oParserState->traceException($e);
						$oListItem = false;
					}
				} else {
					$oListItem = self::parseListItem($oParserState, $oList);
				}
			}
			if($oListItem === null) {
				// List parsing finished
				//foreach($comments as $oComment)
				//    $oList->append($oComment);
				return;
			}
			if($oListItem) {
				$oListItem->setComments($comments);
				$oList->append($oListItem);
			}
		}
		if(!$bIsRoot && !($bLenientParsing & Settings::ParseErrHigh)) {
			throw new SrcExcptn(Settings::ParseErrHigh, "Unexpected end of document", $oParserState->currentPos());
		}
	}

	protected static function parseListItem(ParserState $oParserState, CSSList $oList) {
		$bIsRoot = $oList instanceof Document;
		if ($oParserState->comes('@')) {
			$iPos = $oParserState->currentPos();
			$oAtRule = self::parseAtRule($oParserState);
			if($oAtRule instanceof Charset) {
				if(!$bIsRoot) {
					throw new UnexpectedTokenException(Settings::ParseErrLow, '@charset may only occur in root document', null, 'custom', $iPos);
				}
				if(count($oList->getContents()) > 0) {
					throw new UnexpectedTokenException(Settings::ParseErrLow, '@charset must be the first parseable token in a document', null, 'custom', $iPos);
				}
				$oParserState->setCharset($oAtRule->getCharset()->getString());
			}
			return $oAtRule;
		} else if ($oParserState->comes('}')) {
			$oParserState->consume(1, false);
			if ($bIsRoot) {
				if ($oParserState->getSettings()->bLenientParsing & Settings::ParseErrHigh) {
					$oParserState->consumeExpression('@\\G\\}+@S', false, false);
					return DeclarationBlock::parse($oParserState);
				} else {
					throw new SrcExcptn(Settings::ParseErrHigh, "Unopened {", $oParserState->currentPos());
				}
			} else {
				return null;
			}
		} else {
			return DeclarationBlock::parse($oParserState);
		}
	}

	private static function parseAtRule(ParserState $oParserState) {
		$oParserState->consume(1, false);
		$sIdentifier = strtolower($oParserState->parseIdentifier());
		$iIdentifierPos = $oParserState->currentPos();
		$oParserState->consumeWhiteSpace();
		if ($sIdentifier === 'import') {
			$oLocation = URL::parse($oParserState);
			$oParserState->consumeWhiteSpace();
			$sMediaQuery = null;
			if (!$oParserState->comes(';')) {
				$sMediaQuery = $oParserState->consumeUntil('\\{\\}\\;\\@');
			}
			if (!$oParserState->comes(';'))
				throw new UnexpectedTokenException(Settings::ParseErrHigh, 'Wrong import directive ending, got ', $oParserState->comes(';'), 'custom', $oParserState->currentPos());
			$oParserState->consume(1, false);
			return new Import($oLocation, $sMediaQuery, $iIdentifierPos);
		} else if ($sIdentifier === 'charset') {
			$sCharset = CSSString::parse($oParserState);
			$oParserState->consumeWhiteSpace();
			$oParserState->consume(';', false);
			return new Charset($sCharset, $iIdentifierPos);
		} else if (self::identifierIs($sIdentifier, 'keyframes')) {
			$oResult = new KeyFrame($iIdentifierPos);
			$oResult->setVendorKeyFrame($sIdentifier);
			$oResult->setAnimationName(trim($oParserState->consumeUntil('\\{', false, true)));
			CSSList::parseList($oParserState, $oResult);
			return $oResult;
		} else if ($sIdentifier === 'namespace') {
			$sPrefix = null;
			$mUrl = Value::parsePrimitiveValue($oParserState);
			if (!$oParserState->comes(';')) {
				$sPrefix = $mUrl;
				$mUrl = Value::parsePrimitiveValue($oParserState);
			}
			$oParserState->consume(';', false);
			if ($sPrefix !== null && !is_string($sPrefix)) {
				throw new UnexpectedTokenException(Settings::ParseErrMed, 'Wrong namespace prefix, got ', $sPrefix, 'custom', $iIdentifierPos);
			}
			if (!($mUrl instanceof CSSString || $mUrl instanceof URL)) {
				throw new UnexpectedTokenException(Settings::ParseErrMed, 'Wrong namespace url of invalid type, got ', $mUrl, 'custom', $iIdentifierPos);
			}
			return new CSSNamespace($mUrl, $sPrefix, $iIdentifierPos);
		} else {
			//Unknown other at rule (font-face or such)
			$sArgs = trim($oParserState->consumeUntil('\\{\\;', false, false));
			if($oParserState->comes(';'))
			{
				$oParserState->consume(';', false);
				return new AtRuleDecl($sIdentifier, $sArgs, $iIdentifierPos);
			}

			$oParserState->consume('{', false);
			if (substr_count($sArgs, '(') != substr_count($sArgs, ')')) {
				if (!($oParserState->getSettings()->bLenientParsing & Settings::ParseErrHigh)) {
					throw new SrcExcptn(Settings::ParseErrHigh, "Unmatched brace count in media query", $oParserState->currentPos());
				}
				$sArgs = '';
			}
			$bUseRuleSet = true;
			foreach(AtRule::BLOCK_RULES as $sBlockRuleName) {
				if(self::identifierIs($sIdentifier, $sBlockRuleName)) {
					$bUseRuleSet = false;
					break;
				}
			}
			if($bUseRuleSet) {
				$oAtRule = new AtRuleSet($sIdentifier, $sArgs, $iIdentifierPos);
				RuleSet::parseRuleSet($oParserState, $oAtRule, true);
			} else {
				$oAtRule = new AtRuleBlockList($sIdentifier, $sArgs, $iIdentifierPos);
				RuleSet::parseRuleSet($oParserState, $oAtRule, true);
			}
			return $oAtRule;
		}
	}

		/**
	 * Tests an identifier for a given value. Since identifiers are all keywords, they can be vendor-prefixed. We need to check for these versions too.
	 */
	private static function identifierIs($sIdentifier, $sMatch) {
		return (strcasecmp($sIdentifier, $sMatch) === 0)
			?: preg_match("/^(-\\w+-)?$sMatch$/i", $sIdentifier) === 1;
	}


	/**
	 * @return int
	 */
	public function getPos() {
		return $this->iPos;
	}

	/**
	 * Prepend item to list of contents.
	 *
	 * @param object $oItem Item.
	 */
	public function prepend($oItem) {
		array_unshift($this->aContents, $oItem);
	}

	/**
	 * Append item to list of contents.
	 *
	 * @param object $oItem Item.
	 */
	public function append($oItem) {
		$this->insert($oItem);
	}

	public function insert($oItem, $oItemAfter = null) {
		$iOffset = $oItemAfter ? array_search($oItemAfter, $this->aContents, true) : false;
		if ($iOffset === false)
			$iOffset = count($this->aContents) - 1;
		array_splice($this->aContents, $iOffset + 1, 0, is_array($oItem) ? $oItem : array($oItem));
	}

	/**
	 * Splice the list of contents.
	 *
	 * @param int       $iOffset      Offset.
	 * @param int       $iLength      Length. Optional.
	 * @param RuleSet[] $mReplacement Replacement. Optional.
	 */
	public function splice($iOffset, $iLength = null, $mReplacement = null) {
		array_splice($this->aContents, $iOffset, $iLength, $mReplacement);
	}

	/**
	 * Removes an item from the CSS list.
	 * @param RuleSet|Import|Charset|CSSList $oItemToRemove May be a RuleSet (most likely a DeclarationBlock), a Import, a Charset or another CSSList (most likely a MediaQuery)
	 * @return bool Whether the item was removed.
	 */
	public function remove($oItemToRemove) {
		$iKey = array_search($oItemToRemove, $this->aContents, true);
		if ($iKey !== false) {
			unset($this->aContents[$iKey]);
			return true;
		}
		return false;
	}

	/**
	 * Replaces an item from the CSS list.
	 * @param RuleSet|Import|Charset|CSSList $oItemToRemove May be a RuleSet (most likely a DeclarationBlock), a Import, a Charset or another CSSList (most likely a MediaQuery)
	 */
	public function replace($oOldItem, $oNewItem) {
		$iKey = array_search($oOldItem, $this->aContents, true);
		if ($iKey !== false) {
			array_splice($this->aContents, $iKey, 1, $oNewItem);
			return true;
		}
		return false;
	}

	/**
	 * Set the contents.
	 * @param array $aContents Objects to set as content.
	 */
	public function setContents(array $aContents) {
		$this->aContents = array();
		foreach ($aContents as $content) {
			$this->append($content);
		}
	}

	/**
	 * Removes a declaration block from the CSS list if it matches all given selectors.
	 * @param array|string $mSelector The selectors to match.
	 * @param boolean $bRemoveAll Whether to stop at the first declaration block found or remove all blocks
	 */
	public function removeDeclarationBlockBySelector($mSelector, $bRemoveAll = false) {
		if ($mSelector instanceof DeclarationBlock) {
			$mSelector = $mSelector->getSelectors();
		}
		if (!is_array($mSelector)) {
			$mSelector = explode(',', $mSelector);
		}
		foreach ($mSelector as $iKey => &$mSel) {
			if (!($mSel instanceof Selector)) {
				$mSel = new Selector($mSel);
			}
		}
		foreach ($this->aContents as $iKey => $mItem) {
			if (!($mItem instanceof DeclarationBlock)) {
				continue;
			}
			if ($mItem->getSelectors() == $mSelector) {
				unset($this->aContents[$iKey]);
				if (!$bRemoveAll) {
					return;
				}
			}
		}
	}

	public function __toString() {
		return $this->renderWhole(new \seraph_accel\Sabberworm\CSS\OutputFormat());
	}

	public function render(string &$sResult, \seraph_accel\Sabberworm\CSS\OutputFormat $oOutputFormat) {
		$bIsFirst = true;
		$oNextLevel = $oOutputFormat;
		if(!$this->isRootList()) {
			$oNextLevel = $oOutputFormat->nextLevel();
		}
		foreach ($this->aContents as $oContent) {
			if($bIsFirst) {
				$bIsFirst = false;
				$sResult .= $oNextLevel->spaceBeforeBlocks();
			} else {
				$sResult .= $oNextLevel->spaceBetweenBlocks();
			}
			$oContent->render($sResult, $oNextLevel);
		}

		if(!$bIsFirst) {
			// Had some output
			$sResult .= $oOutputFormat->spaceAfterBlocks();
		}
	}

	/**
	* Return true if the list can not be further outdented. Only important when rendering.
	*/
	public abstract function isRootList();

	public function getContents() {
		return $this->aContents;
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

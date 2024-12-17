<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class CssToXPathNormalizedAttributeMatchingExtension extends Symfony\Component\CssSelector\XPath\Extension\AttributeMatchingExtension
{

	public function translateEquals( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value . ' |';
		return( parent::translateEquals( $xpath, $attribute, $value ) );
	}

	public function translatePrefixMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value;
		return( parent::translatePrefixMatch( $xpath, $attribute, $value ) );
	}

	public function translateSuffixMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = $value . ' |';
		return( parent::translateSuffixMatch( $xpath, $attribute, $value ) );
	}

	public function translateDifferent( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			$value = '| ' . $value . ' |';
		return( parent::translateDifferent( $xpath, $attribute, $value ) );
	}

	public function translateIncludes( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			return( $xpath -> addCondition( $value ? sprintf(
				'%1$s and contains(%1$s, %2$s)',
				$attribute,
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( ' ' . $value . ' ' )
			) : '0' ) );

		return( parent::translateIncludes( $xpath, $attribute, $value ) );
	}

	public function translateDashMatch( Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $attribute, ?string $value ) : Symfony\Component\CssSelector\XPath\XPathExpr
	{
		if( $attribute === '@class' )
			return( $xpath -> addCondition( sprintf(
				'%1$s and (%1$s = %2$s or starts-with(%1$s, %3$s))',
				$attribute,
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( '| ' . $value . ' |' ),
				Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral( '| ' . $value . '-' )
			) ) );

		return( parent::translateDashMatch( $xpath, $attribute, $value ) );
	}
}

class CssToXPathHtmlExtension extends Symfony\Component\CssSelector\XPath\Extension\HtmlExtension
{
	public function translateHover(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $xpath -> addCondition('ANYSTATE or 1');
	}

	public function translateInvalid(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $this -> translateHover($xpath);
	}

	public function translateVisited(Symfony\Component\CssSelector\XPath\XPathExpr $xpath): Symfony\Component\CssSelector\XPath\XPathExpr
	{
		return $this -> translateHover($xpath);
	}
}

class CssSelFs
{
	public $xpath;
	public $flags;
	public $dataDom;
	public $parser;

	public $dataFs;

	private $nNegNesting;

	const F_SUBSELS_SKIP_NAME					= 1;
	const F_ATTR_FORCE_TO_ANY					= 2;
	const F_PSEUDO_FORCE_TO_ANY					= 4;
	const F_FUNCTION_FORCE_TO_ANY				= 8;
	const F_COMB_ADJACENT_FORCE_TO_ANY			= 16;

	public function __construct( $xpath, $sett, $flags = 0 )
	{
		$this -> xpath = $xpath;
		$this -> flags = $flags;

		$this -> dataDom = new CssSelFs_DomData();

		$this -> parser = new Sabberworm\CSS\Parsing\ParserState( '', $sett );
	}

	public function matchSelector( $item, $sel, $fnCombMatchItem = null )
	{
		$node = $this -> parseSelector( $sel );
		return( $node ? $node -> match( $this, $item, null, $fnCombMatchItem ) : false );
	}

	public function parseSelector( string $source )
	{
		$this -> parser -> setText( $source );
		$this -> nNegNesting = 0;

		try
		{
			$node = $this -> parseSelectorNode( $this -> parser );
		}
		catch( \Exception $e )
		{
			$this -> parser -> traceException( $e );
			return( false );
		}

		return( $node );
	}

	public function parseSelectorList( string $source )
	{
		$this -> parser -> setText( $source );
		$this -> nNegNesting = 0;

		try
		{
			$node = $this -> parseSelectorListNode( $this -> parser );
		}
		catch( \Exception $e )
		{
			$this -> parser -> traceException( $e );
			return( false );
		}

		return( $node );
	}

	private function parseSelectorListNode( $parser, $combFirst = ' ' ): CssSelFs_Node
	{
		$res = new CssSelFs_SelectorListNode();

		$parser -> consumeSimpleWhiteSpace();

		while( true )
		{
			$res -> aNode[] = $this -> parseSelectorNode( $parser, $combFirst );

			if( !$parser -> comes( ',' ) )
				break;

			$parser -> consume( 1, false );
			$parser -> consumeSimpleWhiteSpace();
		}

		return( count( $res -> aNode ) == 1 ? $res -> aNode[ 0 ] : $res );
	}

	private function parseSelectorNode( $parser, $combFirst = ' ' ): CssSelFs_Node
	{
		$parser -> consumeSimpleWhiteSpace();
		$peek = $parser -> peek();

		if( $peek === '+' || $peek === '>' || $peek === '~' )
		{
			$combFirst = $peek;
			$parser -> consume( 1, false );
		}

		$node = $this -> parseSimpleSelectorNode( $parser, $combFirst, true );
		if( ( $this -> flags & CssSelFs::F_COMB_ADJACENT_FORCE_TO_ANY ) && ( $combFirst == '+' || $combFirst == '~' ) )
		{
			$node = new CssSelFs_Node();
			$selectorLast = null;
		}
		else
			$selectorLast = $node;

		while( true )
		{
			$parser -> consumeSimpleWhiteSpace();
			if( $parser -> isEnd() )
				break;

			$peek = $parser -> peek();
			if( $peek == ',' || $peek == ')' )
				break;

			if( $peek === '+' || $peek === '>' || $peek === '~' )
			{
				$comb = $peek;
				$parser -> consume( 1, false );
			}
			else
				$comb = ' ';

			$nodeNext = $this -> parseSimpleSelectorNode( $parser, $comb );
			if( $selectorLast )
			{
				if( ( $this -> flags & CssSelFs::F_COMB_ADJACENT_FORCE_TO_ANY ) && ( $comb == '+' || $comb == '~' ) )
				{
					$selectorLast -> next = new CssSelFs_Node();
					$selectorLast = null;
				}
				else
				{
					$selectorLast -> next = $nodeNext;
					$selectorLast = $nodeNext;
				}
			}
		}

		return( $node );
	}

	private function parseSimpleSelectorNode( $parser, $comb = null, $bFirst = false ): CssSelFs_SelectorNode
	{
		$res = new CssSelFs_SelectorNode( $comb, $bFirst );

		$parser -> consumeSimpleWhiteSpace();

		$nHash = 0;
		$nClass = 0;
		$nElem = 0;
		$nAttr = 0;
		$nSubSel = 0;
		$nNeg = 0;
		$nHaving = 0;
		$nFunc = 0;
		$nPseudo = 0;
		$bAnyLast = false;

		$iSelectorStart = $parser -> currentPos();
		if( $node = $this -> parseElementNode( $parser ) )
		{
			$res -> aNode[] = $node;
			$nElem++;
		}
		$pseudoElem = null;

		while( !$parser -> isEnd() )
		{
			if( $parser -> comesSimpleWhiteSpace() )
				break;

			$peek = $parser -> peek();
			if( in_array( $peek, array( ',', ')', '+', '>', '~' ) ) )
				break;

			if( $peek == '#' )
			{
				$parser -> consume( 1, false );
				$node = new CssSelFs_HashNode( $parser -> parseIdentifier() );
				array_splice( $res -> aNode, $nHash++, 0, array( $node ) );
			}
			else if( $peek == '.' )
			{
				$parser -> consume( 1, false );
				$node = new CssSelFs_ClassNode( $parser -> parseIdentifier() );
				array_splice( $res -> aNode, $nHash + $nClass++, 0, array( $node ) );
			}
			else if( $peek == '[' )
			{
				$parser -> consume( 1, false );
				$node = $this -> parseAttributeNode( $parser );

				if( ( $this -> flags & CssSelFs::F_ATTR_FORCE_TO_ANY ) && !( $node -> operator === '' && in_array( $node -> getName(), array( 'id', 'class' ) ) ) )
				{
					if( $this -> nNegNesting && !$bAnyLast )
					{
						$res -> aNode[] = new CssSelFs_Node();
						$bAnyLast = true;
					}
				}
				else
					array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr++, 0, array( $node ) );
			}
			else if( $peek == ':' )
			{
				$parser -> consume( 1, false );

				if( $parser -> comes( ':' ) )
				{
					$parser -> consume( 1, false );
					$pseudoElem = $parser -> parseIdentifier();
					continue;
				}

				$identifier = $parser -> parseIdentifier();
				$identifierLwr = strtolower( $identifier );
				if( in_array( $identifierLwr, array( 'first-line', 'first-letter', 'before', 'after' ) ) )
				{

					$pseudoElem = $identifier;
					continue;
				}

				if( !$parser -> comes( '(' ) )
				{
					if( $this -> flags & CssSelFs::F_PSEUDO_FORCE_TO_ANY )
					{
						if( $this -> nNegNesting && !$bAnyLast )
						{
							$res -> aNode[] = new CssSelFs_Node();
							$bAnyLast = true;
						}
					}
					else
						array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg + $nHaving + $nFunc + $nPseudo++, 0, array( new CssSelFs_PseudoNode( $identifierLwr ) ) );
					continue;
				}

				$parser -> consume( 1, false );
				$parser -> consumeSimpleWhiteSpace();

				if( $identifierLwr === 'not' )
				{

					$this -> nNegNesting++;
					$nodeArg = $this -> parseSelectorListNode( $parser, null );
					$this -> nNegNesting--;

					$parser -> consume( ')', false );
					array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg++, 0, array( new CssSelFs_NegationNode( $nodeArg ) ) );
				}
				else if( $identifierLwr === 'has' )
				{
					$nodeArg = $this -> parseSelectorListNode( $parser );
					$parser -> consume( ')', false );
					array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg + $nHaving++, 0, array( new CssSelFs_HavingNode( $nodeArg ) ) );
				}
				else if( in_array( $identifierLwr, array( 'is', 'where' ) ) )
				{
					$node = new CssSelFs_SubSelsNode( $this -> parseSelectorListNode( $parser, null ), $identifier );
					$parser -> consume( ')', false );

					array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel++, 0, array( $node ) );
				}
				else if( in_array( $identifierLwr, array( '-webkit-any', '-moz-any' ) ) )
				{
					if( $this -> flags & CssSelFs::F_SUBSELS_SKIP_NAME )
						$identifier = 'is';

					$node = new CssSelFs_SubSelsNode( $this -> parseSelectorListNode( $parser, null ), $identifier );
					$parser -> consume( ')', false );

					array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel++, 0, array( $node ) );
				}
				else if( Gen::StrStartsWith( $identifierLwr, 'host' ) )
				{
					$iPos = $parser -> currentPos();
					$nodeSub = $this -> parseSelectorListNode( $parser, null );
					$parser -> consume( ')', false );

					if( $nodeSub instanceof CssSelFs_SelectorListNode )
					{
						$parser -> traceException( new Sabberworm\CSS\Parsing\SrcExcptn( Sabberworm\CSS\Settings::ParseErrLow, 'Selector of \'' . $identifierLwr . '\' should not be multiple.', $iPos ) );
						$nodeSub = $nodeSub -> aNode[ 0 ];
					}

					if( $this -> flags & CssSelFs::F_FUNCTION_FORCE_TO_ANY )
					{
						if( $this -> nNegNesting && !$bAnyLast )
						{
							$res -> aNode[] = new CssSelFs_Node();
							$bAnyLast = true;
						}
					}
					else
						array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg + $nHaving + $nFunc++, 0, array( new CssSelFs_FunctionNode( $identifierLwr, array( $nodeSub ) ) ) );
				}
				else if( Gen::StrStartsWith( $identifierLwr, 'nth-' ) )
				{
					$arguments = [];
					$arg = null;

					while( !$parser -> isEnd() )
					{
						$parser -> consumeSimpleWhiteSpace();

						if( $arg === 'of' )
						{
							$arguments[] = $this -> parseSelectorListNode( $parser, null );
							break;
						}

						$arg = $parser -> consumeUntil( '\\s\\)', false, false, true );
						if( strlen( $arg ) )
							$arguments[] = $arg;

						$parser -> consumeSimpleWhiteSpace();
						if( $parser -> comes( ')' ) )
							break;
					}

					$parser -> consume( ')', false );

					if( $this -> flags & CssSelFs::F_FUNCTION_FORCE_TO_ANY )
					{
						if( $this -> nNegNesting && !$bAnyLast )
						{
							$res -> aNode[] = new CssSelFs_Node();
							$bAnyLast = true;
						}
					}
					else
						array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg + $nHaving + $nFunc++, 0, array( new CssSelFs_FunctionNode( $identifierLwr, $arguments ) ) );
				}
				else
				{
					$arguments = [];
					$arg = null;

					while( !$parser -> isEnd() )
					{
						$parser -> consumeSimpleWhiteSpace();

						$arg = $parser -> consumeUntil( '\\s\\)', false, false, true );
						if( strlen( $arg ) )
							$arguments[] = $arg;

						$parser -> consumeSimpleWhiteSpace();
						if( $parser -> comes( ')' ) )
							break;
					}

					$parser -> consume( ')', false );

					if( $this -> flags & CssSelFs::F_FUNCTION_FORCE_TO_ANY )
					{
						if( $this -> nNegNesting && !$bAnyLast )
						{
							$res -> aNode[] = new CssSelFs_Node();
							$bAnyLast = true;
						}
					}
					else
						array_splice( $res -> aNode, $nHash + $nClass + $nElem + $nAttr + $nSubSel + $nNeg + $nHaving + $nFunc++, 0, array( new CssSelFs_FunctionNode( $identifierLwr, $arguments ) ) );
				}
			}
			else
				throw new Sabberworm\CSS\Parsing\UnexpectedTokenException( Sabberworm\CSS\Settings::ParseErrMed, 'Unknown selector ', $parser -> peek( 5 ), 'custom', $parser -> currentPos() );
		}

		if( $parser -> currentPos() === $iSelectorStart )
			throw new Sabberworm\CSS\Parsing\UnexpectedTokenException( Sabberworm\CSS\Settings::ParseErrMed, 'Unknown selector ', $parser -> peek( 5 ), 'custom', $parser -> currentPos() );

		$res -> pseudoElem = $pseudoElem;
		if( !$res -> aNode )
			$res -> aNode[] = new CssSelFs_ElementNode();
		return( $res );
	}

	private function parseElementNode( $parser )
	{
		if( $parser -> comes( '*' ) )
		{
			$parser -> consume( 1, false );
			$ns = null;
		}
		else
		{
			$ns = $parser -> parseIdentifier( false );
			if( !strlen( $ns ) )
				return( null );
		}

		if( $parser -> comes( '|' ) )
		{
			$parser -> consume( 1, false );
			if( $parser -> comes( '*' ) )
			{
				$parser -> consume( 1, false );
				$name = null;
			}
			else
				$name = $parser -> parseIdentifier();
		}
		else
		{
			$name = $ns;
			$ns = null;
		}

		return( new CssSelFs_ElementNode( $ns, $name !== null ? strtolower( $name ) : null ) );
	}

	private function parseAttributeNode( $parser ): CssSelFs_AttrBaseNode
	{
		$parser -> consumeSimpleWhiteSpace();

		if( $parser -> comes( '*' ) )
		{
			$parser -> consume( 1, false );
			$attribute = null;
		}
		else
			$attribute = strtolower( $parser -> parseIdentifier() );

		if( $parser -> comes( '|' ) )
		{
			$parser -> consume( 1, false );

			if( $parser -> comes( '=' ) )
			{
				$ns = null;
				$parser -> consume( 1, false );
				$operator = '|=';
			}
			else
			{
				$ns = $attribute;
				$attribute = strtolower( $parser -> parseIdentifier() );
				$operator = null;
			}
		}
		else
		{
			if( null === $attribute )
				throw new Sabberworm\CSS\Parsing\UnexpectedTokenException( Sabberworm\CSS\Settings::ParseErrMed, '|', $parser -> peek(), 'literal', $parser -> currentPos() );

			$ns = $operator = null;
		}

		if( null === $operator )
		{
			$parser -> consumeSimpleWhiteSpace();
			$next = $parser -> consume( 1 );

			if( $next == ']' )
				return( CssSelFs_AttrBaseNode::Create( $ns, $attribute, '', null ) );
			else if( $next == '=' )
				$operator = '=';
			else if( in_array( $next, array( '^', '$', '*', '~', '|', '!' ) ) && $parser -> comes( '=' ) )
			{
				$operator = $next . '=';
				$parser -> consume( 1, false );
			}
			else
				throw new Sabberworm\CSS\Parsing\UnexpectedTokenException( Sabberworm\CSS\Settings::ParseErrMed, 'Unknown operator ', $next . $parser -> peek( 1 ), 'custom', $parser -> currentPos() - 1 );
		}

		$value = trim( $parser -> consumeUntil( '\\]', false, false, true ) );
		if( Gen::StrStartsWith( $value, array( '"', '\'' ) ) )
			$value = substr( $value, 1, -1 );

		$parser -> consume( ']', false );
		return( CssSelFs_AttrBaseNode::Create( $ns, $attribute, $operator, $value ) );
	}

	function _getItemData( $item )
	{
		return( $item -> nodeType == XML_ELEMENT_NODE ? $item -> dataDomFs : $this -> dataDom );
	}

	function _initItemData( $item, $sClasses )
	{
		if( $item -> hasChildNodes() )
			$item -> dataDomFs = new CssSelFs_DomData();

		if( $item -> hasAttribute( 'id' ) )
		{
			$sId = $item -> getAttribute( 'id' );

			$itemParent = $item -> parentNode;

			$this -> _getItemData( $itemParent ) -> aId[ $sId ][ '>' ][] = $item;
			do { $this -> _getItemData( $itemParent ) -> aId[ $sId ][ ' ' ][] = $item; } while( $itemParent = $itemParent -> parentNode );
		}

		{
			$itemParent = $item -> parentNode;
			$this -> _getItemData( $itemParent ) -> aTag[ $item -> nodeName ][ '>' ][] = $item;
			do { $this -> _getItemData( $itemParent ) -> aTag[ $item -> nodeName ][ ' ' ][] = $item; } while( $itemParent = $itemParent -> parentNode );
		}

		if( !( $this -> flags & CssSelFs::F_ATTR_FORCE_TO_ANY ) && $item -> attributes )
			foreach( $item -> attributes as $attr )
			{
				$itemParent = $item -> parentNode;
				$this -> _getItemData( $itemParent ) -> aAttr[ $attr -> nodeName ][ '>' ][] = $item;
				do { $this -> _getItemData( $itemParent ) -> aAttr[ $attr -> nodeName ][ ' ' ][] = $item; } while( $itemParent = $itemParent -> parentNode );
			}

		if( $sClasses !== null )
		{
			foreach( Ui::ParseClassAttr( $sClasses ) as $sClass )
			{
				$sClass = ' ' . $sClass . ' ';

				$itemParent = $item -> parentNode;
				$this -> _getItemData( $itemParent ) -> aClass[ $sClass ][ '>' ][] = $item;
				do { $this -> _getItemData( $itemParent ) -> aClass[ $sClass ][ ' ' ][] = $item; } while( $itemParent = $itemParent -> parentNode );
			}
		}
	}

	function _deinitItemData( $item )
	{
		$item -> dataDomFs = null;
	}
}

class CssSelFs_DomData
{
	public $aId;
	public $aClass;
	public $aTag;
	public $aAttr;

	public function __construct()
	{
		$this -> aId = array();
		$this -> aClass = array();
		$this -> aTag = array();
		$this -> aAttr = array();
	}
}

class CssSelFs_Node extends Sabberworm\CSS\Renderable
{
	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
			return( 'ANY' );
		return( self::$g_aComb[ $comb ]( $this, $fs, $item, $fnCombMatchItem ) );
	}

	protected static $g_aComb = array(
		' '				=> 'seraph_accel\\CssSelFs_Node::_enumDescendant',
		'>'					=> 'seraph_accel\\CssSelFs_Node::_enumChild',
		'+'			=> 'seraph_accel\\CssSelFs_Node::_enumDirectAdjacent',
		'~'			=> 'seraph_accel\\CssSelFs_Node::_enumIndirectAdjacent',
	);

	static function _enumDescendant( $thisP, $fs, $item, $fnCombMatchItem )
	{
		for( $itemComb = null; $itemComb = HtmlNd::GetNextTreeChild( $item, $itemComb );  )
		{
			if( $itemComb -> nodeType != XML_ELEMENT_NODE )
				continue;

			$res = $thisP -> match( $fs, $itemComb );
			if( $res === false )
				return( false );
			if( $res === null )
				continue;

			if( $res = $fnCombMatchItem( $fs, $itemComb ) )
				return( $res );
			if( $res === false )
				return( false );
		}

		return( null );
	}

	static function _enumChild( $thisP, $fs, $item, $fnCombMatchItem )
	{
		for( $itemComb = $item -> firstChild; $itemComb; $itemComb = $itemComb -> nextSibling )
		{
			if( $itemComb -> nodeType != XML_ELEMENT_NODE )
				continue;

			$res = $thisP -> match( $fs, $itemComb );
			if( $res === false )
				return( false );
			if( $res === null )
				continue;

			if( $res = $fnCombMatchItem( $fs, $itemComb ) )
				return( $res );
			if( $res === false )
				return( false );
		}

		return( null );
	}

	static function _enumDirectAdjacent( $thisP, $fs, $item, $fnCombMatchItem )
	{
		while( $item = $item -> nextSibling )
		{
			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			$res = $thisP -> match( $fs, $item );
			if( $res === false )
				return( false );
			if( $res === null )
				return( null );

			if( $res = $fnCombMatchItem( $fs, $item ) )
				return( $res );
			if( $res === false )
				return( false );

			break;
		}

		return( null );

	}

	static function _enumIndirectAdjacent( $thisP, $fs, $item, $fnCombMatchItem )
	{
		while( $item = $item -> nextSibling )
		{
			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			$res = $thisP -> match( $fs, $item );
			if( $res === false )
				return( false );
			if( $res === null )
				continue;

			if( $res = $fnCombMatchItem( $fs, $item ) )
				return( $res );
			if( $res === false )
				return( false );
		}

		return( null );

	}

	protected static $g_aCombXpathPrefix = array(
		' '				=> './/',
		'>'					=> './',
		'+'			=> './following-sibling::*[1]/self::',
		'~'			=> './following-sibling::',
	);

	static function _getCombXpathPrefix( $comb )
	{
		return( self::$g_aCombXpathPrefix[ $comb ] );
	}

	function _enumByXpath( $matchSelf, $fs, $expression, $item, $fnCombMatchItem )
	{
		$aItemComb = $fs -> xpath -> evaluate( $expression, $item );
		if( $aItemComb === false )
		{

			return( false );
		}

		if( $matchSelf )
		{
			foreach( $aItemComb as $itemComb )
			{
				$res = $this -> match( $fs, $itemComb );
				if( $res === false )
					return( false );
				if( $res === null )
					continue;

				if( $res = $fnCombMatchItem( $fs, $itemComb ) )
					return( $res );
				if( $res === false )
					return( false );
			}
		}
		else
		{
			foreach( $aItemComb as $itemComb )
			{
				if( $res = $fnCombMatchItem( $fs, $itemComb ) )
					return( $res );
				if( $res === false )
					return( false );
			}
		}

		return( null );
	}

	static function _getNegateRes( $res )
	{
		if( $res === null )
			$res = true;
		else if( $res === true )
			$res = null;
		return( $res );
	}
}

class CssSelFs_SelectorCallCtx
{
	public $thisP;
	public $fnCombMatchItem;

	public function __construct( $thisP, $fnCombMatchItem )
	{
		$this -> thisP = $thisP;
		$this -> fnCombMatchItem = $fnCombMatchItem;
	}

	public function m( CssSelFs $fs, $item )
	{
		return( $this -> thisP -> _match( $fs, $item, $this -> fnCombMatchItem, 0 ) );
	}

	public function mNext( CssSelFs $fs, $item )
	{
		return( $this -> thisP -> _match( $fs, $item, $this -> fnCombMatchItem, 1 ) );
	}
}

class CssSelFs_SelectorNode extends CssSelFs_Node
{
	public $comb;
	public $aNode;
	public $pseudoElem;
	public $next;

	private $bFirst;

	public function __construct( $comb = null, $bFirst = false )
	{
		$this -> bFirst = $bFirst;
		$this -> comb = $comb;
		$this -> aNode = array();
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
		{
			if( !$this -> comb )
				return( $this -> _match( $fs, $item, $fnCombMatchItem, 0 ) );
			return( $this -> aNode[ 0 ] -> match( $fs, $item, $this -> comb, array( new CssSelFs_SelectorCallCtx( $this, $fnCombMatchItem ), 'mNext' ) ) );
		}

		if( $this -> comb )
			return( parent::match( $fs, $item, $comb, array( new CssSelFs_SelectorCallCtx( $this, $fnCombMatchItem ), 'm' ) ) );
		return( $this -> aNode[ 0 ] -> match( $fs, $item, $comb, array( new CssSelFs_SelectorCallCtx( $this, $fnCombMatchItem ), 'mNext' ) ) );
	}

	function _match( $fs, $item, $fnCombMatchItem, $i )
	{
		$resSelf = true;

		for( ; $i < count( $this -> aNode ); $i++ )
		{
			$node = $this -> aNode[ $i ];

			$res = $node -> match( $fs, $item );
			if( $res === false )
				return( false );

			if( !$res )
			{
				$resSelf = null;
				break;
			}

			if( $res === 'ANY' )
				$resSelf = $res;
		}

		if( !$resSelf )
			return( null );

		if( $res = $this -> next ? $this -> next -> match( $fs, $item, null, $fnCombMatchItem ) : ( $fnCombMatchItem ? $fnCombMatchItem( $fs, $item ) : true ) )
			return( $resSelf === 'ANY' ? 'ANY' : $res );
		if( $res === false )
			return( false );

		return( null );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		if( $this -> comb !== null && !( $this -> bFirst && $this -> comb === ' ' ) )
			$sResult .= $this -> comb;

		foreach( $this -> aNode as $node )
			$node -> render( $sResult, $oOutputFormat );

		if( $this -> pseudoElem )
		{
			$sResult .= '::';
			$sResult .= $this -> pseudoElem;
		}

		if( $this -> next )
			$this -> next -> render( $sResult, $oOutputFormat );
	}
}

class CssSelFs_SelectorListNode extends CssSelFs_Node
{
	public $aNode;

	public function __construct()
	{
		$this -> aNode = array();
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		foreach( $this -> aNode as $node )
		{
			if( $res = $node -> match( $fs, $item, $comb, $fnCombMatchItem ) )
				return( $res );
			if( $res === false )
				return( false );
		}

		return( null );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$oOutputFormat -> getFormatter() -> implode( $sResult, ',', $this -> aNode );
	}
}

class CssSelFs_ElementNode extends CssSelFs_Node
{
	public $ns;
	public $name;

	public function __construct( string $ns = null, string $name = null )
	{
		$this -> ns = $ns;
		$this -> name = $name;
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
		{
			if( !$this -> name && !$this -> ns )
				return( true );

			if( ( string )$item -> prefix !== ( string )$this -> ns )
				return( null );

			return( ( !$this -> name || $item -> nodeName === $this -> name ) ? true : null );
		}

		if( !$this -> name && !$this -> ns )
			return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

		if( $comb == ' ' || $comb == '>' )
		{
			$dataDom = $fs -> _getItemData( $item );
			if( $dataDom === null || !isset( $dataDom -> aTag[ $this -> name ][ $comb ] ) )
				return( null );

			foreach( $dataDom -> aTag[ $this -> name ][ $comb ] as $itemComb )
			{
				if( $res = $fnCombMatchItem( $fs, $itemComb ) )
					return( $res );
				if( $res === false )
					return( false );
			}

			return( null );
		}

		return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		if( $this -> ns )
		{
			$sResult .= $this -> ns;
			$sResult .= '|';
		}

		$sResult .= $this -> name !== null ? $this -> name : '*';
	}
}

class CssSelFs_HashNode extends CssSelFs_Node
{
	public $id;

	public function __construct( string $id )
	{
		$this -> id = $id;
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
			return( ( string )$item -> getAttribute( 'id' ) == $this -> id ? ( strpos( $this -> id, '*' ) === false ? true : 'ANY' ) : null );

		if( $comb == ' ' || $comb == '>' )
		{
			$dataDom = $fs -> _getItemData( $item );
			if( $dataDom === null || !isset( $dataDom -> aId[ $this -> id ][ $comb ] ) )
				return( null );

			foreach( $dataDom -> aId[ $this -> id ][ $comb ] as $itemComb )
			{
				if( $res = $fnCombMatchItem( $fs, $itemComb ) )
					return( $res );
				if( $res === false )
					return( false );
			}

			return( null );
		}

		if( !isset( $fs -> dataDom -> aId[ $this -> id ] ) )
			return( null );

		return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= '#';
		$sResult .= $this -> id;
	}
}

class CssSelFs_ClassNode extends CssSelFs_Node
{
	public $name;

	public function __construct( string $name )
	{
		$this -> name = ' ' . $name . ' ';
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
			return( strpos( ( string )$item -> getAttribute( 'class' ), $this -> name ) !== false ? ( strpos( $this -> name, '*' ) === false ? true : 'ANY' ) : null );

		if( $comb == ' ' || $comb == '>' )
		{
			$dataDom = $fs -> _getItemData( $item );
			if( $dataDom === null || !isset( $dataDom -> aClass[ $this -> name ][ $comb ] ) )
				return( null );

			foreach( $dataDom -> aClass[ $this -> name ][ $comb ] as $itemComb )
			{
				if( $res = $fnCombMatchItem( $fs, $itemComb ) )
					return( $res );
				if( $res === false )
					return( false );
			}

			return( null );
		}

		if( !isset( $fs -> dataDom -> aClass[ $this -> name ] ) )
			return( null );

		return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= '.';
		$sResult .= substr( $this -> name, 1, -1 );
	}
}

class CssSelFs_AttrBaseNode extends CssSelFs_Node
{
	public $operator;
	public $value;

	public function __construct( string $operator, ?string $value )
	{
		$this -> operator = $operator;
		$this -> value = $value;
	}

	public function getNs()
	{
		return( null );
	}

	public function getName()
	{
		return( '' );
	}

	static function Create( ?string $ns, string $attribute, string $operator, ?string $value )
	{
		if( !$ns && $attribute == 'class' )
			return( new CssSelFs_AttrClassNode( $operator, $value ) );
		return( new CssSelFs_AttrNode( $ns, $attribute, $operator, $value ) );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= '[';

		if( $this -> getNs() !== null )
		{
			$sResult .= '|';
			$sResult .= $this -> getNs();
		}
		$sResult .= $this -> getName();

		if( $this -> operator !== '' )
		{
			$sResult .= $this -> operator;
			if( $this -> value instanceof Sabberworm\CSS\Renderable )
				$this -> value -> render( $sResult, $oOutputFormat );
			else
				$sResult .= $this -> value;
		}

		$sResult .= ']';
	}
}

class CssSelFs_AttrNode extends CssSelFs_AttrBaseNode
{
	public $ns;
	public $attribute;

	public function __construct( ?string $ns, string $attribute, string $operator, ?string $value )
	{
		parent::__construct( $operator, $value );
		$this -> ns = $ns;
		$this -> attribute = $attribute;
	}

	public function getNs()
	{
		return( $this -> ns );
	}

	public function getName()
	{
		return( $this -> attribute );
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
		{
			$name = $this -> attribute;
			if( $this -> ns )
				$name = $this -> ns . ':' . $name;
			return( self::$g_aOp[ $this -> operator ]( $this, $fs, $item, $name ) );
		}

		if( $comb == ' ' || $comb == '>' )
		{
			$dataDom = $fs -> _getItemData( $item );

			if( $this -> attribute === 'id' && $this -> operator === '=' )
			{
				if( $dataDom === null || !isset( $dataDom -> aId[ ( string )$this -> value ][ $comb ] ) )
					return( null );

				foreach( $dataDom -> aId[ ( string )$this -> value ][ $comb ] as $item )
				{
					if( $res = $fnCombMatchItem( $fs, $item ) )
						return( $res );
					if( $res === false )
						return( false );
				}

				return( null );
			}

			if( $dataDom === null || !isset( $dataDom -> aAttr[ $this -> attribute ][ $comb ] ) )
				return( null );

			if( $this -> operator !== '' )
			{
				foreach( $dataDom -> aAttr[ $this -> attribute ][ $comb ] as $item )
				{
					$res = $this -> match( $fs, $item );
					if( $res === false )
						return( false );
					if( $res === null )
						continue;

					if( $res = $fnCombMatchItem( $fs, $item ) )
						return( $res );
					if( $res === false )
						return( false );
				}
			}
			else
			{
				foreach( $dataDom -> aAttr[ $this -> attribute ][ $comb ] as $item )
				{
					if( $res = $fnCombMatchItem( $fs, $item ) )
						return( $res );
					if( $res === false )
						return( false );
				}
			}

			return( null );
		}

		if( !isset( $fs -> dataDom -> aAttr[ $this -> attribute ] ) )
			return( null );

		return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

	}

	protected static $g_aOp = array(
		''			=> 'seraph_accel\\CssSelFs_AttrNode::_opExist',
		'='					=> 'seraph_accel\\CssSelFs_AttrNode::_opEqual',
		'!='				=> 'seraph_accel\\CssSelFs_AttrNode::_opNotEqual',
		'~='				=> 'seraph_accel\\CssSelFs_AttrNode::_opInclude',
		'|='				=> 'seraph_accel\\CssSelFs_AttrNode::_opDashMatch',
		'^='				=> 'seraph_accel\\CssSelFs_AttrNode::_opPrefixMatch',
		'$='				=> 'seraph_accel\\CssSelFs_AttrNode::_opSuffixMatch',
		'*='				=> 'seraph_accel\\CssSelFs_AttrNode::_opSubstringMatch',
	);

	static private function _opResTrue( $attr, $v )
	{
		return( $attr === 'id' && strpos( $v, '*' ) !== false ? 'ANY' : true );
	}

	static function _opExist( $thisP, $fs, $item, $attr )
	{
		return( $item -> hasAttribute( $attr ) ? true : null );
	}

	static function _opEqual( $thisP, $fs, $item, $attr )
	{
		return( ( string )$item -> getAttribute( $attr ) == ( string )$thisP -> value ? self::_opResTrue( $attr, ( string )$thisP -> value ) : null );
	}

	static function _opNotEqual( $thisP, $fs, $item, $attr )
	{
		return( CssSelFs_Node::_getNegateRes( self::_opEqual( $thisP, $fs, $item, $attr ) ) );
	}

	static function _opInclude( $thisP, $fs, $item, $attr )
	{
		return( strpos( ' ' . ( string )$item -> getAttribute( $attr ) . ' ', ' ' . ( string )$thisP -> value . ' ' ) !== false ? self::_opResTrue( $attr, ( string )$thisP -> value ) : null );
	}

	static function _opDashMatch( $thisP, $fs, $item, $attr )
	{
		$vAttr = ( string )$item -> getAttribute( $attr );
		$v = ( string )$thisP -> value;
		return( ( $vAttr == $v || Gen::StrStartsWith( $vAttr, $v . '-' ) ) ? self::_opResTrue( $attr, $v ) : null );
	}

	static function _opPrefixMatch( $thisP, $fs, $item, $attr )
	{
		return( Gen::StrStartsWith( ( string )$item -> getAttribute( $attr ), ( string )$thisP -> value ) ? self::_opResTrue( $attr, ( string )$thisP -> value ) : null );
	}

	static function _opSuffixMatch( $thisP, $fs, $item, $attr )
	{
		return( Gen::StrEndsWith( ( string )$item -> getAttribute( $attr ), ( string )$thisP -> value ) ? self::_opResTrue( $attr, ( string )$thisP -> value ) : null );
	}

	static function _opSubstringMatch( $thisP, $fs, $item, $attr )
	{
		return( strpos( ( string )$item -> getAttribute( $attr ), ( string )$thisP -> value ) !== false ? self::_opResTrue( $attr, ( string )$thisP -> value ) : null );
	}

}

class CssSelFs_AttrClassNode extends CssSelFs_AttrBaseNode
{
	public function getName()
	{
		return( 'class' );
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( !$comb )
			return( self::$g_aOp[ $this -> operator ]( $this, $item ) );

		if( $comb == ' ' || $comb == '>' )
		{
			$dataDom = $fs -> _getItemData( $item );
			if( $dataDom === null || !isset( $dataDom -> aAttr[ 'class' ][ $comb ] ) )
				return( null );

			if( $this -> operator !== '' )
			{
				foreach( $dataDom -> aAttr[ 'class' ][ $comb ] as $item )
				{
					$res = $this -> match( $fs, $item );
					if( $res === false )
						return( false );
					if( $res === null )
						continue;

					if( $res = $fnCombMatchItem( $fs, $item ) )
						return( $res );
					if( $res === false )
						return( false );
				}
			}
			else
			{
				foreach( $dataDom -> aAttr[ 'class' ][ $comb ] as $item )
				{
					if( $res = $fnCombMatchItem( $fs, $item ) )
						return( $res );
					if( $res === false )
						return( false );
				}
			}

			return( null );
		}

		if( !isset( $fs -> dataDom -> aAttr[ 'class' ] ) )
			return( null );

		return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );

	}

	protected static $g_aOp = array(
		''			=> 'seraph_accel\\CssSelFs_AttrClassNode::_opExist',
		'='					=> 'seraph_accel\\CssSelFs_AttrClassNode::_opEqual',
		'!='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opNotEqual',
		'~='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opInclude',
		'|='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opDashMatch',
		'^='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opPrefixMatch',
		'$='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opSuffixMatch',
		'*='				=> 'seraph_accel\\CssSelFs_AttrClassNode::_opSubstringMatch',
	);

	static private function _opResTrue( $v )
	{
		return( strpos( $v, '*' ) !== false ? 'ANY' : true );
	}

	static function _opExist( $thisP, $item )
	{
		return( $item -> hasAttribute( 'class' ) ? true : null );
	}

	static function _opEqual( $thisP, $item )
	{
		return( ( string )$item -> getAttribute( 'class' ) == ( '| ' . ( string )$thisP -> value . ' |' ) ? self::_opResTrue( ( string )$thisP -> value ) : null );
	}

	static function _opNotEqual( $thisP, $item )
	{
		return( CssSelFs_Node::_getNegateRes( self::_opEqual( $thisP, $item ) ) );
	}

	static function _opInclude( $thisP, $item )
	{
		return( strpos( ' ' . ( string )$item -> getAttribute( 'class' ) . ' ', ' ' . ( string )$thisP -> value . ' ' ) !== false ? self::_opResTrue( ( string )$thisP -> value ) : null );
	}

	static function _opDashMatch( $thisP, $item )
	{
		$vAttr = ( string )$item -> getAttribute( 'class' );
		$v = ( string )$thisP -> value;
		return( ( $vAttr == ( '| ' . $v . ' |' ) || Gen::StrStartsWith( $vAttr, '| ' . $v . '-' ) ) ? self::_opResTrue( $v ) : null );
	}

	static function _opPrefixMatch( $thisP, $item )
	{
		return( Gen::StrStartsWith( ( string )$item -> getAttribute( 'class' ), '| ' . ( string )$thisP -> value ) ? self::_opResTrue( ( string )$thisP -> value ) : null );
	}

	static function _opSuffixMatch( $thisP, $item )
	{
		return( Gen::StrEndsWith( ( string )$item -> getAttribute( 'class' ), ( string )$thisP -> value . ' |' ) ? self::_opResTrue( ( string )$thisP -> value ) : null );
	}

	static function _opSubstringMatch( $thisP, $item )
	{
		return( strpos( ( string )$item -> getAttribute( 'class' ), ( string )$thisP -> value ) !== false ? self::_opResTrue( ( string )$thisP -> value ) : null );
	}

}

class CssSelFs_SubSelsNode extends CssSelFs_Node
{
	public $sub;
	public $name;

	public function __construct( CssSelFs_Node $sub, string $name )
	{
		$this -> sub = $sub;
		$this -> name = $name;
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		return( $this -> sub -> match( $fs, $item, $comb, $fnCombMatchItem ) );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= ':';
		$sResult .= $this -> name;
		$sResult .= '(';

		$this -> sub -> render( $sResult, $oOutputFormat );

		$sResult .= ')';
	}
}

class CssSelFs_HavingNode extends CssSelFs_Node
{
	public $sub;

	public function __construct( CssSelFs_Node $sub )
	{
		$this -> sub = $sub;
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( $comb )
			return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );
		return( $this -> sub -> match( $fs, $item ) );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= ':has(';
		$this -> sub -> render( $sResult, $oOutputFormat );
		$sResult .= ')';
	}
}

class CssSelFs_NegationNode extends CssSelFs_Node
{
	public $sub;

	public function __construct( CssSelFs_Node $sub )
	{
		$this -> sub = $sub;
	}

	public function match( CssSelFs $fs, $item, $comb = null, $fnCombMatchItem = null )
	{
		if( $comb )
			return( parent::match( $fs, $item, $comb, $fnCombMatchItem ) );
		return( CssSelFs_Node::_getNegateRes( $this -> sub -> match( $fs, $item ) ) );
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= ':not(';
		$this -> sub -> render( $sResult, $oOutputFormat );
		$sResult .= ')';
	}
}

class CssSelFs_PseudoNode extends CssSelFs_Node
{
	private $identifier;

	public function __construct( string $identifier )
	{
		$this -> identifier = $identifier;
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= ':';
		$sResult .= $this -> identifier;
	}
}

class CssSelFs_FunctionNode extends CssSelFs_Node
{
	public $name;
	public $arguments;

	public function __construct( string $name, array $arguments = [] )
	{
		$this -> name = $name;
		$this -> arguments = $arguments;
	}

	public function render( string &$sResult, Sabberworm\CSS\OutputFormat $oOutputFormat )
	{
		$sResult .= ':';
		$sResult .= $this -> name;
		$sResult .= '(';

		$oOutputFormat -> getFormatter() -> implode( $sResult, ' ', $this -> arguments );

		$sResult .= ')';
	}
}


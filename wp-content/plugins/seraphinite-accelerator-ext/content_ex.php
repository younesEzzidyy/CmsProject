<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class ContSkeletonHash_MatchAll extends \DOMAttr
{
	public function __construct( $aAttrs, $glob, $aArg )
	{
		parent::__construct( 'd' );

		if( $glob )
			$this -> attr = $aAttrs[ 0 ] -> nodeName;
		else
			$this -> aAttr = $aAttrs;

		$this -> aPattern = $aArg;
		array_shift( $this -> aPattern );
	}
}

class DomElementEx extends \DOMElement
{
	public $dataDomFs;

}

class LazyCont_XpathExtFunc_FollowingSiblingUpToParent_Iterator extends \DOMAttr implements \Iterator
{
	public function __construct( $aNdPrev, $aNdParent = null )
	{
		parent::__construct( '_' );
		$this -> aNdPrev = $aNdPrev;
		$this -> aNdParent = $aNdParent;
	}

	#[\ReturnTypeWillChange]
	function current()
	{
		return( $this -> ndCur );
	}

	#[\ReturnTypeWillChange]
	function key()
	{
		return( -1 );
	}

	#[\ReturnTypeWillChange]
	function next()
	{
		do
		{
			while( $this -> ndCur = HtmlNd::GetNextTreeSibling( $this -> ndCur, $this -> aNdParent ) )
				if( $this -> ndCur -> nodeType == XML_ELEMENT_NODE )
					return;
		}
		while( $this -> ndCur = next( $this -> aNdPrev ) );
	}

	#[\ReturnTypeWillChange]
	function rewind()
	{
		reset( $this -> aNdPrev );
		$this -> ndCur = current( $this -> aNdPrev );
		$this -> next();
	}

	#[\ReturnTypeWillChange]
	function valid()
	{
		return( !!$this -> ndCur );
	}

	private $aNdPrev;
	private $aNdParent;

	private $ndCur;
}


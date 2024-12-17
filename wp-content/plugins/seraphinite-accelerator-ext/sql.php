<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'seraph_accel\\PHPSQLParser' ) === 0 )
			@include_once( __DIR__ . '/Cmn/Ext/PHP/' . str_replace( '\\', '/', substr( $class, 13 ) ) . '.php' );
	}
);

function Sql_Val2QueryStr( $v )
{
	if( is_string( $v ) )
		return( '\'' . esc_sql( $v ) . '\'' );
	return( esc_sql( '' . $v ) );
}

function _Sql_GetNoquotesName( $v )
{
	return( Gen::GetArrField( $v, array( 'no_quotes', 'parts', 0 ) ) );
}

function _Sql_GetVal( $v )
{
	$v = Gen::GetArrField( $v, array( 'base_expr' ) );
	if( substr( $v, 0, 1 ) == '\'' )
		return( @trim( $v, '\'' ) );
	return( @floatval( $v ) );
}

function Sql_GetWhereVals( array &$vals, array $where )
{
	https:

	$exprFirst = (isset($where[ 0 ])?$where[ 0 ]:null);

	if( Gen::GetArrField( $exprFirst, array( 'expr_type' ) ) == 'operator' && Gen::GetArrField( $exprFirst, array( 'base_expr' ) ) == 'NOT' )
		return( array() );

	$whereItem = array();
	$whereItems = array();
	foreach( $where as $expr )
	{
		$exprType = Gen::GetArrField( $expr, array( 'expr_type' ) );
		$exprName = Gen::GetArrField( $expr, array( 'base_expr' ) );

		if( $exprType == 'operator' )
		{
			if( $exprName == 'AND' )
			{
				if( $whereItem )
				{
					$whereItems[] = $whereItem;
					$whereItem = array();
				}

				continue;
			}
			else if( $exprName != '=' )
				return( null );
		}

		$whereItem[] = $expr;
	}

	if( $whereItem )
		$whereItems[] = $whereItem;

	foreach( $whereItems as $whereItem )
	{
		$col = (isset($whereItem[ 0 ])?$whereItem[ 0 ]:null);
		if( Gen::GetArrField( $col, array( 'expr_type' ) ) != 'colref' )
			continue;

		$colName = _Sql_GetNoquotesName( $col );

		$operator = (isset($whereItem[ 1 ])?$whereItem[ 1 ]:null);
		if( !$operator || Gen::GetArrField( $operator, array( 'expr_type' ) ) != 'operator' || Gen::GetArrField( $operator, array( 'base_expr' ) ) != '=' )
			continue;

		$val = (isset($whereItem[ 2 ])?$whereItem[ 2 ]:null);
		if( Gen::GetArrField( $val, array( 'expr_type' ) ) != 'const' )
			continue;

		$colVal = _Sql_GetVal( $val );
		$vals[ $colName ] = $colVal;
	}

	return( $vals );
}

function Sql_GetQueryModificationInfo( $query )
{
	$oper = @strtoupper( @substr( $query, 0, 7 + 1 ) );
	$operFound = false;
	foreach( array( 'INSERT', 'DELETE', 'UPDATE', 'REPLACE' ) as $opCode )
	{
		if( @strpos( $oper, $opCode . ' ' ) === 0 )
		{
			$oper = $opCode;
			$operFound = true;
			break;
		}
	}

	if( !$operFound )
		return( null );

	$parseRes = null;
	try { $parseRes = new PHPSQLParser\PHPSQLParser( $query, true ); } catch( \Exception $e ) {}

	if( !$parseRes || !$parseRes -> parsed )
		return( null );

	$parseRes = $parseRes -> parsed;

	switch( $oper )
	{
	case 'INSERT':
	case 'REPLACE':
	{

		if( Gen::GetArrField( $parseRes, array( $oper, 0, 'base_expr' ) ) != 'INTO' )
			return( null );

		$table = Gen::GetArrField( $parseRes, array( $oper, 1 ) );
		if( Gen::GetArrField( $table, array( 'expr_type' ) ) != 'table' )
			return( null );
		$table = _Sql_GetNoquotesName( $table );

		{
			$colList = Gen::GetArrField( $parseRes, array( $oper, 2 ), array() );
			if( Gen::GetArrField( $colList, array( 'expr_type' ) ) != 'column-list' )
				return( null );
			$colList = Gen::GetArrField( $colList, array( 'sub_tree' ), array() );
		}

		{
			$colVals = Gen::GetArrField( $parseRes, array( 'VALUES', 0 ), array() );
			if( Gen::GetArrField( $colVals, array( 'expr_type' ) ) != 'record' )
				return( null );
			$colVals = Gen::GetArrField( $colVals, array( 'data' ), array() );
		}

		$vals = array();
		foreach( $colList as $colIdx => $col )
		{
			if( Gen::GetArrField( $col, array( 'expr_type' ) ) != 'colref' )
				continue;

			$colName = _Sql_GetNoquotesName( $col );
			if( !$colName )
				return( null );

			$colVal = _Sql_GetVal( (isset($colVals[ $colIdx ])?$colVals[ $colIdx ]:null) );
			$vals[ $colName ] = $colVal;
		}

	} break;

	case 'UPDATE':
	{

		$tables = Gen::GetArrField( $parseRes, array( $oper ), array() );
		if( count( $tables ) != 1 )
			return( null );

		$table = (isset($tables[ 0 ])?$tables[ 0 ]:null);
		if( Gen::GetArrField( $table, array( 'expr_type' ) ) != 'table' )
			return( null );
		$table = _Sql_GetNoquotesName( $table );

		$vals = array();

		Sql_GetWhereVals( $vals, Gen::GetArrField( $parseRes, array( 'WHERE' ), array() ) );

		foreach( Gen::GetArrField( $parseRes, array( 'SET' ), array() ) as $colIdx => $col )
		{
			if( Gen::GetArrField( $col, array( 'expr_type' ) ) != 'expression' )
				continue;

			$colArgs = Gen::GetArrField( $col, array( 'sub_tree' ), array() );
			if( count( $colArgs ) != 3 )
				continue;

			$colOp = (isset($colArgs[ 1 ])?$colArgs[ 1 ]:null);
			if( Gen::GetArrField( $colOp, array( 'expr_type' ) ) != 'operator' || Gen::GetArrField( $colOp, array( 'base_expr' ) ) != '=' )
				continue;

			$colName = (isset($colArgs[ 0 ])?$colArgs[ 0 ]:null);
			if( Gen::GetArrField( $colName, array( 'expr_type' ) ) != 'colref' )
				continue;

			$colVal = (isset($colArgs[ 2 ])?$colArgs[ 2 ]:null);
			if( Gen::GetArrField( $colVal, array( 'expr_type' ) ) != 'const' )
				continue;

			$colName = _Sql_GetNoquotesName( $colName );
			if( !$colName )
				continue;

			$colVal = _Sql_GetVal( $colVal );
			$vals[ $colName ] = $colVal;
		}

	} break;

	case 'DELETE':
	{

		$tables = Gen::GetArrField( $parseRes, array( 'FROM' ), array() );
		if( count( $tables ) != 1 )
			return( null );

		$table = (isset($tables[ 0 ])?$tables[ 0 ]:null);
		if( Gen::GetArrField( $table, array( 'expr_type' ) ) != 'table' )
			return( null );
		$table = _Sql_GetNoquotesName( $table );

		$vals = array();

		Sql_GetWhereVals( $vals, Gen::GetArrField( $parseRes, array( 'WHERE' ), array() ) );

	} break;
	}

	return( array( 'table' => $table, 'oper' => $oper, 'data' => $vals ) );
}


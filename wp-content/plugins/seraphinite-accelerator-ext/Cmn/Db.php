<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class Db
{
	static function GetTblPrefix( $name = '', $blog = false )
	{
		global $wpdb;
		return( ( $blog ? $wpdb -> prefix : $wpdb -> base_prefix ) . $name );
	}

	static function GetSysTbl( $id  )
	{
		global $wpdb;
		return( $wpdb -> { $id } );
	}
}

class DbTbl
{
	static function CreateUpdate( $id, $cols )
	{
		global $wpdb;

		$charset_collate = $wpdb -> get_charset_collate();

		$queryCols = '';
		$querySuffix = '';

		foreach( $cols as $colId => $col )
		{
			$queryColsAttr = '' . $colId . ' ' . strtolower( $col[ 'type' ] );

			$colAttrs = (isset($col[ 'attrs' ])?$col[ 'attrs' ]:null);
			if( $colAttrs )
			{
				foreach( $colAttrs as $colAttr )
				{
					$querySuffixAttr = null;
					switch( $colAttr )
					{
					case 'PRIMARY KEY':
						$querySuffixAttr = 'PRIMARY KEY  (' . $colId . ')';
						break;

					case 'KEY':
					case 'INDEX':
						$querySuffixAttr = 'KEY ' . $colId . ' (' . $colId . ')';
						break;

					default:
						$queryColsAttr .= ' ' . $colAttr;
						break;
					}

					if( $querySuffixAttr )
						self::_AddCommaArg( $querySuffix, $querySuffixAttr, "\n" );
				}
			}

			self::_AddCommaArg( $queryCols, $queryColsAttr, "\n" );
		}

		$query = 'CREATE TABLE ' . $id . ' (' . "\n" . $queryCols;
		self::_AddCommaArg( $query, $querySuffix, "\n" );
		$query .= "\n" . ') ' . $charset_collate . ';';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$suppress = $wpdb -> suppress_errors();
		dbDelta( $query );
		$wpdb -> suppress_errors( $suppress );
	}

	static function Delete( $id )
	{
		global $wpdb;
		$suppress = $wpdb -> suppress_errors();
		$res = $wpdb -> query( 'DROP TABLE ' . $id . '' );
		$wpdb -> suppress_errors( $suppress );
		return( $res );
	}

	static function QueryId( $id )
	{
		return( '`' . str_replace( '.', '`.`', $id ) . '`' );
	}

	static private function _QueryLimit( $limit )
	{
		$sql = '';
		if( $limit )
			$sql = ' LIMIT ' . ( is_array( $limit ) ? implode( ',', $limit ) : $limit );
		return( $sql );
	}

	static private function _QueryOrder( $order )
	{
		$sql = '';

		if( $order )
		{
			$orderArgs = array();
			foreach( $order as $orderCol => $orderColDir )
				$orderArgs[] = DbTbl::QueryId( $orderCol ) . ' ' . $orderColDir;

			if( $orderArgs )
			{
				$orderArgs = implode( ',', $orderArgs );
				$sql .= ' ORDER BY ' . $orderArgs;
			}
		}

		return( $sql );
	}

	static private function _QueryGroup( $group )
	{
		$sql = '';

		if( $group )
		{
			$group = implode( ',', $group );
			$sql .= ' GROUP BY ' . DbTbl::QueryId( $group );
		}

		return( $sql );
	}

	static function Query( $sql, $limit = null, $order = null )
	{
		global $wpdb;

		$sql .= self::_QueryOrder( $order );
		$sql .= self::_QueryLimit( $limit );

		return( $wpdb -> query( $sql ) );
	}

	static function GetRowsEx( $sqlFrom, $cols = null, $limit = null, $where = null, $order = null, $output = ARRAY_A, $prms = array() )
	{
		global $wpdb;

		if( $where )
		{
			$where = self::_process_fields( $where, self::_GetFormat( $where ) );
			if( false === $where )
				return( false );
		}

		$fields = array();
		$conditions = array();
		$values = array();

		if( is_array( $cols ) )
		{
			foreach( $cols as $field )
				$fields[] = DbTbl::QueryId( $field );
		}
		else
			$fields[] = '*';

		if( $where )
		{
			foreach( $where as $field => $value )
			{
				if( is_null( $value[ 'value' ] ) )
				{
					$conditions[] = DbTbl::QueryId( $field ) . ' IS NULL';
					continue;
				}

				if( is_array( $value[ 'value' ] ) )
				{
					$condition = '';
					foreach( $value[ 'value' ] as $v )
					{
						if( $condition )
							$condition .= ',';
						$condition .= $value[ 'format' ];
						$values[] = $v;
					}

					$conditions[] = DbTbl::QueryId( $field ) . ' IN (' . $condition . ')';
					continue;
				}

				$conditions[] = DbTbl::QueryId( $field ) . ' = ' . $value[ 'format' ];
				$values[] = $value[ 'value' ];
			}
		}

		if( $extraWhere = (isset($prms[ 'extraWhere' ])?$prms[ 'extraWhere' ]:null) )
		{
			if( !is_array( $extraWhere ) )
				$extraWhere = array( $extraWhere );
			$conditions = array_merge( $conditions, array_map( function( $e ) { return( str_replace( '%', '%%', $e ) ); }, $extraWhere ) );
		}

		$fields = implode( ',', $fields );
		$conditions = implode( ' AND ', $conditions );

		if( (isset($prms[ 'distinct' ])?$prms[ 'distinct' ]:null) )
			$fields = 'DISTINCT(' . $fields . ')';
		if( (isset($prms[ 'count' ])?$prms[ 'count' ]:null) )
			$fields = 'COUNT(' . $fields . ')';

		$sql = 'SELECT ' . $fields . ' FROM ' . $sqlFrom;
		if( $conditions )
			$sql .= ' WHERE ' . $conditions;

		$sql .= self::_QueryGroup( (isset($prms[ 'group' ])?$prms[ 'group' ]:null) );
		$sql .= self::_QueryOrder( $order );
		$sql .= self::_QueryLimit( $limit );

		$suppress = $wpdb -> suppress_errors();
		$res = $wpdb -> get_results( $values ? $wpdb -> prepare( $sql, $values ) : $sql, $output );
		$wpdb -> suppress_errors( $suppress );
		return( $res );
	}

	static function GetRows( $id, $cols = null, $limit = null, $where = null, $order = null, $output = ARRAY_A, $prms = array() )
	{
		return( DbTbl::GetRowsEx( DbTbl::QueryId( $id ), $cols, $limit, $where, $order, $output, $prms ) );
	}

	static function InsertRow( $id, $data )
	{
		global $wpdb;

		$suppress = $wpdb -> suppress_errors();
		try { $res = $wpdb -> insert( $id, $data, self::_GetFormat( $data ) ); } catch( \Exception $e ) { $res = false; }
		$wpdb -> suppress_errors( $suppress );
		return( $res );
	}

	static function UpdateRows( $id, $data, $where )
	{
		global $wpdb;

		$suppress = $wpdb -> suppress_errors();
		$res = $wpdb -> update( $id, $data, $where, self::_GetFormat( $data ), self::_GetFormat( $where ) );
		$wpdb -> suppress_errors( $suppress );
		return( $res );
	}

	static function InsertUpdateRow( $id, $key, $data, $dataEx )
	{

		global $wpdb;

		{
			$key = self::_process_fields( $key, self::_GetFormat( $key ) );
			if( false === $key )
				return( false );
		}

		{
			$data = self::_process_fields( $data, self::_GetFormat( $data ) );
			if( false === $data )
				return( false );
		}

		{
			$dataEx = self::_process_fields( $dataEx, self::_GetFormat( $dataEx ) );
			if( false === $dataEx )
				return( false );
		}

		$fields = array();
		$formats = array();
		$fieldsUpdate = array();
		$values = array();

		foreach( $key as $field => $value )
		{
			$fields[] = $field;
			$formats[] = $value[ 'format' ];
			$values[] = $value[ 'value' ];
		}

		foreach( $data as $field => $value )
		{
			$fields[] = $field;
			$formats[] = $value[ 'format' ];
			$values[] = $value[ 'value' ];
		}

		foreach( $dataEx as $field => $value )
		{
			$fields[] = $field;
			$formats[] = $value[ 'format' ];
			$values[] = $value[ 'value' ];
		}

		foreach( $dataEx as $field => $value )
		{
			$fieldsUpdate[] = "`$field` = " . $value[ 'format' ];
			$values[] = $value[ 'value' ];
		}

		$fields = '`' . implode( '`, `', $fields ) . '`';
		$formats = implode( ', ', $formats );
		$fieldsUpdate = implode( ', ', $fieldsUpdate );

		$sql = "INSERT INTO `$id` ($fields) VALUES ($formats) ON DUPLICATE KEY UPDATE $fieldsUpdate";

		$suppress = $wpdb -> suppress_errors();
		try { $res = $wpdb -> query( $wpdb -> prepare( $sql, $values ) ); } catch( \Exception $e ) { $res = false; }
		$wpdb -> suppress_errors( $suppress );

		return( $res );
	}

	static function DeleteRows( $id, $where = null )
	{
		global $wpdb;

		$suppress = $wpdb -> suppress_errors();
		$res = $where ? $wpdb -> delete( $id, $where, self::_GetFormat( $where ) ) : $wpdb -> query( 'DELETE FROM `' . $id . '`' );
		$wpdb -> suppress_errors( $suppress );
		return( $res );
	}

	static function GetCountFromRowsResult( $res, $defVal = 0 )
	{
		return( $res ? intval( Gen::ArrGetByPos( $res[ 0 ], 0 ) ) : $defVal );
	}

	private static function _AddCommaArg( &$str, $arg, $commaSuffix = '' )
	{
		if( empty( $arg ) )
			return;

		if( !empty( $str ) )
			$str .= ',' . $commaSuffix;
		$str .= $arg;
	}

	protected static function _GetFormat( $data )
	{
		$formats = array();

		foreach( $data as $field => $value )
		{
			$fmt = '%s';

			if( is_array( $value ) )
				$value = (isset($value[ 0 ])?$value[ 0 ]:null);

			switch( gettype( $value ) )
			{
			case 'integer':	$fmt = '%d'; break;
			case 'boolean':	$fmt = '%u'; break;
			case 'double':	$fmt = '%f'; break;
			}

			$formats[] = $fmt;
		}

		return( $formats );
	}

	protected static function _process_fields( $data, $format )
	{
		global $wpdb;

		$formats          = ( array )$format;
		$original_formats = $formats;

		foreach( $data as $field => $value )
		{
			$value = array(
				'value'  => $value,
				'format' => '%s',
			);

			if ( ! empty( $format ) ) {
				$value['format'] = array_shift( $formats );
				if ( ! $value['format'] ) {
					$value['format'] = reset( $original_formats );
				}
			} elseif ( isset( $wpdb->field_types[ $field ] ) ) {
				$value['format'] = $wpdb->field_types[ $field ];
			}

			$data[ $field ] = $value;
		}

		return( $data );
	}
}

class DbTran
{

	const IsolLvl_Def			= null;
	const IsolLvl_RepRead		= 'REPEATABLE READ';
	const IsolLvl_ReadComm		= 'READ COMMITTED';
	const IsolLvl_ReadUncomm	= 'READ UNCOMMITTED';
	const IsolLvl_Ser			= 'SERIALIZABLE';

	function __destruct()
	{
		$this -> Close();
	}

	function Open( $isolLvl = self::IsolLvl_Def )
	{
		global $wpdb;

		if( $this -> _active )
			return( true );

		$res = true;
		if( $isolLvl )
		    $res = $wpdb -> query( 'SET TRANSACTION ISOLATION LEVEL ' . $isolLvl );

		if( $res !== false )
			$res = $wpdb -> query( 'START TRANSACTION' );

		if( $res !== false )
			$this -> _active = true;
		return( $res );
	}

	function Close( $commit = false )
	{
		global $wpdb;

		if( !$this -> _active )
			return( true );

		$res = $wpdb -> query( $commit ? 'COMMIT' : 'ROLLBACK' );
		if( $res !== false )
			$this -> _active = false;
		return( $res );
	}

	private $_active = false;
}


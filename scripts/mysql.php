<?php

define( 'DB_HOST', 'localhost' );
define( 'DB_USER', 'root' );
define( 'DB_PASS', '111111' );
define( 'DB_DEFAULT', 'ceres' );


$db_link = NULL;


function db_sql()
{   
  GLOBAL $db_link;
  
  // формирование запроса из переданных параметров
  $sql = func_get_arg( 0 );
  $argInd = 1;
    
  if ( is_array( $sql ) )
  {
    $params = $sql;
    $sql = func_get_arg( 1 );
    $argInd++;
  }else
    $params = [];
  
  $sqlSrc = $sql;
  
  $offset = 0;
  
  $argCnt = func_num_args();
  for( ; $argInd < $argCnt; $argInd++ )
  {
    $v = func_get_arg( $argInd );
    
    if ( ( $i = strpos( $sql, '?', $offset ) ) === false )
      throw new Exception("Wrong params count", 500);
    
    if ( is_null( $v ) )
    { 
      $quote = '';
      $equal = '';
      $vS = 'NULL';
    }else
    if ( is_array( $v ) )
    {
      if ( isset( $v['asis'] ) )
      {
        $quote = '';
        $equal = isset( $v['='] ) ? '=' : '';
        $vS = $v['asis'];
      }else{
        $val = $v['val'];
        
        if ( isset($v['AS_IS']) )
        {
          $quote = '';
          $equal = isset( $v['='] ) ? '=' : '';
          $vS = $val;
        }else
        if ( is_null( $val ) )
        {
          $quote = '';
          $equal = isset( $v['='] ) ? 'IS ' : '';
          $vS = 'NULL';
        }else{
          $quote = '\'';
          $equal = isset( $v['='] ) ? '=' : '';
          $vS = addslashes( $val );
        }
      }
      
    }else{
      
      if ( is_integer( $v ) )
      {
        $equal = '';
        $quote = '';
        $vS = $v;
        
      }else{
        $equal = '';
        $quote = '\'';
        $vS = addslashes( $v );
      }
    }

    $sql = substr( $sql, 0, $i ) . $equal . $quote . $vS . $quote . substr( $sql, $i + 1 );

    $offset = $i + strlen( $vS ) + 1;
  }
  //
  
  if ( !$db_link )
      db_connect();

  $r = mysqli_query( $db_link, $sql );
  
  if ( !$r )
    throw new Exception("\n".mysqli_error( $db_link )."\n\n" . $sql ); 
  
  return $r;
}


function db_connect()
{
  global $db_link;

  $db_link = mysqli_connect( 
    DB_HOST, 
    DB_USER, 
    DB_PASS
  );
  mysqli_autocommit( $db_link, true );
  mysqli_query( $db_link, 'SET NAMES `utf8`');
  
  if ( DB_DEFAULT )
    db_sql( 'use ' . DB_DEFAULT );
}

function db_connected()
{
  global $db_link;
  return $db_link;
}

function db_get( &$rs )
{
  return mysqli_fetch_assoc( $rs );
}

function db_row( &$rs )
{
  return mysqli_fetch_row( $rs );
}

function db_cnt( &$rs )
{
  return mysqli_num_rows( $rs );
}
  
?>
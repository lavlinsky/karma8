<?php

/*
* Вывод в лог-файл.
*/
function _log( $str )
{
	if ( is_array( $str ) )
		$str = print_r( $str, 1 );
	
	file_put_contents( __DIR__.'/.log', $str . "\n", FILE_APPEND | LOCK_EX );
}


/*
* Кол-во процессов с указанной подстрокой
*/
function processes_cnt( $str )
{
  $cnt = 0;
  $out = [];
  exec( 'ps -eo cmd', $out );
  foreach( $out as $s )
  if ( strpos( $s, $str ) !== false && strpos( $s, '/bin/sh' ) === false )
    $cnt++;

  return $cnt;
}

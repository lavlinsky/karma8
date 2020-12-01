<?php

/*
* Скрипт ежеминутно вызывается из cron командой:
* nohup php -c "путь к конфигу php" runner.php "параметр" > /dev/null 2>&1 &
*/

		set_time_limit(60*60*3);

try
{
	include "util.php";
	include "mysql.php";
	include "email.php";

	if ( !($file = $argv[ 1 ] ) )
		throw new Exception("Parameter expected", 1);
	
	include $file . '.php';
	run();
	
}catch( Exception $e ) {
	
	if ( db_connected() )
		db_sql("rollback");
		
	_log( 'error: ' . $e->getCode() .': ' . $e->getMessage() );
}

?>
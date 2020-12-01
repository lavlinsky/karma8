<?php

define( 'MAX_TIME_ALLOWED', 60*60*23 );

function run()
{
	// Проверяем кол-во уже запущенных процессов,
	// с учетом того, что один процесс из этого
	// кол-ва - текущий.
	// Нагрузка на сервер может варьироваться тут.
	$allowed_cnt = 10;
	
	if ( processes_cnt( "runner.php mailer" ) > $allowed_cnt )
		return;
	//

	$time = time();

	do
	{
		db_sql("begin");

		$rs = db_sql("
			SELECT *
			FROM mails m
			ORDER BY id
			LIMIT 1
			FOR UPDATE SKIP LOCKED" );
		
		if ( $r = db_get( $rs ) )
		{
			send_email( 
				$r['email'], 
				$r['from'], 
				$r['to'], 
				$r['subj'], 
				$r['body'] 
			);


			db_sql("DELETE FROM mails WHERE id =?", $r['id'] );

			
			db_sql( "commit" );

		}else {
			// данных больше нет
			db_sql("rollback");
			break;
		}
			
		}while( time() < $time + MAX_TIME_ALLOWED );
	}
}

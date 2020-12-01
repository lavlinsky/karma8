<?php

define( 'EXPIRED_EDGE', 60*60*24*2 );
define( 'NOTIFY_MORATORY', 60*60*24*1 );


function run()
{
	// Проверяем кол-во уже запущенных процессов,
	// с учетом того, что один процесс из этого
	// кол-ва - текущий.
	// Нагрузка на сервер может варьироваться тут.
	$allowed_cnt = 10;
	
	if ( processes_cnt( "runner.php informer" ) > $allowed_cnt )
		return;
	//

	db_sql("begin");

	
	// Получаем и блокируем некоторое кол-во строк.
	$limit = 100;
	
	
	$rs = db_sql("
		SELECT u.email, u.username 
		FROM users u, emails e, email_informed ei
		WHERE u.email_confirmed = 1 
					AND u.validts < unix_timestamp() - ? 
					AND ei.email = e.email 
					AND ( ei.datets IS NULL 
								OR ei.datets < unix_timestamp() - ? )
					AND e.valid = 1
					AND e.email = u.email
					LIMIT ?
					FOR UPDATE SKIP LOCKED",
					EXPIRED_EDGE,
					NOTIFY_MORATORY,
					$limit
	);
	
	// explain
	//# id, select_type, table, partitions, type, possible_keys, key, key_len, ref, rows, filtered, Extra
	//1, SIMPLE, e, , ref, PRIMARY,emails_valid_email, emails_valid_email, 1, const, 1, 100.00, Using index
	//1, SIMPLE, u, , eq_ref, email_UNIQUE,email_confirmed_validts, email_UNIQUE, 302, karma.e.email, 1, 5.00, Using where
	//1, SIMPLE, ei, , eq_ref, PRIMARY,email_datets, PRIMARY, 302, karma.e.email, 1, 100.00, Using where
	
	while( $r = db_get( $rs ) )
	{
		$email = $r['email'];
		$username = $r['username'];
		
		db_sql("INSERT `email_informed` SET 
						email =?,
						datets = UNIX_TIMESTAMP()
						ON DUPLICATE KEY 
						datets = UNIX_TIMESTAMP()",
						$email
		);
		
		// сохраняем данные для последующей отправки письма
		make_mail( $email, $username );
		
		// фиксируем, что на этот емайл уже отправили
		db_sql("INSERT `email_for_send` SET 
						email =?,
						datets = UNIX_TIMESTAMP()
						ON DUPLICATE KEY 
						datets = UNIX_TIMESTAMP()",
						$email
		);
		
	}

	db_sql( "commit" );
}

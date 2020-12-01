<?php

function run()
{
	// Проверяем кол-во уже запущенных процессов,
	// с учетом того, что один процесс из этого
	// кол-ва - текущий.
	// Нагрузка на сервер может варьироваться тут.
	$allowed_cnt = 10;
	
	if ( processes_cnt( "runner.php checker" ) > $allowed_cnt )
		return;
	//


	db_sql("begin");
	
	// Получаем и блокируем некоторое кол-во строк.
	$limit = 2;
	
	$rs = db_sql( "SELECT e.email
							FROM `emails` e 
							LEFT JOIN `users` u USE INDEX(`email_email_confirmed`) 
								ON u.email = e.email and u.email_confirmed = 1
							WHERE e.checked = 0
							LIMIT $limit
							FOR UPDATE SKIP LOCKED" );
	// explain
	// # id, select_type, table, partitions, type, possible_keys, key, key_len, ref, rows, filtered, Extra
	// '1', 'SIMPLE', 'e', NULL, 'ref', 'emails_checked', 'emails_checked', '1', 'const', '34', '100.00', NULL
	// '1', 'SIMPLE', 'u', NULL, 'eq_ref', 'email_email_confirmed', 'email_confirmed', '304', 'karma.e.email,const', '1', '100.00', NULL
	
	while( $r = db_get( $rs ) )
	{
		$email = $r['email'];
		
		db_sql("UPDATE `emails` SET 
						emails_checked =?,
						emails_valid =?
						WHERE email =?",
						1,
						check_email( $email ),
						$email
		);
	}

	db_sql( "commit" );
}

<?php


function check_email( $email )
{
	return filter_var( $email, FILTER_VALIDATE_EMAIL) ? 1 : 0;
}


function make_mail( $email, $to )
{
  $subj = "Notification from the Best Company!";
  $body = "$to, you subscription is expiring".
  
  db_sql("
    INSERT `mails` SET
    email =?,
    to =?,
    subj =?,
    body =?",
    $email,
    $to,
    $subj,
    $body
  );  
}


function send_email( $email, $from, $to, $subj, $body )
{
  $subj = '=?utf-8?b?'.base64_encode($subj).'?=';
  $uid = strtoupper(uniqid(time()));  
  $from = 'noreply@karma8.io';
  
  $header  = "From: karma8 <$from>\n";  
  $header .= "X-Mailer: karma8\n";  
  $header .= "Mime-Version: 1.0\n";
  $header .= "Content-Type:multipart/mixed;";  
  $header .= "boundary=\"----------".$uid."\"\n\n";  
  
  $att  = "------------".$uid."\n";
  $att .= "Content-Type:text/html; charset=utf-8; Content-Transfer-Encoding: 8bit\n\n$body\n\n";  

	return;

  if ( !mail( $to, $subj, $att, $header ) )
    throw new Exception("Can`t send mail");
}

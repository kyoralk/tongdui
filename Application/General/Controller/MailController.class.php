<?php
namespace General\Controller;
use Think\Controller;

class MailController extends Controller{
	 
	static  function sendMail(){
		Vendor('swiftmailer.swift_required');
		$transport=\Swift_SmtpTransport::newInstance('smtp.163.com',25,'')->setUsername('laimaijiu0')->setPassword('4000634919');
		$mailer =\Swift_Mailer::newInstance($transport);
		$message=\Swift_Message::newInstance()->setSubject('ceshiyixia')->setFrom(array('laimaijiu0@163.com'=>'laimaijiu0'))->setTo('zhangbin1126@126.com')->setContentType("text/html")->setBody('hellobin');
		$mailer->protocol='smtp';
		$mailer->send($message);
	}
	
}
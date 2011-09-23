<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class ErrorMessageUtils
	{
		public static function sendMessage(Exception $e)
		{
			$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$uri =
				isset($_SERVER['HTTP_HOST']) && isset($_SERVER["REQUEST_URI"])
				? $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]
				: '';
			$ip =
				isset($_SERVER['HTTP_X_REAL_IP'])
				? $_SERVER['HTTP_X_REAL_IP']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER['HTTP_REFERER'] : '';
			$method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '';
			$exception = get_class($e);

			MailUtils::create()->
			setModel(
				Model::create()->
				set('server', $server)->
				set('uri', $uri)->
				set('ip', $ip)->
				set('useragent', $useragent)->
				set('referer', $referer)->
				set('method', $method)->
				set('exception', $exception)->
				set('e', $e)->
				set('from', FEEDBACK_EMAIL)->
				set('to', BUGLOVERS)->
				set('date', Timestamp::makeNow())
			)->
			setView('mail/errorMessage')->
			send();
		}

		public static function printMessage(Exception $e)
		{
			$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$uri =
				isset($_SERVER['HTTP_HOST']) && isset($_SERVER["REQUEST_URI"])
				? $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]
				: '';
			$ip =
				isset($_SERVER['HTTP_X_REAL_IP'])
				? $_SERVER['HTTP_X_REAL_IP']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
			$useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER['HTTP_REFERER'] : '';
			$method = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : '';
			$exception = get_class($e);
			$trace = nl2br($e->getTraceAsString());

			$str = <<<HTML
Class: $exception<br>
Message: {$e->getMessage()}<br>
File: {$e->getFile()}<br>
Line: {$e->getLine()}<br>
Code: {$e->getCode()}<br>
Trace: {$trace}<br><br>
Server: $server<br>
URI: $uri<br>
IP: $ip<br>
UserAgent: $useragent<br>
Referer: $referer<br>
Request method: $method<br>
HTML;
			return $str;
		}
	}
?>
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
//			if(
//				$e instanceof DatabaseException
//				|| strpos($e->getMessage(), 'mysql_connect') !== false
//			) return false;

			$server =
				isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: (defined('HTTP_HOST') ? HTTP_HOST : '');

			$uri =
				isset($_SERVER['REQUEST_URI'])
				? $server.$_SERVER['REQUEST_URI']
				: $_SERVER['PHP_SELF'];

			$ip =
				isset($_SERVER['HTTP_X_REAL_IP'])
				? $_SERVER['HTTP_X_REAL_IP']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			$ip2 =
				isset($_SERVER['HTTP_X_FORWARDED_FOR'])
				? $_SERVER['HTTP_X_FORWARDED_FOR']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			$useragent =
				isset($_SERVER['HTTP_USER_AGENT'])
				? $_SERVER['HTTP_USER_AGENT']
				: '';

			$referer =
				isset($_SERVER['HTTP_REFERER'])
				? $_SERVER['HTTP_REFERER']
				: '';

			$method =
				isset($_SERVER['REQUEST_METHOD'])
				? $_SERVER['REQUEST_METHOD']
				: '';

			$exception = get_class($e);

			$get = var_export($_GET, true);
			$post = var_export($_POST, true);
			$cookie = var_export($_COOKIE, true);

			MailUtils::create()->
			setModel(
				Model::create()->
				set('server', $server)->
				set('uri', $uri)->
				set('ip', $ip)->
				set('ip2', $ip2)->
				set('useragent', $useragent)->
				set('referer', $referer)->
				set('method', $method)->
				set('exception', $exception)->
				set('e', $e)->
				set('get', $get)->
				set('post', $post)->
				set('cookie', $cookie)->
				set('from', FEEDBACK_EMAIL)->
				set('to', BUGLOVERS)->
				set('date', Timestamp::makeNow())
			)->
			setView('mail/errorMessage')->
			setEncoding(DEFAULT_MAIL_ENCODING)->
			send();
		}

		public static function sendFatalError($message)
		{
			$server =
				isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: (defined('HTTP_HOST') ? HTTP_HOST : '');

			$uri =
				isset($_SERVER['REQUEST_URI'])
				? $server.$_SERVER['REQUEST_URI']
				: '';

			$ip =
				isset($_SERVER['HTTP_X_REAL_IP'])
				? $_SERVER['HTTP_X_REAL_IP']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			$ip2 =
				isset($_SERVER['HTTP_X_FORWARDED_FOR'])
				? $_SERVER['HTTP_X_FORWARDED_FOR']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			$useragent =
				isset($_SERVER['HTTP_USER_AGENT'])
				? $_SERVER['HTTP_USER_AGENT']
				: '';

			$referer =
				isset($_SERVER['HTTP_REFERER'])
				? $_SERVER['HTTP_REFERER']
				: '';

			$method =
				isset($_SERVER['REQUEST_METHOD'])
				? $_SERVER['REQUEST_METHOD']
				: '';

			$date = Timestamp::makeNow();

			$from = FEEDBACK_EMAIL;
			$to = BUGLOVERS;
			$subject = $uri.' - Fatal error - '.$message;

			$text =
				'Class: Fatal error<br>'
				.'Message: '.$message.'<br><br>'
				.'Server: '.$server.'<br>'
				.'URI: '.$uri.'<br>'
				.'IP: '.$ip.'<br>'
				.'IP2: '.$ip2.'<br>'
				.'UserAgent: '.$useragent.'<br>'
				.'Referer: '.$referer.'<br>'
				.'Request method: '.$method.'<br><br>'
				.'Message sent: '.$date->toString();

			Mail::create()->
			setContentType('text/html')->
			setFrom($from)->
			setTo($to)->
			setSubject($subject)->
			setText($text)->
			send();
		}

		public static function printMessage(Exception $e)
		{
			$server =
				isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: (defined('HTTP_HOST') ? HTTP_HOST : '');

			$uri =
				isset($_SERVER['REQUEST_URI'])
				? $server.$_SERVER['REQUEST_URI']
				: '';

			$ip =
				isset($_SERVER['HTTP_X_REAL_IP'])
				? $_SERVER['HTTP_X_REAL_IP']
				: isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

			$useragent =
				isset($_SERVER['HTTP_USER_AGENT'])
				? $_SERVER['HTTP_USER_AGENT']
				: '';

			$referer =
				isset($_SERVER['HTTP_REFERER'])
				? $_SERVER['HTTP_REFERER']
				: '';

			$method =
				isset($_SERVER['REQUEST_METHOD'])
				? $_SERVER['REQUEST_METHOD']
				: '';

			$exception = get_class($e);

			$trace =
				strpos($e->getMessage(), 'mysql_connect') === false
				? nl2br($e->getTraceAsString())
				: null;

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
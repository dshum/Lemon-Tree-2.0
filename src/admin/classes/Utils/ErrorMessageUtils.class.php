<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class ErrorMessageUtils
	{
		const TIME_DELAY = 60;

		public static function sendMessage(Exception $e)
		{
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

			if(!defined('ERRORS_DIR')) {
				define('ERRORS_DIR', ONPHP_TEMP_PATH.'errors/');
			}

			if(!is_dir(ERRORS_DIR)) {
				mkdir(ERRORS_DIR, 0755);
			}

			$filename = md5(
				$exception.' - '.$e->getMessage().' - '.$e->getTraceAsString()
			);

			$send = false;
			$count = 0;
			$diff = 0;

			if(file_exists(ERRORS_DIR.$filename)) {
				$time = filemtime(ERRORS_DIR.$filename);
				if(time() - $time > self::TIME_DELAY) {
					$count = self::reset(ERRORS_DIR.$filename);
					$diff = time() - $time;
					$send = true;
				} else {
					self::increment(ERRORS_DIR.$filename);
				}
			} else {
				self::reset(ERRORS_DIR.$filename);
				$send = true;
			}

			if($send) {
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
					set('count', $count)->
					set('diff', $diff)->
					set('from', FEEDBACK_EMAIL)->
					set('to', BUGLOVERS)->
					set('date', Timestamp::makeNow())
				)->
				setView('mail/errorMessage')->
				setEncoding(DEFAULT_MAIL_ENCODING)->
				send();
			}
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

			if(!defined('ERRORS_DIR')) {
				define('ERRORS_DIR', ONPHP_TEMP_PATH.'errors/');
			}

			if(!is_dir(ERRORS_DIR)) {
				mkdir(ERRORS_DIR, 0755);
			}

			$filename = md5(
				'Fatal error - '.$message
			);

			$send = false;
			$count = 0;
			$diff = 0;

			if(file_exists(ERRORS_DIR.$filename)) {
				$time = filemtime(ERRORS_DIR.$filename);
				if(time() - $time > self::TIME_DELAY) {
					$count = self::reset(ERRORS_DIR.$filename);
					$diff = time() - $time;
					$send = true;
				} else {
					self::increment(ERRORS_DIR.$filename);
				}
			} else {
				self::reset(ERRORS_DIR.$filename);
				$send = true;
			}

			$text =
				'Class: Fatal error<br>'
				.'Message: '.$message.'<br><br>'
				.($count ? $count.' error(s) per '.$diff.' sec<br><br>' : '')
				.'Server: '.$server.'<br>'
				.'URI: '.$uri.'<br>'
				.'IP: '.$ip.'<br>'
				.'IP2: '.$ip2.'<br>'
				.'UserAgent: '.$useragent.'<br>'
				.'Referer: '.$referer.'<br>'
				.'Request method: '.$method.'<br><br>'
				.'Message sent: '.$date->toString();

			if($send) {
				Mail::create()->
				setContentType('text/html')->
				setFrom($from)->
				setTo($to)->
				setSubject($subject)->
				setText($text)->
				send();
			}
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

			$get = var_export($_GET, true);
			$post = var_export($_POST, true);
			$cookie = var_export($_COOKIE, true);

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
GET: <pre>$get</pre><br>
POST: <pre>$post</pre><br>
COOKIE: <pre>$cookie</pre><br>
HTML;

			return $str;
		}

		protected static function reset($filepath)
		{
			$count = 0;

			if(is_readable($filepath)) {
				$f = fopen($filepath, 'r');
				$count = floor(fread($f, 4096));
				fclose($f);
			}

			$f = fopen($filepath, 'w');
			fwrite($f, 1);
			fclose($f);

			return $count;
		}

		protected static function increment($filepath)
		{
			$count = 0;

			if(is_readable($filepath)) {
				$f = fopen($filepath, 'r');
				$count = floor(fread($f, 4096));
				fclose($f);
			}

			$f = fopen($filepath, 'w');
			fwrite($f, ++$count);
			fclose($f);

			return $count;
		}
	}
?>
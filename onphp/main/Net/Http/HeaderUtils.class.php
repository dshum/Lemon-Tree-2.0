<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: HeaderUtils.class.php 5142 2008-05-16 15:55:54Z ewgraf $ */

	/**
	 * Collection of static header functions.
	 * 
	 * @ingroup Http
	**/
	final class HeaderUtils extends StaticFactory
	{
		private static $headerSent		= false;
		private static $redirectSent	= false;
		private static $cacheLifeTime	= 3600;
		
		public static function redirectRaw($url)
		{
			self::sendRedirectStatus();
			header("Location: {$url}");

			self::$headerSent = true;
			self::$redirectSent = true;
		}
		
		public static function redirectBack()
		{
			if (isset($_SERVER['HTTP_REFERER'])) {
				self::sendRedirectStatus();
				header("Location: {$_SERVER['HTTP_REFERER']}");
				self::$headerSent = true;
				self::$redirectSent = true;
				return $_SERVER['HTTP_REFERER'];
			} else
				return false;
		}
		
		public static function sendRedirectStatus()
		{
			$protocol = HttpStatus::HTTP_1_1;
			
			if (isset($_SERVER['SERVER_PROTOCOL'])) {
				$protocol = $_SERVER['SERVER_PROTOCOL'];
			}
			
			$status = null;

			if ($protocol == HttpStatus::HTTP_1_0) {
				$status = new HttpStatus(HttpStatus::CODE_302);
			} else {
				$status = new HttpStatus(HttpStatus::CODE_303);
			}
			
			self::sendHttpStatus($status, $protocol);
		}
		
		public static function getParsedURI(/* ... */)
		{
			if ($num = func_num_args()) {
				$out = self::getURI();
				$uri = null;
				$arr = func_get_args();
				
				for ($i = 0; $i < $num; ++$i)
					unset($out[$arr[$i]]);
				
				foreach ($out as $key => $val) {
					if (is_array($val)) {
						foreach ($val as $k => $v)
							$uri .= "&{$key}[{$k}]={$v}";
					} else
						$uri .= "&{$key}={$val}";
				}

				return $uri;
			}

			return null;
		}
		
		public static function sendCachedHeader()
		{
			header('Cache-control: private, max-age=3600');
			
			header(
				'Expires: '
				.date('D, d M Y H:i:s', date('U') + self::$cacheLifeTime)
				.' GMT'
			);
			
			self::$headerSent = true;
		}

		public static function sendNotCachedHeader()
		{
			header('Cache-control: no-cache');
			header(
				'Expires: '
				.date('D, d M Y H:i:s', date('U') - self::$cacheLifeTime)
				.' GMT'
			);
			
			self::$headerSent = true;
		}
		
		public static function sendContentLength($length)
		{
			Assert::isInteger($length);
			
			header(
				"Content-Length: {$length}"
			);
			
			self::$headerSent = true;
		}
		
		public static function sendHttpStatus(
			HttpStatus $status,
			$protocol = HttpStatus::HTTP_1_1
		)
		{
			header($status->toString($protocol));
			
			self::$headerSent = true;
		}

		public static function isHeaderSent()
		{
			return self::$headerSent;
		}
		
		public static function forceHeaderSent()
		{
			self::$headerSent = true;
		}
		
		public static function isRedirectSent()
		{
			return self::$redirectSent;
		}
		
		public static function setCacheLifeTime($cacheLifeTime)
		{
			self::$cacheLifeTime = $cacheLifeTime;
		}
		
		public static function getCacheLifeTime()
		{
			return self::$cacheLifeTime;
		}

		private static function getURI()
		{
			$out = null;
			
			parse_str($_SERVER['QUERY_STRING'], $out);
			
			return $out;
		}
	}
?>
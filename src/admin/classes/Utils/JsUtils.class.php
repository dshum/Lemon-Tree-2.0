<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class JsUtils
	{
		public static function php2js($data = false)
		{
			if(is_null($data)) return 'null';

			if($data === false) return 'false';

			if($data === true) return 'true';

			if(is_scalar($data)) {
				if(is_float($data)) {
					$data = str_replace(',', '.', strval($data));
				}
				$search = array("\\", "/", "\n", "\t", "\r", "\b", "\f", "\"");
				$replace = array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
				return '"'.str_replace($search, $replace, $data).'"';
			}

			$isList = true;
			for($i = 0, reset($data); $i < sizeof($data); $i++, next($data)) {
				if(key($data) !== $i) {
					$isList = false;
					break;
				}
			}

			$result = array();
			if($isList) {
				foreach($data as $v) {
					$result[] = self::php2js($v);
				}
				return '[ '.join(', ', $result).' ]';
			} else {
				foreach($data as $k => $v) {
					$result[] = self::php2js($k).': '.self::php2js($v);
				}
				return '{ '.join(', ', $result).' }';
			}
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	final class UrlUtils
	{
		private $url;

		public static function create()
		{
			return new self($url = null);
		}

		public function __construct($url = null)
		{
			$this->url = $url ? $url : $_SERVER['QUERY_STRING'];
		}

		public function getUrlWithoutParameter()
		{
			$args = func_get_args();

			if(strlen($this->url)) {
				$queryArray = explode('&', $this->url);
				foreach($queryArray as $k => $param) {
					$paramArray = explode('=', $param);
					$name = array_shift($paramArray);
					if(in_array($name, $args)) {
						unset($queryArray[$k]);
					}
				}

				return
					implode('&', $queryArray)
					.(sizeof($queryArray) ? '&' : '');
			}

			return null;
		}
	}
?>
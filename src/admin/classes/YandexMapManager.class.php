<?php
	final class YandexMapManager extends Singleton implements Instantiatable
	{
		private $keyList = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function addKey($domain, $key)
		{
			$domain = $this->cleanDomain($domain);

			$this->keyList[$domain] = $key;

			return $this;
		}

		public function getKey($domain = HTTP_HOST)
		{
			$domain = $this->cleanDomain($domain);

			return
				isset($this->keyList[$domain])
				? $this->keyList[$domain]
				: null;
		}

		private function cleanDomain($domain)
		{
			$domain = strtolower($domain);
			$domain = str_replace('www.', '', $domain);

			return $domain;
		}
	}
?>
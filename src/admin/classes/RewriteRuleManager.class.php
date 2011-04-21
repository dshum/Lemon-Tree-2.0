<?php
	final class RewriteRuleManager extends Singleton implements Instantiatable
	{
		private $ruleList = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public static function check($rule, $search)
		{
			$method = $rule['method'];
			$pattern = $rule['pattern'];

			return self::$method($search, $pattern);
		}

		public static function getItem($rule)
		{
			$className = $rule['className'];
			$item = Item::dao()->getItemByName($className);

			return $item;
		}

		public static function getItemClass($rule)
		{
			$item = self::getItem($rule);

			if($item instanceof Item) {
				$itemClass = $item->getClass();
				return $itemClass;
			}

			return null;
		}

		public static function addPathPrefix($rule, $elementPath)
		{
			$pathPrefix = $rule['pathPrefix'];
			$elementPath = $pathPrefix.$elementPath;

			return $elementPath;
		}

		public static function itself($search, $pattern)
		{
			$search = '/'.trim($search, '/');
			$pattern = '/'.trim($pattern, '/');

			return $search == $pattern;
		}

		public static function child($search, $pattern)
		{
			$search = trim($search, '/');
			$pattern = trim($pattern, '/');

			$arr = explode('/', $search);
			array_pop($arr);
			$search = implode('/', $arr);

			return $search == $pattern;
		}

		public static function grandchild($search, $pattern)
		{
			$search = trim($search, '/');
			$pattern = trim($pattern, '/');

			$arr = explode('/', $search);
			if(sizeof($arr) > 1) {
				array_pop($arr);
				array_pop($arr);
				$search = implode('/', $arr);

				return $search == $pattern;
			}

			return false;
		}

		public static function greatgrandchild($search, $pattern)
		{
			$search = trim($search, '/');
			$pattern = trim($pattern, '/');

			$arr = explode('/', $search);
			if(sizeof($arr) > 2) {
				array_pop($arr);
				array_pop($arr);
				array_pop($arr);
				$search = implode('/', $arr);

				return $search == $pattern;
			}

			return false;
		}

		public static function regexp($search, $pattern)
		{
			$search = '/'.trim($search, '/');
			$pattern = '/'.trim($pattern, '/');

			return preg_match('~'.$pattern.'~i', $search);
		}

		public function addRule($method, $pattern, $className, $pathPrefix = '')
		{
			$this->ruleList[] = array(
				'method' => $method,
				'pattern' => $pattern,
				'className' => $className,
				'pathPrefix' => $pathPrefix,
			);

			return $this;
		}

		public function getRuleList()
		{
			return $this->ruleList;
		}
	}
?>
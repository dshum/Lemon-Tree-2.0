<?php
	final class RewriteRuleManager extends Singleton implements Instantiatable
	{
		private $rewriteRuleList = array();
		private $currentElement = null;
		private $firstpageControllerName = null;
		private $defaultControllerName = null;
		private $controllerName = 'error';

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setDefaultControllerName($controllerName)
		{
			if(!ClassUtils::isClassName($controllerName)) return $this;

			$this->defaultControllerName = $controllerName;

			return $this;
		}

		public function setFirstpageControllerName($controllerName)
		{
			if(!ClassUtils::isClassName($controllerName)) return $this;

			$this->firstpageControllerName = $controllerName;

			return $this;
		}

		public function plain($search, $pattern)
		{
			$search = trim($search, '/');
			$pattern = trim($pattern, '/');

			if($search == $pattern) return true;

			$searchArray = explode('/', $search);
			$patternArray = explode('/', $pattern);

			if(sizeof($searchArray) != sizeof($patternArray)) return false;

			foreach($patternArray as $key => $value) {
				if($value == '*') continue;
				if($value == $searchArray[$key]) continue;
				return false;
			}

			return true;
		}

		public function regexp($search, $pattern)
		{
			$search = '/'.trim($search, '/');

			return preg_match('~'.$pattern.'~i', $search);
		}

		public function addRule($pattern, $className, $controllerName)
		{
			$this->rewriteRuleList[] = array(
				'method' => 'plain',
				'pattern' => $pattern,
				'className' => $className,
				'controllerName' => $controllerName,
			);

			return $this;
		}

		public function addRegexpRule($pattern, $className, $controllerName)
		{
			$this->rewriteRuleList[] = array(
				'method' => 'regexp',
				'pattern' => $pattern,
				'className' => $className,
				'controllerName' => $controllerName,
			);

			return $this;
		}

		public function check($rewriteRule, $search)
		{
			$method = $rewriteRule['method'];
			$pattern = $rewriteRule['pattern'];

			return $this->$method($search, $pattern);
		}

		public function import($elementPath)
		{
			if($elementPath == '/') {

				$this->controllerName = $this->getFirstpageControllerName();

			} else {

				$this->controllerName = $this->getDefaultControllerName();

				$rewriteRuleList = $this->getRewriteRuleList();

				foreach($rewriteRuleList as $rewriteRule) {

					if($this->check($rewriteRule, $elementPath)) {

						$className = $rewriteRule['className'];
						$controllerName = $rewriteRule['controllerName'];

						$item = Item::dao()->getItemByName($className);
						$itemClass = $item->getClass();
						$currentElement = $itemClass->dao()->getByElementPath($elementPath);

						if($currentElement instanceof Element) {
							$this->currentElement = $currentElement;
							$this->controllerName = $controllerName;
							break;
						}
					}
				}
			}

			return $this;
		}

		public function getControllerNameByElement(Element $element)
		{
			if(!$element->getId()) return $this->getDefaultControllerName();

			$elementClassName = $element->getClass();
			$href = $element->getHref();

			$elementPath = Site::prepareElementPath($href);

			$rewriteRuleList = $this->getRewriteRuleList();

			foreach($rewriteRuleList as $rewriteRule) {

				if($this->check($rewriteRule, $elementPath)) {

					$className = $rewriteRule['className'];
					$controllerName = $rewriteRule['controllerName'];

					if($elementClassName == $className) return $controllerName;
				}
			}

			return $this->getDefaultControllerName();
		}

		public function getRewriteRuleList()
		{
			return $this->rewriteRuleList;
		}

		public function getDefaultControllerName()
		{
			return $this->defaultControllerName;
		}

		public function getFirstpageControllerName()
		{
			return $this->firstpageControllerName;
		}

		public function getCurrentElement()
		{
			return $this->currentElement;
		}

		public function getControllerName()
		{
			return $this->controllerName;
		}

		public function getController()
		{
			return new $this->controllerName;
		}
	}
?>
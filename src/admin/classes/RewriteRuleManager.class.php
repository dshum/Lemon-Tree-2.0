<?php
	final class RewriteRuleManager extends Singleton implements Instantiatable
	{
		private $rewriteRuleList = array();
		private $currentElement = null;
		private $firstpageControllerName = null;
		private $defaultControllerName = 'error';
		private $controller = null;

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

		public function addRedirectRule($pattern, $redirect)
		{
			$this->rewriteRuleList[] = array(
				'method' => 'redirect',
				'pattern' => $pattern,
				'redirect' => $redirect,
			);

			return $this;
		}

		public function addRegexpRedirectRule($pattern, $redirect)
		{
			$this->rewriteRuleList[] = array(
				'method' => 'regexpRedirect',
				'pattern' => $pattern,
				'redirect' => $redirect,
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
			$firstpageControllerName = $this->getFirstpageControllerName();

			if($elementPath == '/' && $firstpageControllerName) {

				$this->controller = new $firstpageControllerName;

			} else {

				$rewriteRuleList = $this->getRewriteRuleList();

				foreach($rewriteRuleList as $rewriteRule) {

					if($this->check($rewriteRule, $elementPath)) {

						if(
							$rewriteRule['method'] == 'redirect'
							|| $rewriteRule['method'] == 'regexpRedirect'
						) {

							$this->currentElement = null;
							$this->controller = new RedirectController;
							$this->controller->setRedirectUrl($rewriteRule['redirect']);
							break;

						} else {

							$className = $rewriteRule['className'];
							$controllerName = $rewriteRule['controllerName'];

							$item = Item::dao()->getItemByName($className);
							$itemClass = $item->getClass();
							$currentElement = $itemClass->dao()->getByElementPath($elementPath);

							if($currentElement instanceof Element) {
								$this->currentElement = $currentElement;
								$this->controller = new $controllerName;
								break;
							}

						}

					}

				}

			}

			if(!$this->controller) {
				$defaultControllerName = $this->getDefaultControllerName();
				$this->controller = new $defaultControllerName;
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

		public function getController()
		{
			return $this->controller;
		}

		public function getControllerName()
		{
			return get_class($this->getController());
		}

		private function plain($search, $pattern)
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

		private function regexp($search, $pattern)
		{
			$search = '/'.trim($search, '/');

			return preg_match('~'.$pattern.'~i', $search);
		}

		private function redirect($search, $pattern)
		{
			return $this->plain($search, $pattern);
		}

		private function regexpRedirect($search, $pattern)
		{
			return $this->regexp($search, $pattern);
		}
	}
?>
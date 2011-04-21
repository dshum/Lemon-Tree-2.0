<?php
	final class ControllerManager extends Singleton implements Instantiatable
	{
		private $defaultController = null;
		private $mainPageController = null;
		private $itemControllerList = array();
		private $elementControllerList = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setDefaultController($controllerName)
		{
			if(!ClassUtils::isClassName($controllerName)) return $this;

			$this->defaultController = $controllerName;

			return $this;
		}

		public function setMainPageController($controllerName)
		{
			if(!ClassUtils::isClassName($controllerName)) return $this;

			$this->mainPageController = $controllerName;

			return $this;
		}

		public function addItemController($className, $controllerName)
		{
			if(!ClassUtils::isClassName($className)) return $this;

			if(!ClassUtils::isClassName($controllerName)) return $this;

			$this->itemControllerList[$className] = $controllerName;

			return $this;
		}

		public function addElementController($className, $ids, $controllerName)
		{
			if(!ClassUtils::isClassName($className)) return $this;

			if(!ClassUtils::isClassName($controllerName)) return $this;

			if(!is_array($ids)) $ids = array($ids);

			foreach($ids as $id) {
				if(is_integer($id)) {
					$this->elementControllerList[$className][$id] = $controllerName;
				}
			}

			return $this;
		}

		public function getDefaultController()
		{
			return $this->defaultController;
		}

		public function getMainPageController()
		{
			return $this->mainPageController;
		}

		public function getController(Element $element)
		{
			$className = $element->getClass();
			$id = $element->getId();

			if(isset($this->elementControllerList[$className][$id])) {
				return $this->elementControllerList[$className][$id];
			}

			if(isset($this->itemControllerList[$className])) {
				return $this->itemControllerList[$className];
			}

			return $this->defaultController;
		}
	}
?>
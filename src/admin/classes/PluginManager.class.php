<?php
	final class PluginManager extends Singleton implements Instantiatable
	{
		private $actionList = array();
		private $filterList = array();
		private $browsePluginList = array();
		private $editPluginList = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function addBeforeInsertAction($className, $actionName)
		{
			$this->actionList[$className]['beforeInsert'] = $actionName;

			return $this;
		}

		public function addAfterInsertAction($className, $actionName)
		{
			$this->actionList[$className]['afterInsert'] = $actionName;

			return $this;
		}

		public function addBeforeUpdateAction($className, $actionName)
		{
			$this->actionList[$className]['beforeUpdate'] = $actionName;

			return $this;
		}

		public function addAfterUpdateAction($className, $actionName)
		{
			$this->actionList[$className]['afterUpdate'] = $actionName;

			return $this;
		}

		public function addBeforeDeleteAction($className, $actionName)
		{
			$this->actionList[$className]['beforeDelete'] = $actionName;

			return $this;
		}

		public function addAfterDeleteAction($className, $actionName)
		{
			$this->actionList[$className]['afterDelete'] = $actionName;

			return $this;
		}

		public function addFilter($className, $filterName)
		{
			$this->filterList[$className] = $filterName;

			return $this;
		}

		public function addBrowsePlugin($id, $pluginName)
		{
			$this->browsePluginList[$id] = $pluginName;

			return $this;
		}

		public function addEditPlugin($id, $pluginName)
		{
			$this->editPluginList[$id] = $pluginName;

			return $this;
		}

		public function getFilter($className)
		{
			return
				isset($this->filterList[$className])
				? $this->filterList[$className]
				: null;
		}

		public function getBeforeInsertAction($className)
		{
			return
				isset($this->actionList[$className]['beforeInsert'])
				? $this->actionList[$className]['beforeInsert']
				: null;
		}

		public function getAfterInsertAction($className)
		{
			return
				isset($this->actionList[$className]['afterInsert'])
				? $this->actionList[$className]['afterInsert']
				: null;
		}

		public function getBeforeUpdateAction($className)
		{
			return
				isset($this->actionList[$className]['beforeUpdate'])
				? $this->actionList[$className]['beforeUpdate']
				: null;
		}

		public function getAfterUpdateAction($className)
		{
			return
				isset($this->actionList[$className]['afterUpdate'])
				? $this->actionList[$className]['afterUpdate']
				: null;
		}

		public function getBeforeDeleteAction($className)
		{
			return
				isset($this->actionList[$className]['beforeDelete'])
				? $this->actionList[$className]['beforeDelete']
				: null;
		}

		public function getAfterDeleteAction($className)
		{
			return
				isset($this->actionList[$className]['afterDelete'])
				? $this->actionList[$className]['afterDelete']
				: null;
		}

		public function getBrowsePlugin($elementId)
		{
			if(isset($this->browsePluginList[$elementId])) {
				return $this->browsePluginList[$elementId];
			}

			list($className, $id) = explode(
				PrimitivePolymorphicIdentifier::DELIMITER,
				$elementId
			);

			return
				isset($this->browsePluginList[$className])
				? $this->browsePluginList[$className]
				: null;
		}

		public function getEditPlugin($elementId)
		{
			if(isset($this->editPluginList[$elementId])) {
				return $this->editPluginList[$elementId];
			}

			list($className, $id) = explode(
				PrimitivePolymorphicIdentifier::DELIMITER,
				$elementId
			);

			return
				isset($this->editPluginList[$className])
				? $this->editPluginList[$className]
				: null;
		}
	}
?>
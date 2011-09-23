<?php
	final class PluginManager extends Singleton implements Instantiatable
	{
		private $itemPluginList = array();
		private $itemFilterList = array();
		private $itemActionList = array();
		private $elementPluginList = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function addItemPlugin($className, $pluginName)
		{
			$this->itemPluginList[$className] = $pluginName;

			return $this;
		}

		public function addItemFilter($className, $filterName)
		{
			$this->itemFilterList[$className] = $filterName;

			return $this;
		}

		public function addItemAction($className,
			$beforeInsert, $afterInsert,
			$beforeUpdate, $afterUpdate,
			$beforeDelete, $afterDelete
		)
		{
			$this->itemActionList[$className] = array(
				'beforeInsert' => $beforeInsert,
				'afterInsert' => $afterInsert,
				'beforeUpdate' => $beforeUpdate,
				'afterUpdate' => $afterUpdate,
				'beforeDelete' => $beforeDelete,
				'afterDelete' => $afterDelete,
			);

			return $this;
		}

		public function addElementPlugin($elementId, $pluginName)
		{
			$this->elementPluginList[$elementId] = $pluginName;

			return $this;
		}

		public function getItemPlugin($className)
		{
			return
				isset($this->itemPluginList[$className])
				? $this->itemPluginList[$className]
				: null;
		}

		public function getItemFilter($className)
		{
			return
				isset($this->itemFilterList[$className])
				? $this->itemFilterList[$className]
				: null;
		}

		public function getItemAction($className)
		{
			return
				isset($this->itemActionList[$className])
				? $this->itemActionList[$className]
				: null;
		}

		public function getElementPlugin($elementId)
		{
			if(isset($this->elementPluginList[$elementId])) {

				return $this->elementPluginList[$elementId];

			} else {

				list($className, $id) = explode(
					PrimitivePolymorphicIdentifier::DELIMITER,
					$elementId
				);

				return
					isset($this->itemActionList[$className])
					? $this->itemActionList[$className]
					: null;
			}
		}
	}
?>
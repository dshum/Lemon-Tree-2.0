<?php
	class Item extends AutoItem implements Prototyped, DAOConnected
	{
		const DEFAULT_PERPAGE = 10;

		/**
		 * @return Item
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return ItemDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('ItemDAO');
		}

		/**
		 * @return ProtoItem
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoItem');
		}

		public function getClass()
		{
			try {
				if(!$this->isAbstract()) {
					$className = $this->getItemName();
					if(
						ClassUtils::isClassName($className)
						&& ClassUtils::isInstanceOf($className, 'Element')
					) {
						return new $className();
					}
				}
			} catch (BaseException $e) {}

			return null;
		}

		public function isExtendable(Item $item)
		{
			if($item === $this) return false;

			$thisPropertyList = Property::dao()->getPropertyList($this);

			$itemPropertyList = Property::dao()->getPropertyList($item);

			foreach($itemPropertyList as $itemProperty) {
				try {
					$thisProperty =
						Property::dao()->getPropertyByName(
							$this,
							$itemProperty->getPropertyName()
						);
					if(
						$thisProperty->getPropertyClass()
						!= $itemProperty->getPropertyClass()
					) {
						return false;
					}
				} catch (ObjectNotFoundException $e) {
					return false;
				}
			}
			return true;
		}

		public function isDefault()
		{
			return $this->getClassType() == 'default';
		}

		public function isAbstract()
		{
			return $this->getClassType() == 'abstract';
		}

		public function isVirtual()
		{
			return $this->getClassType() == 'virtual';
		}

		public function getDefaultTableName()
		{
			$itemName = $this->getItemName();

			return
				$itemName
				? TABLE_PREFIX
				.trim(
					mb_strtolower(
						preg_replace(':([A-Z]):', '_\1', $itemName)
					),
					'_')
				: '';
		}

		public function getFolderName()
		{
			return $this->getDefaultTableName();
		}

		public function getFolderPath()
		{
			return PATH_LTDATA.$this->getFolderName().DIRECTORY_SEPARATOR;
		}

		public function getFolderWebPath()
		{
			return PATH_WEB_LTDATA.$this->getFolderName().DIRECTORY_SEPARATOR;
		}

		public function getColumnName()
		{
			$itemName = $this->getItemName();

			return
				$itemName
				? trim(
					mb_strtolower(
						preg_replace(':([A-Z]):', '_\1', $itemName)
					),
					'_')
				: '';
		}

		public function getOrderBy()
		{
			$orderBy = new OrderBy($this->getOrderField());
			if($this->getOrderDirection()) {
				$orderBy->desc();
			} else {
				$orderBy->asc();
			}

			return $orderBy;
		}

		public function getChildrenItemList()
		{
			$childrenItemList = array();

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				if($item->isAbstract()) continue;
				$propertyList = Property::dao()->getPropertyList($item);
				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == Property::ONE_TO_ONE_PROPERTY
						&& $property->getFetchClass() == $this->getItemName()
					) {
						$childrenItemList[] = $item;
						break;
					}
				}
			}

			return $childrenItemList;
		}

		public function getFilter()
		{
			$filterName = $this->getFilterName();

			return $filterName ? new $filterName : null;
		}
	}
?>
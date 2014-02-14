<?php
	abstract class Element extends AutoElement implements Prototyped, DAOConnected
	{
		const MAX_NUMBER_OF_CHECK_ELEMENTS = 250;

		protected $shortName = null;

		public static function getByPolymorphicId($elementId)
		{
			if(
				strpos(
					$elementId,
					PrimitivePolymorphicIdentifier::DELIMITER
				) !== false
			) {
				list($className, $id) =
					explode(
						PrimitivePolymorphicIdentifier::DELIMITER,
						$elementId,
						2
					);
				if(
					ClassUtils::isClassName($className)
					&& ClassUtils::isInstanceOf($className, 'Element')
				) {
					$class = new $className;
					try {
						return $class->dao()->getById($id);
					} catch (ObjectNotFoundException $e) {}
				}
			}

			return null;
		}

		public static function getListByIds(ElementDAO $dao, $ids = array())
		{
			$elementList = array();

			foreach($ids as $id) {
				try {
					$element = $dao->getById($id);
					$elementList[] = $element;
				} catch (ObjectNotFoundException $e) {}
			}

			return $elementList;
		}

		public static function getListByPolymorphicIds($elementIds = array())
		{
			$elementList = array();

			foreach($elementIds as $elementId) {
				if(
					strpos(
						$elementId,
						PrimitivePolymorphicIdentifier::DELIMITER
					) !== false
				) {
					list($className, $id) =
						explode(
							PrimitivePolymorphicIdentifier::DELIMITER,
							$elementId,
							2
						);
					if(
						ClassUtils::isClassName($className)
						&& ClassUtils::isInstanceOf($className, 'Element')
						&& (int)$id
					) {
						try {
							$class = new $className();
							$element = $class->dao()->getById($id);
							$elementList[] = $element;
						} catch (ObjectNotFoundException $e) {}
					}
				}
			}

			return $elementList;
		}

		public function getParent()
		{
			if($this instanceof Root) return null;

			$currentItem = $this->getItem();

			$propertyList = Property::dao()->getParentPropertyList($currentItem);

			foreach($propertyList as $property) {
				if($property->getIsParent()) {
					$getter = $property->getter();
					try {
						$parent = $this->$getter();
						if($parent instanceof Element) {
							return
								$this->getStatus() == 'trash'
								&& $parent->getStatus() != 'trash'
								? Root::trash()
								: $parent;
						}
					} catch (ObjectNotFoundException $e) {}
				}
			}

			foreach($propertyList as $property) {
				$getter = $property->getter();
				try {
					$parent = $this->$getter();
					if($parent instanceof Element) {
						return
							$this->getStatus() == 'trash'
							&& $parent->getStatus() != 'trash'
							? Root::trash()
							: $parent;
					}
				} catch (ObjectNotFoundException $e) {}
			}

			return Root::me();
		}

		public function getParentClass()
		{
			return
				$this->getParent()
				? $this->getParent()->getClass()
				: null;
		}

		public function getParentId()
		{
			return
				$this->getParent()
				? $this->getParent()->getId()
				: null;
		}

		public function getParentField()
		{
			return Property::getColumnName($this->getClass()).'_id';
		}

		public function getItem()
		{
			$item = Item::dao()->getItemByName(get_class($this));

			return $item;
		}

		public function getItemId()
		{
			return $this->getItem()->getId();
		}

		public function getClass()
		{
			return get_class($this);
		}

		public function getPolymorphicId()
		{
			return
				$this->getClass()
				.PrimitivePolymorphicIdentifier::DELIMITER
				.$this->getId();
		}

		public function getAlterName()
		{
			return $this->getElementName();
		}

		public function getShortName()
		{
			if($this->shortName !== null) return $this->shortName;

			$elementPath = $this->getElementPath();
			$array = explode('/', $elementPath);
			$shortName = array_pop($array);

			return $shortName;
		}

		public function setShortName($shortName)
		{
			$this->shortName = $shortName ? $shortName : '';

			return $this;
		}

		public function getHref()
		{
			return PATH_WEB.trim($this->getElementPath(), '/').'/';
		}

		public function isParent(Element $element)
		{
			return (
				$element->getParentClass() == $this->getClass()
				&& $element->getParentId() == $this->getId()
			);
		}

		public function isEqualTo($element)
		{
			return
				$element instanceof Element
				&& $this->getPolymorphicId() == $element->getPolymorphicId();
		}

		public function isValid()
		{
			return $this->getStatus() == 'root';
		}

		public function property($propertyName)
		{
			switch(strtolower($propertyName)) {
				case 'id':
					return $this->getId();
				case 'itemid':
					$item = $this->getItem();
					return $item->getId();
				case 'itemname': case 'classname':
					return $this->getClass();
				case 'parent':
					return $this->getParent();
				case 'parentid':
					return $this->getParentId();
				case 'parentitem':
					return $this->getParentItem();
				case 'parentitemid':
					return $this->getParentItemId();
				case 'parentclass':
					return $this->getParentClass();
				case 'elementname':
					return $this->getElementName();
				case 'shortname':
					return $this->getShortName();
				case 'status':
					return $this->getStatus();
				case 'elementpath':
					return $this->getElementPath();
				case 'href':
					return $this->getHref();
				default:
					$item = $this->getItem();
					try {
						$property = Property::dao()->getPropertyByName($item, $propertyName);
						return $property->getClass($this);
					} catch (ObjectNotFoundException $e) {
						return null;
					}
			}
		}

		public function set($propertyName, $value)
		{
			$setter = 'set'.ucfirst($propertyName);
			$this->$setter($value);

			return $this;
		}

		public function get($propertyName) {
			switch(strtolower($propertyName)) {
				case 'id':
					return $this->getId();
				case 'itemid':
					$item = $this->getItem();
					return $item->getId();
				case 'itemname': case 'classname':
					return $this->getClass();
				case 'parent':
					return $this->getParent();
				case 'parentid':
					return $this->getParentId();
				case 'parentitem':
					return $this->getParentItem();
				case 'parentitemid':
					return $this->getParentItemId();
				case 'parentclass':
					return $this->getParentClass();
				case 'elementname':
					return $this->getElementName();
				case 'shortname':
					return $this->getShortName();
				case 'status':
					return $this->getStatus();
				case 'elementpath':
					return $this->getElementPath();
				case 'href':
					return $this->getHref();
				default:
					$getter = 'get'.ucfirst($propertyName);
					return
						method_exists($this, $getter)
						? $this->$getter()
						: null;
			}
		}

		public function getParentList()
		{
			$parentList = array();

			$count = 0;
			$parent = $this->getParent();
			while($count < 100 && $parent instanceof Element) {
				$parentList[] = $parent;
				$parent = $parent->getParent();
				$count++;
			}
			krsort($parentList);

			return $parentList;
		}

		public function getChildrenItemList()
		{
			$childrenItemList = array();

			$itemList = Item::dao()->getDefaultItemList();

			foreach($itemList as $item) {
				$propertyList = Property::dao()->getPropertyList($item);
				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == Property::ONE_TO_ONE_PROPERTY
						&& $property->getFetchClass() == $this->getClass()
					) {
						$childrenItemList[] = $item;
						break;
					}
				}
			}

			return $childrenItemList;
		}

		/**
		 * Recursive check whether the restrictions exist in 1:1 relations
		**/
		public function checkRestriction()
		{
			$currentItem = $this->getItem();

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				if($item->isAbstract()) continue;

				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == Property::ONE_TO_ONE_PROPERTY
						&& $property->getFetchClass() == $currentItem->getItemName()
					) {
						if(
							$property->getOnDelete() != 'set_null'
							&& $property->getOnDelete() != 'cascade'
						) {
							if(!sizeof($currentItem->getChildrenItemList())) continue;
							$count = $itemClass->dao()->getChildrenCountByProperty($this, $property);
							if($count > 0) return false;
						}
					}
				}
			}

			foreach($itemList as $item) {
				if($item->isAbstract()) continue;

				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == Property::ONE_TO_ONE_PROPERTY
						&& $property->getFetchClass() == $currentItem->getItemName()
					) {
						if(
							$property->getOnDelete() == 'set_null'
							|| $property->getOnDelete() == 'cascade'
						) {
							if(!sizeof($currentItem->getChildrenItemList())) continue;
							$count = $itemClass->dao()->getChildrenCountByProperty($this, $property);
							if($count <= self::MAX_NUMBER_OF_CHECK_ELEMENTS) {
								$childList =
									$itemClass->dao()->
									getChildrenByProperty($this, $property)->
									getList();
								foreach($childList as $child) {
									if(!$child->checkRestriction()) return false;
								}
							} else {
								return false;
							}
						}
					}
				}
			}

			return true;
		}

		public function getPermission(User $user)
		{
			$userGroup = $user->getGroup();

			$currentItem = $this->getItem();

			$elementPermission =
				ElementPermission::dao()->getByGroupAndElement(
					$userGroup,
					$this
				);

			if($elementPermission) {

				$permission = $elementPermission->getPermission();

			} else {

				$itemPermission =
					ItemPermission::dao()->getByGroupAndItem(
						$userGroup,
						$currentItem
					);

				if($itemPermission) {

					if(
						$this->getUser()
						&& $this->getUser()->getId() == $user->getId()
					) {
						$permission = $itemPermission->getOwnerPermission();
					} elseif(
						$this->getGroup()
						&& $this->getGroup()->getId() == $userGroup->getId()
					) {
						$permission = $itemPermission->getGroupPermission();
					} else {
						$permission = $itemPermission->getWorldPermission();
					}

				} else {

					if(
						$this->getUser()
						&& $this->getUser()->getId() == $user->getId()
					) {
						$permission = $userGroup->getOwnerPermission();
					} elseif(
						$this->getGroup()
						&& $this->getGroup()->getId() == $userGroup->getId()
					) {
						$permission = $userGroup->getGroupPermission();
					} else {
						$permission = $userGroup->getWorldPermission();
					}
				}
			}

			return $permission;
		}

		public function getUser()
		{
			if (!$this->user && $this->userId) {
				try {
					$this->user = User::dao()->getById($this->userId);
				} catch (ObjectNotFoundException $e) {}
			}

			return $this->user;
		}

		public function getGroup()
		{
			if (!$this->group && $this->groupId) {
				try {
					$this->group = Group::dao()->getById($this->groupId);
				} catch (ObjectNotFoundException $e) {}
			}

			return $this->group;
		}
	}
?>
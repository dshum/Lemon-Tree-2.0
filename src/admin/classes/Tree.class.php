<?php
	final class Tree
	{
		public static function getTree()
		{
			$tree = array();

			$loggedUser = LoggedUser::getUser();

			$itemList = Item::dao()->getDefaultItemList();

			# Item permission map

			$itemPermissionList =
				Criteria::create(ItemPermission::dao())->
				add(
					Expression::eqId(
						new DBField('group_id', ItemPermission::dao()->getTable()),
						$loggedUser->getGroup()
					)
				)->
				getList();

			$itemPermissionMap = array();
			foreach($itemPermissionList as $itemPermission) {
				$itemPermissionMap[$itemPermission->getItem()->getId()] = $itemPermission;
			}
			foreach($itemList as $item) {
				if(!$item->getIsFolder()) continue;
				if(!isset($itemPermissionMap[$item->getId()])) {
					$itemPermissionMap[$item->getId()] =
						ItemPermission::create()->
						setOwnerPermission($loggedUser->getGroup()->getOwnerPermission())->
						setGroupPermission($loggedUser->getGroup()->getGroupPermission())->
						setWorldPermission($loggedUser->getGroup()->getWorldPermission());
				}
			}

			# Element permission map

			$elementPermissionList =
				Criteria::create(ElementPermission::dao())->
				add(
					Expression::eqId(
						new DBField('group_id', ElementPermission::dao()->getTable()),
						$loggedUser->getGroup()
					)
				)->
				getList();

			$elementPermissionMap = array();
			foreach($elementPermissionList as $elementPermission) {
				$elementPermissionMap[$elementPermission->getElementId()] = $elementPermission->getPermission();
			}

			$openFolderList = Session::get('openFolderList');
			if(!$openFolderList) {
				$openFolderList = array();
			}

			foreach($itemList as $item) {
				if(!$item->getIsFolder()) continue;

				try {
					$itemClass = $item->getClass();

					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

					foreach($elementList as $element) {

						if(isset($elementPermissionMap[$element->getPolymorphicId()])) {
							$permission = $elementPermissionMap[$element->getPolymorphicId()];
						} else {
							if($element->getUser() && $element->getUser()->getId() == $loggedUser->getId()) {
								$permission = $itemPermissionMap[$item->getId()]->getOwnerPermission();
							} elseif($element->getGroup() && $element->getGroup()->getId() == $loggedUser->getGroup()->getId()) {
								$permission = $itemPermissionMap[$item->getId()]->getGroupPermission();
							} else {
								$permission = $itemPermissionMap[$item->getId()]->getWorldPermission();
							}
						}

						if($permission < Permission::PERMISSION_READ_ID) continue;

						$openFolder =
							isset($openFolderList[$element->getPolymorphicId()])
							? 1
							: 0;

						$tree[] = array(
							'parentId' => $element->getParent()->getPolymorphicId(),
							'elementId' => $element->getPolymorphicId(),
							'elementName' => $element->getElementName(),
							'permissionId' => $permission,
							'openFolder' => $openFolder,
						);
					}

				} catch (BaseException $e) {}
			}

			return $tree;
		}

		public static function getLinkTree($itemList, $openIdList, $activeClass, $activeElement)
		{
			$map = array();
			$tree = array();
			$prev = 0;
			$flag = true;

			foreach($itemList as $item) {

				try {
					$itemClass = $item->getClass();

					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

					foreach($elementList as $element) {
						$tree[$element->getParent()->getPolymorphicId()][$element->getPolymorphicId()] = $element;
						$tree2[$element->getParent()->getPolymorphicId()][$element->getPolymorphicId()] = 1;
						$prev++;
					}

				} catch (BaseException $e) {}
			}

			while ($flag) {
				$curr = 0;
				foreach ($tree as $parentId => $elementList) {
					foreach ($elementList as $elementId => $element) {
						$curr++;
						if (!isset($tree[$elementId]) || !sizeof($tree[$elementId])) {
							if ($element->getClass() != $activeClass) {
								unset($tree[$parentId][$elementId]);
								$curr--;
							}
						}
					}
				}
				if($curr == $prev) {
					$flag = false;
				} else {
					$prev = $curr;
				}
			}

			foreach ($tree as $parentId => $elementList) {
				if (!sizeof($tree[$parentId])) {
					unset($tree[$parentId]);
				}
			}

			foreach ($tree as $parentId => $elementList) {
				foreach ($elementList as $elementId => $element) {
					$isActive =
						$activeElement instanceof Element
						&& $element->getPolymorphicId() == $activeElement->getPolymorphicId()
						? 1
						: 0;

					$isOpen =
						isset($openIdList[$element->getPolymorphicId()])
						? 1
						: 0;

					$isRadio =
						$element->getClass() == $activeClass
						? 1
						: 0;

					$map[] = array(
						'parentId' => $element->getParent()->getPolymorphicId(),
						'elementId' => $element->getPolymorphicId(),
						'elementName' => $element->getAlterName(),
						'isActive' => $isActive,
						'isOpen' => $isOpen,
						'isRadio' => $isRadio,
					);
				}
			}

			return $map;
		}

		public static function getLinkPlainList($node, $activeClass, $activeElement)
		{
			$tree = array();

			try {

				$item = Item::dao()->getItemByName($activeClass);
				$itemClass = $item->getClass();

				if($node instanceof Element) {

					$elementList =
						$itemClass->dao()->getChildren($node)->
						addOrder($item->getOrderBy())->
						getList();

				} else {

					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

				}

				foreach($elementList as $element) {
					$isActive =
						$activeElement instanceof Element
						&& $element->getPolymorphicId() == $activeElement->getPolymorphicId()
						? 1
						: 0;

					$tree[] = array(
						'parentId' => $element->getParent()->getPolymorphicId(),
						'elementId' => $element->getPolymorphicId(),
						'elementName' => $element->getAlterName(),
						'isActive' => $isActive,
						'isOpen' => 0,
						'isRadio' => 1,
					);
				}

			} catch (BaseException $e) {}

			return $tree;
		}

		public static function getMultilinkTree($itemList, $openIdList, $activeList, $activeClass)
		{
			$map = array();
			$tree = array();
			$prev = 0;
			$flag = true;

			$activeIdList = array();
			foreach($activeList as $activeElement) {
				if($activeElement instanceof Element) {
					$activeIdList[] = $activeElement->getPolymorphicId();
				}
			}

			foreach($itemList as $item) {

				try {
					$itemClass = $item->getClass();

					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

					foreach($elementList as $element) {
						$tree[$element->getParent()->getPolymorphicId()][$element->getPolymorphicId()] = $element;
						$tree2[$element->getParent()->getPolymorphicId()][$element->getPolymorphicId()] = 1;
						$prev++;
					}

				} catch (BaseException $e) {}
			}

			while ($flag) {
				$curr = 0;
				foreach ($tree as $parentId => $elementList) {
					foreach ($elementList as $elementId => $element) {
						$curr++;
						if (!isset($tree[$elementId]) || !sizeof($tree[$elementId])) {
							if ($element->getClass() != $activeClass) {
								unset($tree[$parentId][$elementId]);
								$curr--;
							}
						}
					}
				}
				if($curr == $prev) {
					$flag = false;
				} else {
					$prev = $curr;
				}
			}

			foreach ($tree as $parentId => $elementList) {
				if (!sizeof($tree[$parentId])) {
					unset($tree[$parentId]);
				}
			}

			foreach ($tree as $parentId => $elementList) {
				foreach ($elementList as $elementId => $element) {
					$isActive =
						in_array($element->getPolymorphicId(), $activeIdList)
						? 1
						: 0;

					$isOpen =
						isset($openIdList[$element->getPolymorphicId()])
						? 1
						: 0;

					$isCheckbox =
						$element->getClass() == $activeClass
						? 1
						: 0;

					$map[] = array(
						'parentId' => $element->getParent()->getPolymorphicId(),
						'elementId' => $element->getPolymorphicId(),
						'elementName' => $element->getAlterName(),
						'isActive' => $isActive,
						'isOpen' => $isOpen,
						'isCheckbox' => $isCheckbox,
					);
				}
			}

			return $map;
		}

		public static function getMultilinkPlainList($node, $activeClass, $activeElements)
		{
			$tree = array();

			try {

				$item = Item::dao()->getItemByName($activeClass);
				$itemClass = $item->getClass();

				if($node instanceof Element) {

					$elementList =
						$itemClass->dao()->getChildren($node)->
						addOrder($item->getOrderBy())->
						getList();

				} else {

					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

				}

				foreach($elementList as $element) {
					$isActive = 0;
					foreach ($activeElements as $activeElement) {
						if(
							$activeElement instanceof Element
							&& $element->getPolymorphicId() == $activeElement->getPolymorphicId()
						) {
							$isActive = 1;
						}
					}

					$tree[] = array(
						'parentId' => $element->getParent()->getPolymorphicId(),
						'elementId' => $element->getPolymorphicId(),
						'elementName' => $element->getAlterName(),
						'isActive' => $isActive,
						'isOpen' => 0,
						'isCheckbox' => 1,
					);
				}

			} catch (BaseException $e) {}

			return $tree;
		}
	}
?>
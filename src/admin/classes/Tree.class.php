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
						Criteria::create($itemClass->dao())->
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
			$tree = array();

			foreach($itemList as $item) {

				try {
					$itemClass = $item->getClass();

					$elementList =
						Criteria::create($itemClass->dao())->
						addOrder($item->getOrderBy())->
						getList();

					foreach($elementList as $element) {

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

						$tree[] = array(
							'parentId' => $element->getParent()->getPolymorphicId(),
							'elementId' => $element->getPolymorphicId(),
							'elementName' => $element->getAlterName(),
							'isActive' => $isActive,
							'isOpen' => $isOpen,
							'isRadio' => $isRadio,

						);
					}

				} catch (BaseException $e) {}
			}

			return $tree;
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
			$tree = array();

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
						Criteria::create($itemClass->dao())->
						addOrder($item->getOrderBy())->
						getList();

					foreach($elementList as $element) {

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

						$tree[] = array(
							'parentId' => $element->getParent()->getPolymorphicId(),
							'elementId' => $element->getPolymorphicId(),
							'elementName' => $element->getAlterName(),
							'isActive' => $isActive,
							'isOpen' => $isOpen,
							'isCheckbox' => $isCheckbox,

						);
					}

				} catch (BaseException $e) {}
			}

			return $tree;
		}
	}
?>
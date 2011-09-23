<?php
	final class Tree
	{
		const MAX_NUMBER_OF_ELEMENTS = 500;

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

					if(sizeof($elementList) > self::MAX_NUMBER_OF_ELEMENTS) {
						continue;
					}

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

		public static function getTreeForLink($itemList)
		{
			$tree = array();

			foreach($itemList as $item) {
				if($item->getClassType() == 'abstract') continue;

				try {
					$itemClass = $item->getClass();
					$elementList =
						Criteria::create($itemClass->dao())->
						addOrder($item->getOrderBy())->
						getList();

					if(sizeof($elementList) > self::MAX_NUMBER_OF_ELEMENTS) continue;

					foreach($elementList as $element) {
						$tree[$element->getRealParent()->getPolymorphicId()][] = $element;
					}

				} catch (BaseException $e) {}
			}

			return $tree;
		}

		public static function getValidTreeForLink($itemList)
		{
			$tree = array();

			foreach($itemList as $item) {
				if($item->getClassType() == 'abstract') continue;

				try {
					$itemClass = $item->getClass();
					$elementList =
						$itemClass->dao()->getValid()->
						addOrder($item->getOrderBy())->
						getList();

					if(sizeof($elementList) > self::MAX_NUMBER_OF_ELEMENTS) continue;

					foreach($elementList as $element) {
						$tree[$element->getParent()->getPolymorphicId()][] = $element;
					}

				} catch (BaseException $e) {}
			}

			return $tree;
		}
	}
?>
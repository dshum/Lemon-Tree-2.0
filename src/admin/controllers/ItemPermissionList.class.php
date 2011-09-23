<?php
	final class ItemPermissionList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('save', 'save')->
				setMethodMapping('edit', 'edit')->
				setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function save(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('groupId')->
					of('Group')->
					required()
				)->
				add(Primitive::set('ownerPermission'))->
				add(Primitive::set('groupPermission'))->
				add(Primitive::set('worldPermission'))->
				import($_POST);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');
				$ownerPermissionList = $form->getValue('ownerPermission');
				$groupPermissionList = $form->getValue('groupPermission');
				$worldPermissionList = $form->getValue('worldPermission');

				$itemList = Item::dao()->getDefaultItemList();

				$itemPermissionList =
					Criteria::create(ItemPermission::dao())->
					add(
						Expression::eqId(
							new DBField('group_id', ItemPermission::dao()->getTable()),
							$currentGroup
						)
					)->
					getList();

				$itemPermissionMap = array();
				foreach($itemPermissionList as $itemPermission) {
					$itemPermissionMap[$itemPermission->getItem()->getId()] = $itemPermission;
				}

				foreach($itemList as $item) {
					if(
						isset($ownerPermissionList[$item->getId()])
						&& isset($groupPermissionList[$item->getId()])
						&& isset($worldPermissionList[$item->getId()])
						&& Permission::permissionExists($ownerPermissionList[$item->getId()])
						&& Permission::permissionExists($groupPermissionList[$item->getId()])
						&& Permission::permissionExists($worldPermissionList[$item->getId()])
					) {
						if(
							$ownerPermissionList[$item->getId()] == $currentGroup->getOwnerPermission()
							&& $groupPermissionList[$item->getId()] == $currentGroup->getGroupPermission()
							&& $worldPermissionList[$item->getId()] == $currentGroup->getWorldPermission()
						) {
							if(isset($itemPermissionMap[$item->getId()])) {
								ItemPermission::dao()->drop($itemPermissionMap[$item->getId()]);
							}
						} else {
							if(isset($itemPermissionMap[$item->getId()])) {
								$permission = $itemPermissionMap[$item->getId()];
								if(
									$ownerPermissionList[$item->getId()] != $permission->getOwnerPermission()
									|| $groupPermissionList[$item->getId()] != $permission->getGroupPermission()
									|| $worldPermissionList[$item->getId()] != $permission->getWorldPermission()
								) {
									$permission->
									setOwnerPermission($ownerPermissionList[$item->getId()])->
									setGroupPermission($groupPermissionList[$item->getId()])->
									setWorldPermission($worldPermissionList[$item->getId()]);
									ItemPermission::dao()->save($permission);
								}
							} else {
								$permission =
									ItemPermission::create()->
									setGroup($currentGroup)->
									setItem($item)->
									setOwnerPermission($ownerPermissionList[$item->getId()])->
									setGroupPermission($groupPermissionList[$item->getId()])->
									setWorldPermission($worldPermissionList[$item->getId()]);
								ItemPermission::dao()->add($permission);
							}
						}
					}
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ItemPermissionList');

		}

		public function edit(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('groupId')->
					of('Group')->
					required()
				)->
				import($_GET);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				$itemList = Item::dao()->getDefaultItemList();

				$itemPermissionList =
					Criteria::create(ItemPermission::dao())->
					add(
						Expression::eqId(
							new DBField('group_id', ItemPermission::dao()->getTable()),
							$currentGroup
						)
					)->
					getList();

				$itemPermissionMap = array('ownerPermission', 'groupPermission', 'worldPermission');
				foreach($itemPermissionList as $itemPermission) {
					$itemPermissionMap['ownerPermission'][$itemPermission->getItem()->getId()] = $itemPermission->getOwnerPermission();
					$itemPermissionMap['groupPermission'][$itemPermission->getItem()->getId()] = $itemPermission->getGroupPermission();
					$itemPermissionMap['worldPermission'][$itemPermission->getItem()->getId()] = $itemPermission->getWorldPermission();
				}

				$model->set('currentGroup', $currentGroup);
				$model->set('itemList', $itemList);
				$model->set('itemPermissionMap', $itemPermissionMap);

			}

			return ModelAndView::create()->
				setModel($model)->
				setView('ItemPermissionList');
		}
	}
?>
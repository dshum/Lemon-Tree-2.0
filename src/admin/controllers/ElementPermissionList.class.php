<?php
	final class ElementPermissionList extends MethodMappedController
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
				add(
					Primitive::polymorphicIdentifier('elementId')->
					ofBase('Element')->
					required()
				)->
				add(Primitive::set('permission'))->
				import($_POST);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');
				$currentElement = $form->getValue('elementId');
				$permissionList = $form->getValue('permission');

				$itemList = $currentElement->getShowChildrenItemList();

				$elementList = array();

				foreach($itemList as $item) {
					$itemClass = $item->getClass();
					$criteria = $itemClass->dao()->getChildren($currentElement);
					$criteria->addOrder($item->getOrderBy());
					$pager = CriteriaPager::create($criteria);
					if($item->getPerPage()) {
						$page = Session::get('page');
						$perpage =
							$item->getPerPage()
							? $item->getPerPage()
							: Item::DEFAULT_PERPAGE;
						$currentPage =
							isset($page[$currentElement->getItemId()][$currentElement->getId()][$item->getId()])
							? $page[$currentElement->getItemId()][$currentElement->getId()][$item->getId()]
							: 1;
						$pager->setPerpage($perpage)->setCurrentPage($currentPage);
					}
					try {
						$elementList[$item->getId()] = $pager->getList();
					} catch (BaseException $e) {
						$elementList[$item->getId()] = array();
						ErrorMessageUtils::sendMessage($e);
					}
				}

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

				$elementIdList = array();
				foreach($itemList as $item) {
					if(isset($elementList[$item->getId()])) {
						foreach($elementList[$item->getId()] as $element) {
							$elementIdList[] = $element->getPolymorphicId();
						}
					}
				}

				$elementPermissionMap = array();

				if(sizeof($elementIdList)) {
					$elementPermissionList =
						Criteria::create(ElementPermission::dao())->
						add(
							Expression::eqId(
								new DBField('group_id', ElementPermission::dao()->getTable()),
								$currentGroup
							)
						)->
						add(
							Expression::in(
								new DBField('element_id', ElementPermission::dao()->getTable()),
								$elementIdList
							)
						)->
						getList();

					foreach($elementPermissionList as $elementPermission) {
						$elementPermissionMap[$elementPermission->getElementId()] = $elementPermission;
					}
				}

				foreach($itemList as $item) {
					if(isset($elementList[$item->getId()])) {
						foreach($elementList[$item->getId()] as $element) {
							if(
								!isset($permissionList[$element->getPolymorphicId()])
								|| !Permission::permissionExists($permissionList[$element->getPolymorphicId()])
							) continue;

							if(
								(
									isset($itemPermissionMap[$item->getId()])
									&& $element->getGroup()
									&& $element->getGroup()->getId() == $currentGroup->getId()
									&& $permissionList[$element->getPolymorphicId()] == $itemPermissionMap[$item->getId()]->getGroupPermission()
								) || (
									isset($itemPermissionMap[$item->getId()])
									&& $permissionList[$element->getPolymorphicId()] == $itemPermissionMap[$item->getId()]->getWorldPermission()
								) || (
									$element->getGroup() && $element->getGroup()->getId() == $currentGroup->getId()
									&& $permissionList[$element->getPolymorphicId()] == $currentGroup->getGroupPermission()
								) || (
									$permissionList[$element->getPolymorphicId()] == $currentGroup->getWorldPermission()
								)
							) {
								if(isset($elementPermissionMap[$element->getPolymorphicId()])) {
									ElementPermission::dao()->drop($elementPermissionMap[$element->getPolymorphicId()]);
								}
							} else {
								if(isset($elementPermissionMap[$element->getPolymorphicId()])) {
									$permission = $elementPermissionMap[$element->getPolymorphicId()];
									if($permissionList[$element->getPolymorphicId()] != $permission->getPermission()) {
										$permission->setPermission($permissionList[$element->getPolymorphicId()]);
										ElementPermission::dao()->save($permission);
									}
								} else {
									$permission =
										ElementPermission::create()->
										setGroup($currentGroup)->
										setElementId($element->getPolymorphicId())->
										setPermission($permissionList[$element->getPolymorphicId()]);
									ElementPermission::dao()->add($permission);
								}
							}
						}
					}
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementPermissionList');

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
				add(
					Primitive::polymorphicIdentifier('elementId')->
					ofBase('Element')->
					setDefault(Root::me())
				)->
				add(
					Primitive::identifier('pagerItemId')->
					of('Item')
				)->
				add(
					Primitive::integer('pagerPage')
				)->
				import($_GET);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');
				$currentElement = $form->getActualValue('elementId');
				$parentList = $currentElement->getParentList();

				$itemList = $currentElement->getChildrenItemList();

				# Pager

				$pagerItem = $form->getValue('pagerItemId');
				$pagerPage = $form->getValue('pagerPage');
				if($pagerItem && $pagerPage) {
					$page = Session::get('page');
					$page[$currentElement->getItemId()][$currentElement->getId()][$pagerItem->getId()] = $pagerPage;
					Session::assign('page', $page);
				}

				# Element list

				$elementList = array();
				$pagerList = array();

				foreach($itemList as $item) {
					$itemClass = $item->getClass();
					$criteria = $itemClass->dao()->getChildren($currentElement);
					$criteria->addOrder($item->getOrderBy());
					$pager = CriteriaPager::create($criteria);
					if($item->getPerPage()) {
						$page = Session::get('page');
						$perpage =
							$item->getPerPage()
							? $item->getPerPage()
							: Item::DEFAULT_PERPAGE;
						$currentPage =
							isset($page[$currentElement->getItemId()][$currentElement->getId()][$item->getId()])
							? $page[$currentElement->getItemId()][$currentElement->getId()][$item->getId()]
							: 1;
						$pager->setPerpage($perpage)->setCurrentPage($currentPage);
					}
					try {
						$elementList[$item->getId()] = $pager->getList();
						$pagerList[$item->getId()] = $pager;
					} catch (BaseException $e) {
						$elementList[$item->getId()] = array();
						ErrorMessageUtils::sendMessage($e);
					}
				}

				# Permission list

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

				$elementIdList = array();
				foreach($itemList as $item) {
					if(isset($elementList[$item->getId()])) {
						foreach($elementList[$item->getId()] as $element) {
							$elementIdList[] = $element->getPolymorphicId();
						}
					}
				}

				$elementPermissionMap = array();

				if(sizeof($elementIdList)) {
					$elementPermissionList =
						Criteria::create(ElementPermission::dao())->
						add(
							Expression::eqId(
								new DBField('group_id', ElementPermission::dao()->getTable()),
								$currentGroup
							)
						)->
						add(
							Expression::in(
								new DBField('element_id', ElementPermission::dao()->getTable()),
								$elementIdList
							)
						)->
						getList();

					foreach($elementPermissionList as $elementPermission) {
						$elementPermissionMap[$elementPermission->getElementId()] = $elementPermission->getPermission();
					}
				}

				$model->set('currentGroup', $currentGroup);
				$model->set('currentElement', $currentElement);
				$model->set('parentList', $parentList);
				$model->set('itemList', $itemList);
				$model->set('elementList', $elementList);
				$model->set('pagerList', $pagerList);
				$model->set('itemPermissionMap', $itemPermissionMap);
				$model->set('elementPermissionMap', $elementPermissionMap);
			}

			return ModelAndView::create()->
				setModel($model)->
				setView('ElementPermissionList');
		}
	}
?>
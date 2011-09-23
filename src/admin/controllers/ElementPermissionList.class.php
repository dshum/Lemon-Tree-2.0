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
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

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
					$itemPermissionMap[$itemPermission->getItem()->getItemName()] = $itemPermission;
				}

				$elementIdList = array();
				foreach($permissionList as $elementId => $permission) {
					$elementIdList[] = $elementId;
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

				foreach($permissionList as $elementId => $permission) {

					list($className, $id) = explode(PrimitivePolymorphicIdentifier::DELIMITER, $elementId);

					$element = Element::getByPolymorphicId($elementId);

					if(!$element || !Permission::permissionExists($permission)) continue;

					if(
						(
							isset($itemPermissionMap[$className])
							&& $element->getGroup() && $element->getGroup()->getId() == $currentGroup->getId()
							&& $permission == $itemPermissionMap[$className]->getGroupPermission()
						) || (
							isset($itemPermissionMap[$className])
							&& $permission == $itemPermissionMap[$className]->getWorldPermission()
						) || (
							$element->getGroup() && $element->getGroup()->getId() == $currentGroup->getId()
							&& $permission == $currentGroup->getGroupPermission()
						) || (
							$permission == $currentGroup->getWorldPermission()
						)
					) {
						if(isset($elementPermissionMap[$elementId])) {
							ElementPermission::dao()->drop($elementPermissionMap[$elementId]);
						}
					} else {
						if(isset($elementPermissionMap[$elementId])) {
							$elementPermission = $elementPermissionMap[$elementId];
							if($permission != $elementPermission->getPermission()) {
								$elementPermission->setPermission($permission);
								ElementPermission::dao()->save($elementPermission);
							}
						} else {
							$elementPermission =
								ElementPermission::create()->
								setGroup($currentGroup)->
								setElementId($elementId)->
								setPermission($permission);
							ElementPermission::dao()->add($elementPermission);
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
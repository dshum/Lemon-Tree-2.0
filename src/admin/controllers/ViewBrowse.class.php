<?php
	final class ViewBrowse extends MethodMappedController
	{
		const PAGE_LIMIT = 29;

		public function __construct()
		{
			$this->
			setMethodMapping('show', 'showList')->
			setMethodMapping('save', 'saveList')->
			setMethodMapping('drop', 'dropList')->
			setMethodMapping('restore', 'restoreList')->
			setMethodMapping('view', 'view')->
			setDefaultAction('view');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		protected function showList(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('elementId')->
				ofBase('Element')->
				required()
			)->
			add(
				Primitive::identifier('itemId')->
				of('Item')->
				required()
			)->
			add(
				Primitive::choice('list')->
				setList(array(
					'expand' => 'expand',
					'open' => 'open',
					'close' => 'close',
				))
			)->
			add(
				Primitive::string('fieldName')
			)->
			add(
				Primitive::choice('direction')->
				setList(array(
					'asc' => 'asc',
					'desc' => 'desc',
				))
			)->
			add(
				Primitive::integer('page')
			)->
			add(
				Primitive::boolean('filter')
			)->
			import($request->getPost());

			if(!$form->getErrors()) {

				$element = $form->getValue('elementId');
				$item = $form->getValue('itemId');
				$expandList = $form->getValue('list');
				$sortFieldName = $form->getValue('fieldName');
				$sortDirection = $form->getValue('direction');
				$currentPage = $form->getValue('page');
				$isFilter = $form->getValue('filter');

				$isAsc = $sortDirection == 'desc' ? false : true;

				if($expandList) {
					$expand = $loggedUser->getParameter('expand');
					if($expandList == 'open' || $expandList == 'expand') {
						$expand[$element->getPolymorphicId()][$item->getId()] = 1;
					} elseif($expandList == 'close') {
						if(isset($expand[$element->getPolymorphicId()][$item->getId()])) {
							unset($expand[$element->getPolymorphicId()][$item->getId()]);
							if(empty($expand[$element->getPolymorphicId()])) {
								unset($expand[$element->getPolymorphicId()]);
							}
						}
					}
					$loggedUser->setParameter('expand', $expand);
				}

				if($sortFieldName) {
					$sort = Session::get('sort');
					$sort[$element->getPolymorphicId()][$item->getId()] = array(
						'fieldName' => $sortFieldName,
						'isAsc' => $isAsc,
					);
					Session::assign('sort', $sort);
					$currentPage = 1;
				}

				if($currentPage) {
					$page = Session::get('page');
					$page[$element->getPolymorphicId()][$item->getId()] = $currentPage;
					Session::assign('page', $page);
				}

				if(
					$expandList == 'expand'
					|| $sortFieldName
					|| $currentPage
					|| $isFilter
				) {
					$elementListModelAndView = $this->getElementListModelAndView(
						$request,
						$element,
						$item
					);
					$model->set('itemId', $item->getId());
					$model->set('elementListModelAndView', $elementListModelAndView);
				}

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ViewBrowse');
		}

		protected function saveList(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::set('edited')
			)->
			import($request->getPost());

			$edited = $form->getValue('edited');

			$elementMap = array();
			$originalMap = array();
			$changed = array();
			$saved = array();

			foreach($edited as $elementId => $propertyList) {
				$element = Element::getByPolymorphicId($elementId);
				if($element instanceof Element) {
					$original = clone $element;
					$item = $element->getItem();

					# Before update action
					try {
						$actionName = PluginManager::me()->getBeforeUpdateAction(
							$item->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($original);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					$elementMap[$elementId] = $element;
					$originalMap[$elementId] = $original;
				}
			}

			foreach($edited as $elementId => $propertyList) {
				if(isset($elementMap[$elementId])) {

					$element = $elementMap[$elementId];
					$original = $originalMap[$elementId];
					$item = $element->getItem();
					$form = Form::create();

					foreach($propertyList as $propertyName => $flag) {
						if(!$flag) continue;
						$property = Property::dao()->getPropertyByName($item, $propertyName);
						$propertyClass = $property->getClass($element)->setParameters();
						$form = $propertyClass->add2multiform($form);
					}
					$form->import($request->getPost());

					foreach($propertyList as $propertyName => $flag) {
						if(!$flag) continue;
						$property = Property::dao()->getPropertyByName($item, $propertyName);
						$primitiveName = 'edit_'.$element->getClass().'_'.$element->getId().'_'.$propertyName.'';
						if($form->primitiveExists($primitiveName)) {
							$value = $form->getValue($primitiveName);
							$primitive = $form->get($primitiveName);
							$element->set($propertyName, $value);
							$propertyClass = $property->getClass($element)->setParameters();
							$changed[$elementId][$propertyName] = $propertyClass->getEditElementListView();
						}
					}

					if(isset($changed[$elementId])) {
						try {
							$element = $element->dao()->save($element);
							$saved[] = $element->getPolymorphicId();
						} catch (BaseException $e) {
							ErrorMessageUtils::sendMessage($e);
						}
					}
				}
			}

			foreach($edited as $elementId => $propertyList) {
				if(isset($elementMap[$elementId])) {

					$element = $elementMap[$elementId];
					$original = $originalMap[$elementId];
					$item = $element->getItem();

					# After update action
					try {
						$actionName = PluginManager::me()->getAfterUpdateAction(
							$item->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($element, $original);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}
				}
			}

			# User log
			UserLog::me()->log(
				UserActionType::ACTION_TYPE_SAVE_ELEMENT_LIST_ID,
				implode(', ', $saved)
			);

			Site::updateLastModified();

			$model->set('changed', $changed);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ViewBrowse');
		}

		protected function dropList(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::set('check')
			)->
			import($request->getPost());

			$check = $form->getValue('check');

			$itemList = Item::dao()->getItemList();

			$dropped = array();
			$refreshTree = false;
			$actionTypeId = null;

			$dropElementList = Element::getListByPolymorphicIds($check);

			foreach($dropElementList as $element) {
				$item = $element->getItem();

				# Before delete action
				try {
					$actionName = PluginManager::me()->getBeforeDeleteAction(
						$item->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($element);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				try {
					if($element->getStatus() == 'trash') {
						$result = $element->dao()->dropElement($element);
						$actionTypeId = UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_ID;
					} else {
						$result = $element->dao()->moveElementToTrash($element);
						$actionTypeId = UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID;
					}
				} catch (BaseException $e) {
					$result = null;
					$model->set('drop', 'error');
				}

				if($result) {

					# After delete action
					try {
						$actionName = PluginManager::me()->getAfterDeleteAction(
							$item->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($element);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					if($item->getIsFolder()) {
						$refreshTree = true;
					}
					$dropped[] = $element->getPolymorphicId();

				} else {

					$model->set('restrict', 'error');

				}
			}

			# User log
			UserLog::me()->log(
				$actionTypeId,
				implode(', ', $dropped)
			);

			Site::updateLastModified();

			$model->set('dropped', $dropped);

			# Refresh tree
			if($refreshTree) {
				$tree = Tree::getTree();
				$model->set('tree', $tree);
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ViewBrowse');
		}

		protected function restoreList(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::set('check')
			)->
			import($request->getPost());

			$check = $form->getValue('check');

			$itemList = Item::dao()->getItemList();

			$restored = array();
			$refreshTree = false;

			$restoreElementList = Element::getListByPolymorphicIds($check);

			foreach($restoreElementList as $element) {
				$item = $element->getItem();
				if($element->getStatus() == 'trash') {

					# Before restore action
					try {
						$actionName = PluginManager::me()->getBeforeInsertAction(
							$item->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($element);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					$result = $element->dao()->restoreElementFromTrash($element);

					# After restore action
					try {
						$actionName = PluginManager::me()->getAfterInsertAction(
							$item->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($element);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					if($item->getIsFolder()) {
						$refreshTree = true;
					}
					$restored[] = $element->getPolymorphicId();
				}
			}

			# User log
			UserLog::me()->log(
				UserActionType::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID,
				implode(', ', $restored)
			);

			Site::updateLastModified();

			$model->set('restored', $restored);

			# Refresh tree
			if($refreshTree) {
				$tree = Tree::getTree();
				$model->set('tree', $tree);
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ViewBrowse');
		}

		protected function view(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form = $this->makeElementListForm();

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$currentElement =
				$form->getValue('elementId')
				? $form->getValue('elementId')
				: Root::me();
			$currentItem = $currentElement->getItem();
			$currentItemId = $currentElement->getItemId();
			$parentList = $currentElement->getParentList();

			# Plugin

			$pluginModelAndView = null;

			$pluginName = PluginManager::me()->getBrowsePlugin(
				$currentElement->getPolymorphicId()
			);

			if($pluginName) {

				$pluginClass = new $pluginName($currentElement);
				$pluginModelAndView = $pluginClass->handleRequest($request);

				$pluginModel = $pluginModelAndView->getModel();
				$pluginView = $pluginModelAndView->getView();

				if(is_string($pluginView) && $pluginView == 'redirectBack') {
					$href =
						isset($_SERVER['HTTP_REFERER'])
						? $_SERVER['HTTP_REFERER']
						: PATH_ADMIN_BROWSE;
					$pluginView = new RedirectView($href);
				}

				if(!$pluginView instanceof View) {
					$pluginViewName = $pluginView;
					$viewResolver =
						MultiPrefixPhpViewResolver::create()->
						setViewClassName('SimplePhpView')->
						addPrefix(PATH_USER_PLUGIN_TEMPLATES)->
						addPrefix(PATH_USER_TEMPLATES);
					$pluginView = $viewResolver->resolveViewName($pluginViewName);
					$pluginModelAndView->setView($pluginView);
				}

				$pluginModel->
				set('element', $currentElement)->
				set('selfUrl', PATH_ADMIN_BROWSE.'?module='.get_class($this))->
				set('baseUrl', PATH_ADMIN_BROWSE);

				if(
					$pluginView instanceof RedirectView
					|| sizeof($request->getPost())
					|| $request->hasGetVar('print')
				) {
					return $pluginModelAndView;
				}
			}

			# Set id for active element and opened folder in tree

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

			if(
				($currentItem && $currentItem->getIsFolder())
				|| $currentElement instanceof Root
			) {
				Session::assign('activeElement', $currentElement);
				$openFolderList = Session::get('openFolderList');
				$openFolderList[$currentElement->getPolymorphicId()] = 1;
				Session::assign('openFolderList', $openFolderList);
			}

			$activeElement =
				Session::exist('activeElement')
				? Session::get('activeElement')
				: Root::me();

			$itemList = Item::dao()->getDefaultItemList();

			if($currentElement != Root::trash()) {
				$childrenItemList = array();
				foreach($itemList as $item) {
					$propertyList = Property::dao()->getPropertyList($item);
					foreach($propertyList as $property) {
						if(
							$property->getPropertyClass() == 'OneToOneProperty'
							&& $property->getFetchClass() == $currentElement->getClass()
						) {
							$propertyClass = $property->getClass(null)->setParameters();
							$isShowList = $propertyClass->getParameterValue('showList');
							if($isShowList) {
								$childrenItemList[] = $item;
								break;
							}
						}
					}
				}
				$itemList = $childrenItemList;
			}

			# Bind list

			$bindList = array();

			if($currentElement->getStatus() == 'root') {
				$bind2ItemList =
					Criteria::create(Bind2Item::dao())->
					add(
						Expression::eq(
							new DBField('item_id', Bind2Item::dao()->getTable()),
							new DBValue($currentItemId)
						)
					)->
					getList();

				foreach($bind2ItemList as $bind2Item) {
					if(in_array($bind2Item->getBindItem(), $itemList)) {
						$bindList[] = $bind2Item->getBindItem()->getId();
					}
				}

				$bind2ElementList =
					Criteria::create(Bind2Element::dao())->
					add(
						Expression::eq(
							new DBField('element_id', Bind2Element::dao()->getTable()),
							new DBValue($currentElement->getPolymorphicId())
						)
					)->
					getList();

				foreach($bind2ElementList as $bind2Element) {
					if(in_array($bind2Element->getBindItem(), $itemList)) {
						$bindList[] = $bind2Element->getBindItem()->getId();
					}
				}
			}

			# Element list

			$expand = $loggedUser->getParameter('expand');

			$elementListModelAndViewList = array();
			$empty = true;

			foreach($itemList as $item) {
				if(!$item->getClass() instanceof Element) continue;

				$elementListModelAndView = $this->getElementListModelAndView(
					$request,
					$currentElement,
					$item
				);

				$elementListModel = $elementListModelAndView->getModel();

				if($elementListModel->get('empty') == false) {
					$elementListModelAndViewList[$item->getId()] = $elementListModelAndView;
					$empty = false;
				}
			}

			UserLog::me()->log(
				UserActionType::ACTION_TYPE_VIEW_BROWSE_ID,
				$currentElement->getPolymorphicId()
			);

			$model->set('currentItem', $currentItem);
			$model->set('currentElement', $currentElement);
			$model->set('parentList', $parentList);
			$model->set('activeElement', $activeElement);
			$model->set('itemList', $itemList);
			$model->set('bindList', $bindList);
			$model->set('pluginModelAndView', $pluginModelAndView);
			$model->set('elementListModelAndViewList', $elementListModelAndViewList);
			$model->set('empty', $empty);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ViewBrowse');
		}

		private function getElementListModelAndView(
			HttpRequest $request,
			Element $element,
			Item $item
		)
		{
			$elementListModelAndView = $this->ElementListHandleRequest(
				$request,
				$element,
				$item
			);

			$elementListModel = $elementListModelAndView->getModel();
			$elementListView = $elementListModelAndView->getView();

			$elementListViewName = $elementListView;
			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView')->
				addPrefix(PATH_ADMIN_TEMPLATES);
			$elementListView = $viewResolver->resolveViewName($elementListViewName);
			$elementListModelAndView->setView($elementListView);

			return $elementListModelAndView;
		}

		private function ElementListHandleRequest(
			HttpRequest $request,
			Element $currentElement,
			Item $currentItem
		)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			$expand = $loggedUser->getParameter('expand');
			$openItem = isset($expand[$currentElement->getPolymorphicId()][$currentItem->getId()]);

			# Item permission and element permission map

			$itemPermission = ItemPermission::dao()->getByGroupAndItem($loggedUserGroup, $currentItem);

			if(!$itemPermission) {
				$itemPermission =
					ItemPermission::create()->
					setOwnerPermission($loggedUser->getGroup()->getOwnerPermission())->
					setGroupPermission($loggedUser->getGroup()->getGroupPermission())->
					setWorldPermission($loggedUser->getGroup()->getWorldPermission());
			}

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
				$elementId = $elementPermission->getElementId();
				list($className, $id) = explode(PrimitivePolymorphicIdentifier::DELIMITER, $elementId);
				if($currentItem->getItemName() == $className) {
					$elementPermissionMap[$id] = $elementPermission->getPermission();
				}
			}

			$deniedElementList = array();
			$allowedElementList = array();
			foreach($elementPermissionMap as $id => $permission) {
				if($permission == Permission::PERMISSION_DENIED_ID) {
					$deniedElementList[$id] = $id;
				} else {
					$allowedElementList[$id] = $id;
				}
			}

			# Criteria

			$currentItemClass = $currentItem->getClass();

			if($currentElement == Root::trash()) {
				$criteria = $currentItemClass->dao()->getByStatus('trash');
			} else {
				$criteria = $currentItemClass->dao()->getChildren($currentElement);
			}

			# Permission for elements

			if(sizeof($deniedElementList)) {
				$criteria->
				add(
					Expression::notIn(
						new DBField('id', $currentItemClass->dao()->getTable()),
						$deniedElementList
					)
				);
			}

			# Permission for owner

			if($itemPermission->getOwnerPermission() == Permission::PERMISSION_DENIED_ID) {
				if(sizeof($allowedElementList)) {
					$criteria->
					add(
						Expression::orBlock(
							Expression::notEq(
								new DBField('user_id', $currentItemClass->dao()->getTable()),
								new DBValue($loggedUser->getId())
							),
							Expression::in(
								new DBField('id', $currentItemClass->dao()->getTable()),
								$allowedElementList
							)
						)
					);
				} else {
					$criteria->
					add(
						Expression::notEq(
							new DBField('user_id', $currentItemClass->dao()->getTable()),
							new DBValue($loggedUser->getId())
						)
					);
				}
			}

			# Permission for group

			if($itemPermission->getGroupPermission() == Permission::PERMISSION_DENIED_ID) {
				if(sizeof($allowedElementList)) {
					$criteria->
					add(
						Expression::orBlock(
							Expression::notEq(
								new DBField('group_id', $currentItemClass->dao()->getTable()),
								new DBValue($loggedUserGroup->getId())
							),
							Expression::in(
								new DBField('id', $currentItemClass->dao()->getTable()),
								$allowedElementList
							)
						)
					);
				} else {
					$criteria->
					add(
						Expression::notEq(
							new DBField('group_id', $currentItemClass->dao()->getTable()),
							new DBValue($loggedUserGroup->getId())
						)
					);
				}
			}

			# Permission for world

			if($itemPermission->getWorldPermission() == Permission::PERMISSION_DENIED_ID) {
				if(sizeof($allowedElementList)) {
					$criteria->
					add(
						Expression::orBlock(
							Expression::eq(
								new DBField('group_id', $currentItemClass->dao()->getTable()),
								new DBValue($loggedUserGroup->getId())
							),
							Expression::in(
								new DBField('id', $currentItemClass->dao()->getTable()),
								$allowedElementList
							)
						)
					);
				} else {
					$criteria->
					add(
						Expression::eq(
							new DBField('group_id', $currentItemClass->dao()->getTable()),
							new DBValue($loggedUserGroup->getId())
						)
					);
				}
			}

			# Filter

			$filterName = PluginManager::me()->getFilter(
				$currentItem->getItemName()
			);

			if($filterName) {

				$filterCriteria = clone $criteria;
				$projection = new ProjectionChain();
				$projection->add(Projection::count('id', 'count'));
				$filterCriteria->setProjection($projection)->dropOrder();
				$total = $filterCriteria->getCustom('count');

				if($total > 0) {
					$filterClass = new $filterName($currentElement, $currentItem, $criteria);
					$filterModelAndView = $filterClass->handleRequest($request);
					$criteria = $filterClass->getCriteria();

					$filterModel = $filterModelAndView->getModel();
					$filterViewName = $filterModelAndView->getView();

					$filterModel->
					set('element', $currentElement)->
					set('item', $currentItem)->
					set('selfUrl', PATH_ADMIN_BROWSE.'?module=ViewBrowse')->
					set('baseUrl', PATH_ADMIN_BROWSE);

					$viewResolver =
						MultiPrefixPhpViewResolver::create()->
						setViewClassName('SimplePhpView')->
						addPrefix(PATH_USER_PLUGIN_TEMPLATES);
					$filterView = $viewResolver->resolveViewName($filterViewName);
					$filterModelAndView->setView($filterView);

					$model->set('filterModelAndView', $filterModelAndView);
				}
			}

			# Sort

			$sort = Session::get('sort');

			$sortFieldName =
				isset($sort[$currentElement->getPolymorphicId()][$currentItem->getId()]['fieldName'])
				? $sort[$currentElement->getPolymorphicId()][$currentItem->getId()]['fieldName']
				: $currentItem->getOrderBy()->getFieldName();

			$sortIsAsc =
				isset($sort[$currentElement->getPolymorphicId()][$currentItem->getId()]['isAsc'])
				? $sort[$currentElement->getPolymorphicId()][$currentItem->getId()]['isAsc']
				: $currentItem->getOrderBy()->isAsc();

			$orderBy = OrderBy::create($sortFieldName);
			if(!$sortIsAsc) {
				$orderBy->desc();
			}

			$criteria->addOrder($orderBy);

			# Pager

			$page = Session::get('page');

			$currentPage =
				isset($page[$currentElement->getPolymorphicId()][$currentItem->getId()])
				? $page[$currentElement->getPolymorphicId()][$currentItem->getId()]
				: 1;

			$pager = CriteriaPager::create($criteria);

			$perPage =
				$currentElement == Root::trash() && !$currentItem->getPerPage()
				? Item::DEFAULT_PERPAGE
				: $currentItem->getPerPage();

			if($perPage) {
				$pager->
				setPerpage($perPage)->
				setPageLimit(self::PAGE_LIMIT)->
				setCurrentPage($currentPage);
			}

			if($openItem) {

				# Get list

				try {
					$elementList = $pager->getList();
				} catch (BaseException $e) {
					$elementList = array();
					ErrorMessageUtils::sendMessage($e);
				}

				# Property list

				$propertyList = Property::dao()->getPropertyList($currentItem);
				foreach($propertyList as $k => $property) {
					if(!$property->getIsShow()) {
						unset($propertyList[$k]);
					}
				}

				# Permission list

				$permissionList = array();

				foreach($elementList as $element) {
					if(isset($elementPermissionMap[$element->getId()])) {
						$permissionList[$element->getId()] = $elementPermissionMap[$element->getId()];
					} else {
						if($element->getUser() && $element->getUser()->getId() == $loggedUser->getId()) {
							$permissionList[$element->getId()] = $itemPermission->getOwnerPermission();
						} elseif($element->getGroup() && $element->getGroup()->getId() == $loggedUserGroup->getId()) {
							$permissionList[$element->getId()] = $itemPermission->getGroupPermission();
						} else {
							$permissionList[$element->getId()] = $itemPermission->getWorldPermission();
						}
					}
				}

				$total = $pager->getTotal();

				$model->set('elementList', $elementList);
				$model->set('propertyList', $propertyList);
				$model->set('permissionList', $permissionList);
				$model->set('orderBy', $orderBy);

			} else {

				# Get count

				$total = $pager->getCountOnly();

			}

			$empty =
				$total || isset($filterModelAndView)
				? false
				: true;

			$model->set('currentElement', $currentElement);
			$model->set('currentItem', $currentItem);
			$model->set('pager', $pager);
			$model->set('openItem', $openItem);
			$model->set('selfUrl', PATH_ADMIN_BROWSE.'?module=ViewBrowse');
			$model->set('baseUrl', PATH_ADMIN_BROWSE);
			$model->set('empty', $empty);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ViewBrowse.elementList');
		}

		private function makeElementForm()
		{
			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('elementId')->
				ofBase('Element')
			)->
			import($_GET);

			if(
				!$form->getValue('elementId') instanceof Element
				|| $form->getValue('elementId') instanceof Root
			) {
				$form->markWrong('elementId');
			}

			return $form;
		}

		private function makeElementListForm()
		{
			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('elementId')->
				ofBase('Element')
			)->
			add(
				Primitive::identifier('sortItemId')->
				of('Item')
			)->
			add(
				Primitive::string('sortPropertyName')
			)->
			add(
				Primitive::choice('sortDirection')->
				setList(array('asc' => 'asc', 'desc' => 'desc'))
			)->
			add(
				Primitive::identifier('pagerItemId')->
				of('Item')
			)->
			add(
				Primitive::integer('pagerPage')
			)->
			import($_GET);

			return $form;
		}
	}
?>
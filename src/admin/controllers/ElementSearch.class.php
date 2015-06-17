<?php
	final class ElementSearch extends MethodMappedController
	{
		const PAGE_LIMIT = 29;

		private $defaultPerPage = 25;
		private $lastMaxNumber = 5;

		public function __construct()
		{
			$this->
			setMethodMapping('hint', 'hint')->
			setMethodMapping('multihint', 'multihint')->
			setMethodMapping('expand', 'expand')->
			setMethodMapping('show', 'showList')->
			setMethodMapping('save', 'saveList')->
			setMethodMapping('drop', 'dropList')->
			setMethodMapping('view', 'view')->
			setDefaultAction('view');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		protected function hint(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form =
				Form::create()->
				add(
					Primitive::identifier('itemId')->
					of('Item')->
					required()
				)->
				add(
					Primitive::string('term')
				)->
				import($request->getGet());

			if(!$form->getErrors()) {

				$item =  $form->getValue('itemId');
				$query =  $form->getValue('term');

				$itemClass = $item->getClass();

				$elementListCriteria =
					$itemClass->dao()->getValid()->
					addOrder(
						DBField::create('element_name', $itemClass->dao()->getTable())
					)->
					setLimit(20);

				if($query) {
					$elementListCriteria->add(
						Expression::orBlock(
							Expression::like(
								DBField::create('id', $itemClass->dao()->getTable()),
								DBValue::create('%'.$query.'%')
							),
							Expression::like(
								DBField::create('element_name', $itemClass->dao()->getTable()),
								DBValue::create('%'.$query.'%')
							)
						)
					);
				}

				$elementList = $elementListCriteria->getList();

				$prev = null;
				$k = 2;
				$hint = array();

				foreach($elementList as $element) {
					$id = $element->getId();
					$name = $element->getAlterName();
					$name = str_replace('&nbsp;', ' ', $name);
					if($prev == $name) {
						$name = $name.' '.$k;
						$k++;
					} else {
						$name = $name;
						$k = 2;
					}
					$hint[] = array(
						'id' => $id,
						'value' => $name,
					);
					$prev = $element->getAlterName();
				}

				$model->set('hint', $hint);
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementSearch.hint');
		}

		protected function multihint(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form =
				Form::create()->
				add(
					Primitive::identifier('propertyId')->
					of('Property')->
					required()
				)->
				add(
					Primitive::string('q')
				)->
				import($request->getGet());

			if(!$form->getErrors()) {

				$property =  $form->getValue('propertyId');
				$query =  $form->getValue('q');

				$propertyClass = $property->getClass(null)->setParameters();

				$showItemList = $propertyClass->getParameterValue('showItems');

				$hint = array();

				foreach($showItemList as $item) {

					$itemClass = $item->getClass();

					$elementListCriteria =
						$itemClass->dao()->getValid()->
						addOrder(
							DBField::create('element_name', $itemClass->dao()->getTable())
						)->
						setLimit(20);

					if($query) {
						$elementListCriteria->add(
							Expression::orBlock(
								Expression::like(
									DBField::create('id', $itemClass->dao()->getTable()),
									DBValue::create('%'.$query.'%')
								),
								Expression::like(
									DBField::create('element_name', $itemClass->dao()->getTable()),
									DBValue::create('%'.$query.'%')
								)
							)
						);
					}

					$elementList = $elementListCriteria->getList();

					$prev = null;
					$k = 2;

					foreach($elementList as $element) {
						$id = $element->getPolymorphicId();
						$name = $element->getElementName();
						$name = str_replace('&nbsp;', ' ', $name);
						if($prev == $name) {
							$name = $name.' '.$k;
							$k++;
						} else {
							$name = $name;
							$k = 2;
						}
						$hint[] = array(
							'id' => $id,
							'value' => $name,
						);
						$prev = $element->getElementName();
					}

				}

				$model->set('hint', $hint);
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementSearch.hint');
		}

		protected function expand(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form =
				Form::create()->
				add(
					Primitive::identifier('itemId')->
					of('Item')->
					required()
				)->
				import($_POST);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$item = $form->getValue('itemId');

				$search = $loggedUser->getParameter('search');
				$search['current'] = $item->getId();
				$loggedUser->setParameter('search', $search);

				$propertyList = Property::dao()->getPropertyList($item);

				$fieldList = array();
				foreach($propertyList as $property) {
					$content = $property->getClass(null)->getElementSearchView($form);
					if(!$content) continue;
					$content = str_replace(
						array('<', '>'),
						array('[', ']'),
						$content
					);
					$fieldList[] = $content;
				}

				$model->set('fieldList', $fieldList);

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementSearch');
		}

		protected function showList(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form = Form::create();

			$form->
			add(
				Primitive::identifier('itemId')->
				of('Item')->
				required()
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

				$item = $form->getValue('itemId');
				$sortFieldName = $form->getValue('fieldName');
				$sortDirection = $form->getValue('direction');
				$currentPage = $form->getValue('page');
				$isFilter = $form->getValue('filter');

				$isAsc = $sortDirection == 'desc' ? false : true;

				if($sortFieldName) {
					$sort = Session::get('sort');
					$sort['search'][$item->getId()] = array(
						'fieldName' => $sortFieldName,
						'isAsc' => $isAsc,
					);
					Session::assign('sort', $sort);
					$currentPage = 1;
				}

				if($currentPage) {
					$page = Session::get('page');
					$page['search'][$item->getId()] = $currentPage;
					Session::assign('page', $page);
				}

				if(
					$sortFieldName
					|| $currentPage
					|| $isFilter
				) {

					$form = $this->makeElementSearchForm($item);

					$elementId = $form->getValue('elementId');
					$elementName = $form->getValue('elementName');
					$shortName = $form->getValue('shortName');

					$propertyList = Property::dao()->getPropertyList($item);

					$itemClass = $item->getClass();
					$criteria = $itemClass->dao()->getByStatus('root');

					if($elementId) {
						$criteria->
						add(
							Expression::eq(
								new DBField('id', $itemClass->dao()->getTable()),
								new DBValue($elementId)
							)
						);
					}

					if($elementName) {
						$criteria->
						add(
							Expression::like(
								new DBField('element_name', $itemClass->dao()->getTable()),
								new DBValue('%'.$elementName.'%')
							)
						);
					}

					if($shortName) {
						$criteria->
						add(
							Expression::like(
								new DBField('element_path', $itemClass->dao()->getTable()),
								new DBValue('%'.$shortName.'%')
							)
						);
					}

					foreach($propertyList as $property) {
						$criteria = $property->getClass(null)->add2criteria($criteria, $form);
					}

					$elementListModelAndView = $this->getElementListModelAndView(
						$request,
						$item,
						$criteria
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
			import($_POST);

			$edited = $form->getValue('edited');

			$changed = array();
			$saved = array();

			foreach($edited as $elementId => $propertyList) {
				$element = Element::getByPolymorphicId($elementId);
				if($element instanceof Element) {

					$item = $element->getItem();
					$form = Form::create();

					foreach($propertyList as $propertyName => $flag) {
						if(!$flag) continue;
						$property = Property::dao()->getPropertyByName($item, $propertyName);
						$propertyClass = $property->getClass($element);
						$form = $propertyClass->add2multiform($form);
					}
					$form->import($_POST);

					foreach($propertyList as $propertyName => $flag) {
						if(!$flag) continue;
						$property = Property::dao()->getPropertyByName($item, $propertyName);
						$primitiveName = 'edit_'.$element->getClass().'_'.$element->getId().'_'.$propertyName.'';
						if($form->primitiveExists($primitiveName)) {
							$value = $form->getValue($primitiveName);
							$primitive = $form->get($primitiveName);
							$element->set($propertyName, $value);
							$propertyClass = $property->getClass($element);
							$changed[$elementId][$propertyName] = $propertyClass->getEditElementListView();
						}
					}

					if(isset($changed[$elementId])) {
						$element = $element->dao()->save($element);
						$saved[] = $element->getPolymorphicId();
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
			import($_POST);

			$check = $form->getValue('check');

			$itemList = Item::dao()->getItemList();

			$dropped = array();
			$refreshTree = false;
			$actionTypeId = null;

			$dropElementList = Element::getListByPolymorphicIds($check);

			foreach($dropElementList as $element) {
				$item = $element->getItem();
				$result =  $element->dao()->moveElementToTrash($element);
				$actionTypeId = UserActionType::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID;
				if(!$result) {
					$model->set('restrict', 'error');
				} else {
					if($item->getIsFolder()) {
						$refreshTree = true;
					}
					$dropped[] = $element->getPolymorphicId();
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

		protected function view(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			$itemList = Item::dao()->getDefaultItemList();

			$allowedItemList = array();

			foreach($itemList as $item) {
				if($item->getIsSearch()) {
					$allowedItemList[] = $item;
				}
			}

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

			$search = $loggedUser->getParameter('search');

			if(!isset($search['last'])) {
				$search['last'] = array();
			}

			for($i = 0; $i < $this->lastMaxNumber; $i++) {
				if(!isset($search['last'][$i])) {
					$search['last'][$i] = null;
				}
			}

			$form0 =
				Form::create()->
				add(
					Primitive::identifier('itemId')->
					of('Item')->
					required()
				)->
				import($request->getGet());

			if(!$form0->getErrors()) {

				$currentItem = $form0->getValue('itemId');
				$itemClass = $currentItem->getClass();

				# Plugin

				$pluginModelAndView = null;

				$pluginName = PluginManager::me()->getSearchPlugin(
					$currentItem->getItemName()
				);

				if($pluginName) {

					$pluginClass = new $pluginName($currentItem);
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
					set('item', $currentItem)->
					set('selfUrl', PATH_ADMIN_BROWSE.'?module='.get_class($this))->
					set('baseUrl', PATH_ADMIN_BROWSE);

					if(
						$pluginView instanceof RedirectView
						|| $request->hasGetVar('print')
					) {
						return $pluginModelAndView;
					}
				}

				$last = array($currentItem->getId());
				foreach($search['last'] as $itemId) {
					if(
						$currentItem->getId() != $itemId
						&& sizeof($last) < $this->lastMaxNumber
					) {
						$last[] = $itemId;
					}
				}
				$search['last'] = $last;

				$loggedUser->setParameter('search', $search);

				$lastItemList = array();
				foreach($search['last'] as $itemId) {
					if($itemId) {
						try {
							$lastItemList[$itemId] = Item::dao()->getById($itemId);
						} catch (ObjectNotFoundException $e) {}
					}
				}

				$form = $this->makeElementSearchForm($currentItem);

				$elementId = $form->getValue('elementId');
				$elementName = $form->getValue('elementName');
				$shortName = $form->getValue('shortName');

				$propertyList = Property::dao()->getPropertyList($currentItem);

				$criteria = $itemClass->dao()->getByStatus('root');

				if($elementId) {
					$criteria->
					add(
						Expression::eq(
							new DBField('id', $itemClass->dao()->getTable()),
							new DBValue($elementId)
						)
					);
				}

				if($elementName) {
					$criteria->
					add(
						Expression::like(
							new DBField('element_name', $itemClass->dao()->getTable()),
							new DBValue('%'.$elementName.'%')
						)
					);
				}

				if($shortName) {
					$criteria->
					add(
						Expression::like(
							new DBField('element_path', $itemClass->dao()->getTable()),
							new DBValue('%'.$shortName.'%')
						)
					);
				}

				foreach($propertyList as $property) {
					$criteria = $property->getClass(null)->add2criteria($criteria, $form);
				}

				$elementListModelAndView = $this->getElementListModelAndView(
					$request,
					$currentItem,
					$criteria
				);

				$elementListModel = $elementListModelAndView->getModel();
				$pager = $elementListModel->get('pager');

				UserLog::me()->log(
					UserActionType::ACTION_TYPE_SEARCH_ID,
					$currentItem->getItemName()
				);

				$model->set('currentItem', $currentItem);
				$model->set('allowedItemList', $allowedItemList);
				$model->set('lastItemList', $lastItemList);
				$model->set('propertyList', $propertyList);
				$model->set('elementId', $elementId);
				$model->set('elementName', $elementName);
				$model->set('shortName', $shortName);
				$model->set('form', $form);
				$model->set('pager', $pager);
				$model->set('pluginModelAndView', $pluginModelAndView);
				$model->set('elementListModelAndView', $elementListModelAndView);

			} else {

				$currentItem = null;
				$propertyList = null;

				if(isset($search['current'])) {
					try {
						$currentItem = Item::dao()->getById($search['current']);
						$propertyList = Property::dao()->getPropertyList($currentItem);
					} catch (ObjectNotFoundException $e) {}
				}

				$lastItemList = array();
				foreach($search['last'] as $itemId) {
					if($itemId) {
						try {
							$lastItemList[$itemId] = Item::dao()->getById($itemId);
						} catch (ObjectNotFoundException $e) {}
					}
				}

				$model->set('currentItem', $currentItem);
				$model->set('allowedItemList', $allowedItemList);
				$model->set('lastItemList', $lastItemList);
				$model->set('propertyList', $propertyList);
				$model->set('elementId', null);
				$model->set('elementName', null);
				$model->set('shortName', null);
				$model->set('form', Form::create());
				$model->set('pager', null);

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementSearch');
		}

		private function getElementListModelAndView(
			HttpRequest $request,
			Item $item,
			Criteria $criteria
		)
		{
			$sort = Session::get('sort');
			$page = Session::get('page');

			$sortFieldName =
				isset($sort['search'][$item->getId()]['fieldName'])
				? $sort['search'][$item->getId()]['fieldName']
				: $item->getOrderBy()->getFieldName();

			$sortIsAsc =
				isset($sort['search'][$item->getId()]['isAsc'])
				? $sort['search'][$item->getId()]['isAsc']
				: $item->getOrderBy()->isAsc();

			$orderBy = OrderBy::create($sortFieldName);
			if(!$sortIsAsc) {
				$orderBy->desc();
			}

			$currentPage =
				isset($page['search'][$item->getId()])
				? $page['search'][$item->getId()]
				: 1;

			$elementListModelAndView = $this->ElementListHandleRequest(
				$request,
				$item,
				$criteria,
				$orderBy,
				$currentPage
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
			Item $currentItem,
			Criteria $criteria,
			OrderBy $orderBy,
			$currentPage = 1
		)
		{
			$model = Model::create();

			$currentItemClass = $currentItem->getClass();

			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

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

			$criteria->addOrder($orderBy);

			# Pager

			$pager = CriteriaPager::create($criteria);

			$perPage =
				!$currentItem->getPerPage()
				? Item::DEFAULT_PERPAGE
				: $currentItem->getPerPage();

			if($perPage) {
				$pager->
				setPerpage($perPage)->
				setPageLimit(self::PAGE_LIMIT)->
				setCurrentPage($currentPage);
			}

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
			$model->set('currentElement', null);
			$model->set('currentItem', $currentItem);
			$model->set('pager', $pager);
			$model->set('openItem', true);
			$model->set('selfUrl', PATH_ADMIN_BROWSE.'?module=ViewBrowse');
			$model->set('baseUrl', PATH_ADMIN_BROWSE);
			$model->set('empty', false);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ViewBrowse.elementList');
		}

		private function makeElementSearchForm(Item $currentItem)
		{
			$form =
				Form::create()->
				add(
					Primitive::integer('elementId')->
					addImportFilter(Filter::trim())->
					setMin(0)
				)->
				add(
					Primitive::string('elementName')->
					addImportFilter(Filter::trim())->
					setMin(1)->
					setMax(255)
				)->
				add(
					Primitive::string('shortName')->
					addImportFilter(Filter::trim())->
					setMin(1)->
					setMax(50)
				)->
				add(
					Primitive::integer('page')
				);

			$propertyList = Property::dao()->getPropertyList($currentItem);
			foreach($propertyList as $property) {
				$form = $property->getClass(null)->add2search($form);
			}

			$form->import($_GET);

			return $form;
		}
	}
?>
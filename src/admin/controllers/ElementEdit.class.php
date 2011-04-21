<?php
	final class ElementEdit extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('drop', 'drop')->
			setMethodMapping('restore', 'restore')->
			setMethodMapping('add', 'add')->
			setMethodMapping('save', 'save')->
			setMethodMapping('create', 'create')->
			setMethodMapping('edit', 'edit')->
			setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		public function drop(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {
				$model->set('form0', $form0);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			$currentElement = $form0->getValue('elementId');
			$currentItem = $currentElement->getItem();
			$itemClass = $currentItem->getClass();

			$permission = $currentElement->getPermission($loggedUser);

			if($permission < Permission::PERMISSION_DROP_ID) {
				$model->set('denied', 'drop');
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			try {

				# Before delete action
				try {
					$actionName = PluginManager::me()->getBeforeDeleteAction(
						$currentItem->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($currentElement);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				$returnElementId = $currentElement->getParent()->getPolymorphicId();

				# Drop element
				if($currentElement->getStatus() == 'trash') {
					$result = $itemClass->dao()->dropElement($currentElement);
					$actionTypeId = UserActionType::ACTION_TYPE_DROP_ELEMENT_ID;
				} else {
					$result = $itemClass->dao()->moveElementToTrash($currentElement);
					$actionTypeId = UserActionType::ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID;
				}

				if($result) {

					# After delete action
					try {
						$actionName = PluginManager::me()->getAfterDeleteAction(
							$currentItem->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($currentElement);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					# User log
					UserLog::me()->log(
						$actionTypeId,
						$result->getPolymorphicId()
					);

					Site::updateLastModified();

					$model->set('returnElementId', $returnElementId);
					$model->set('dropped', $result->getPolymorphicId());

				} else {

					$model->set('restrict', 'error');

				}

			} catch (BaseException $e) {}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function restore(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {
				$model->set('form0', $form0);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			$currentElement = $form0->getValue('elementId');
			$currentItem = $currentElement->getItem();
			$itemClass = $currentItem->getClass();

			$permission = $currentElement->getPermission($loggedUser);

			if($permission < Permission::PERMISSION_DROP_ID) {
				$model->set('denied', 'drop');
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			try {

				# After restore action

				$returnElementId = $currentElement->getParent()->getPolymorphicId();

				if($currentElement->getStatus() == 'trash') {

					# Before restore action

					try {
						$actionName = PluginManager::me()->getBeforeInsertAction(
							$currentItem->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($currentElement);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					$result = $itemClass->dao()->restoreElementFromTrash($currentElement);

					# After restore action

					try {
						$actionName = PluginManager::me()->getAfterInsertAction(
							$currentItem->getItemName()
						);
						if($actionName && ClassUtils::isClassName($actionName)) {
							$action = new $actionName($currentElement);
						}
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}

					# User log
					UserLog::me()->log(
						UserActionType::ACTION_TYPE_RESTORE_ELEMENT_ID,
						$result->getPolymorphicId()
					);

					Site::updateLastModified();
				}

				$model->set('returnElementId', $returnElementId);

				# Refresh tree
				if($currentItem->getIsFolder()) {
					$tree = Tree::getTree();
					$model->set('tree', $tree);
				}

			} catch (BaseException $e) {}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function add(HttpRequest $request)
		{
			$model = Model::create();

			$form0 = Form::create();

			$form0->
			add(
				Primitive::identifier('itemId')->
				of('Item')->
				required()
			)->
			import($request->getGet());

			if($form0->getErrors()) {
				$model->set('form0', $form0);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			$currentItem = $form0->getValue('itemId');

			$propertyList = Property::dao()->getPropertyList($currentItem);

			$form = $this->makeAddForm($currentItem);

			if($form->getErrors()) {
				$model->set('form', $form);
				$model->set('propertyList', $propertyList);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			try {

				$loggedUser = LoggedUser::getUser();

				$currentElement = $currentItem->getClass();

				$currentElement->
				setElementName($form->getValue('elementName'))->
				setShortName($form->getValue('shortName'))->
				setStatus('root')->
				setGroup($loggedUser->getGroup())->
				setUser($loggedUser);

				foreach($propertyList as $property) {
					$property->getClass($currentElement)->setParameters()->set($form);
				}

				# Before insert action
				try {
					$actionName = PluginManager::me()->getBeforeInsertAction(
						$currentItem->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($currentElement);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				# Add element
				$currentElement =
					$currentItem->getClass()->dao()->
					addElement($currentElement);

				foreach($propertyList as $property) {
					$property->getClass($currentElement)->setParameters()->setAfter($form);
				}

				# After insert action
				try {
					$actionName = PluginManager::me()->getAfterInsertAction(
						$currentItem->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($currentElement);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				# Refresh tree
				if($currentItem->getIsFolder()) {
					$tree = Tree::getTree();
					$model->set('tree', $tree);
				}

				# User log
				UserLog::me()->log(
					UserActionType::ACTION_TYPE_ADD_ELEMENT_ID,
					$currentElement->getPolymorphicId()
				);

				Site::updateLastModified();

				$model->set('parentId', $currentElement->getParent()->getPolymorphicId());

				$model->set('addElement', 'ok');

			} catch (BaseException $e) {
				$model->set('addElement', 'error');
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function save(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {
				$model->set('form0', $form0);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			$currentElement = $form0->getValue('elementId');
			$currentItem = $currentElement->getItem();

			$permission = $currentElement->getPermission($loggedUser);

			if($permission < Permission::PERMISSION_WRITE_ID) {
				$model->set('denied', 'save');
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			$propertyList = Property::dao()->getPropertyList($currentItem);

			$form = $this->makeSaveForm($currentItem);

			if($form->getErrors()) {
				$model->set('form', $form);
				$model->set('propertyList', $propertyList);
				return
					ModelAndView::create()->
					setModel($model)->
					setView('request/ElementEdit');
			}

			try {

				$originalElement = clone $currentElement;

				# Before update action
				try {
					$actionName = PluginManager::me()->getBeforeUpdateAction(
						$currentItem->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($originalElement);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				$currentElement->
				setElementName($form->getValue('elementName'))->
				setShortName($form->getValue('shortName'));

				foreach($propertyList as $property) {
					$propertyClass = $property->getClass($currentElement)->setParameters();
					$propertyClass->set($form);
				}

				# Save element
				$currentElement =
					$currentItem->getClass()->dao()->
					saveElement($currentElement);

				# After update action
				try {
					$actionName = PluginManager::me()->getAfterUpdateAction(
						$currentItem->getItemName()
					);
					if($actionName && ClassUtils::isClassName($actionName)) {
						$action = new $actionName($currentElement, $originalElement);
					}
				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}

				$propertyContent = array();

				foreach($propertyList as $property) {
					$propertyClass = $property->getClass($currentElement)->setParameters();
					$propertyClass->setAfter($form);

					# Update properties content
					if($propertyClass->isUpdate()) {
						$content = $propertyClass->getEditElementView();
						$propertyContent[] = array(
							'propertyName' => $property->getPropertyName(),
							'propertyContent' => str_replace(array('<', '>'), array('[[[', ']]]'), $content),
						);
					}
				}

				# User log
				UserLog::me()->log(
					UserActionType::ACTION_TYPE_SAVE_ELEMENT_ID,
					$currentElement->getPolymorphicId()
				);

				Site::updateLastModified();

				if($currentItem->getIsFolder()) {
					$model->set('elementId', $currentElement->getPolymorphicId());
					$model->set('elementName', $currentElement->getElementName());
				}

				$model->set('propertyContent', $propertyContent);
				$model->set('saveElement', 'ok');

			} catch (BaseException $e) {
				$model->set('saveElement', 'error');
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function create(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$form = $this->makeCreateForm();

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$currentItem = $form->getValue('itemId');
			$parentElement = $form->getValue('parentId');

			$currentElement =
				$currentItem->getClass()->
				setId(null)->
				setStatus('root');

			# Plugin

			$pluginModelAndView = null;

			$pluginName = PluginManager::me()->getEditPlugin(
				$currentElement->getPolymorphicId()
			);

			if($pluginName) {

				$pluginClass = new $pluginName($currentElement);
				$pluginModelAndView = $pluginClass->handleRequest($request);

				$pluginModel = $pluginModelAndView->getModel();
				$pluginView = $pluginModelAndView->getView();

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

				if($request->hasGetVar('print')) {
					return $pluginModelAndView;
				}
			}

			# Property list
			$propertyList = Property::dao()->getPropertyList($currentItem);

			foreach($propertyList as $property) {
				if(
					$property->getPropertyClass() == 'OneToOneProperty'
					&& $property->getFetchClass() == $parentElement->getClass()
				) {
					$setter = $property->setter();
					$currentElement->$setter($parentElement);
				}
			}

			# Parent list
			$parentList = $currentElement->getParentList();

			# Set id for active element
			$activeElement =
				Session::exist('activeElement')
				? Session::get('activeElement')
				: Root::me();

			$oldActiveElement =
				Session::exist('oldActiveElement')
				? Session::get('oldActiveElement')
				: Root::me();

			$model->set('mode', 'create');
			$model->set('currentItem', $currentItem);
			$model->set('currentElement', $currentElement);
			$model->set('parentList', $parentList);
			$model->set('pluginModelAndView', $pluginModelAndView);
			$model->set('propertyList', $propertyList);
			$model->set('oldActiveElement', $oldActiveElement);
			$model->set('activeElement', $activeElement);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementEdit');
		}

		public function edit(HttpRequest $request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

			$form = $this->makeEditForm();

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$currentElement = $form->getValue('elementId');
			$currentItem = $currentElement->getItem();

			$permission = $currentElement->getPermission($loggedUser);

			if(
				!$currentItem->isDefault()
				|| $permission < Permission::PERMISSION_READ_ID
			) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$parentList = $currentElement->getParentList();

			# Plugin

			$pluginModelAndView = null;

			$pluginName = PluginManager::me()->getEditPlugin(
				$currentElement->getPolymorphicId()
			);

			if($pluginName) {

				$pluginClass = new $pluginName($currentElement);
				$pluginModelAndView = $pluginClass->handleRequest($request);

				$pluginModel = $pluginModelAndView->getModel();
				$pluginView = $pluginModelAndView->getView();

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

				if($request->hasGetVar('print')) {
					return $pluginModelAndView;
				}
			}

			# Property list
			$propertyList = Property::dao()->getPropertyList($currentItem);

			# Set id for active element and opened folder in tree
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

			UserLog::me()->log(
				UserActionType::ACTION_TYPE_EDIT_ELEMENT_ID,
				$currentElement->getPolymorphicId()
			);

			$model->set('mode', 'edit');
			$model->set('currentItem', $currentItem);
			$model->set('currentElement', $currentElement);
			$model->set('permission', $permission);
			$model->set('parentList', $parentList);
			$model->set('pluginModelAndView', $pluginModelAndView);
			$model->set('propertyList', $propertyList);
			$model->set('activeElement', $activeElement);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementEdit');
		}

		private function makeCreateForm()
		{
			$form = Form::create();

			$form->add(
				Primitive::identifier('itemId')->
				of('Item')->
				required()
			)->
			add(
				Primitive::polymorphicIdentifier('parentId')->
				ofBase('Element')->
				required()
			)->
			importMore($_GET);

			return $form;
		}

		private function makeEditForm()
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

		private function makeAddForm(Item $currentItem)
		{
			$form = Form::create();

			$propertyList = Property::dao()->getPropertyList($currentItem);

			$form->
			add(
				Primitive::string('elementName')->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::string('shortName')->
				setAllowedPattern('/^([a-z0-9_\-.]*)$/i')->
				setMax(50)
			);

			foreach($propertyList as $property) {
				$form = $property->getClass(null)->setParameters()->add2form($form);
			}

			$form->
			import($_POST)->
			importMore($_FILES);

			return $form;
		}

		private function makeSaveForm(Item $currentItem)
		{
			$form = Form::create();

			$propertyList = Property::dao()->getPropertyList($currentItem);

			$form->
			add(
				Primitive::string('elementName')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())->
				addImportFilter(Filter::replaceSymbols('\'', '&#146;'))
			)->
			add(
				Primitive::string('shortName')->
				setAllowedPattern('/^([a-z0-9_\-.]*)$/i')->
				setMax(50)
			);

			foreach($propertyList as $property) {
				$form = $property->getClass(null)->setParameters()->add2form($form);
			}

			$form->
			import($_POST)->
			importMore($_FILES);

			return $form;
		}
	}
?>
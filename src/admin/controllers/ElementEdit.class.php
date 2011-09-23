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

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentElement = $form0->getValue('elementId');
				$currentItem = $currentElement->getItem();
				$itemClass = $currentItem->getClass();

				try {

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
						# User log
						UserLog::me()->log(
							$actionTypeId,
							$result->getPolymorphicId()
						);
						$model->set('returnElementId', $returnElementId);
						$model->set('dropped', $result->getPolymorphicId());
					} else {
						$model->set('restrict', 'error');
					}

				} catch (BaseException $e) {}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function restore(HttpRequest $request)
		{
			$model = Model::create();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentElement = $form0->getValue('elementId');
				$currentItem = $currentElement->getItem();
				$itemClass = $currentItem->getClass();

				try {

					$returnElementId = $currentElement->getParent()->getPolymorphicId();

					if($currentElement->getStatus() == 'trash') {
						$result = $itemClass->dao()->restoreElementFromTrash($currentElement);
						# User log
						UserLog::me()->log(
							UserActionType::ACTION_TYPE_RESTORE_ELEMENT_ID,
							$result->getPolymorphicId()
						);
					}

					$model->set('returnElementId', $returnElementId);

					# Refresh tree
					if($currentItem->getIsFolder()) {
						$tree = Tree::getTree();
						$model->set('tree', $tree);
					}

				} catch (BaseException $e) {}
			}

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
			importMore($_GET);

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentItem = $form0->getValue('itemId');

				$propertyList = Property::dao()->getPropertyList($currentItem);

				$form = $this->makeAddForm($currentItem);

				if($form->getErrors()) {

					$model->set('form', $form);
					$model->set('propertyList', $propertyList);

				} else {

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

						# Add element
						$currentElement =
							$currentItem->getClass()->dao()->
							addElement($currentElement);

						# User log
						UserLog::me()->log(
							UserActionType::ACTION_TYPE_ADD_ELEMENT_ID,
							$currentElement->getPolymorphicId()
						);

						foreach($propertyList as $property) {
							$property->getClass($currentElement)->setParameters()->setAfter($form);
						}

						# Refresh tree
						if($currentItem->getIsFolder()) {
							$tree = Tree::getTree();
							$model->set('tree', $tree);
						}

						$model->set('parentId', $currentElement->getParent()->getPolymorphicId());

						$model->set('addElement', 'ok');

					} catch (BaseException $e) {
						$model->set('addElement', 'error');
					}
				}

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function save(HttpRequest $request)
		{
			$model = Model::create();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentElement = $form0->getValue('elementId');
				$currentItem = $currentElement->getItem();

				$propertyList = Property::dao()->getPropertyList($currentItem);

				$form = $this->makeSaveForm($currentItem);

				if($form->getErrors()) {

					$model->set('form', $form);
					$model->set('propertyList', $propertyList);

				} else {

					try {

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

						# User log
						UserLog::me()->log(
							UserActionType::ACTION_TYPE_SAVE_ELEMENT_ID,
							$currentElement->getPolymorphicId()
						);

						$propertyContent = array();

						foreach($propertyList as $property) {
							$propertyClass = $property->getClass($currentElement)->setParameters();
							$propertyClass->setAfter($form);

							# Update properties content
							if($propertyClass->isUpdate()) {
								$content = $propertyClass->getEditElementView();
								$propertyContent[] = array(
									'propertyName' => $property->getPropertyName(),
									'propertyContent' => $content,
								);
							}
						}

						if($currentItem->getIsFolder()) {
							$model->set('elementId', $currentElement->getPolymorphicId());
							$model->set('elementName', $currentElement->getElementName());
						}

						$model->set('propertyContent', $propertyContent);
						$model->set('saveElement', 'ok');

					} catch (BaseException $e) {
						$model->set('saveElement', 'error');
					}
				}

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
			$loggedUserGroup = $loggedUser->getGroup();

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

			if(!$currentItem->isDefault()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			# Permission

			$elementPermission =
				ElementPermission::dao()->getByGroupAndElement(
					$loggedUserGroup,
					$currentElement
				);

			if($elementPermission) {

				$permission = $elementPermission->getPermission();

			} else {

				$itemPermission =
					ItemPermission::dao()->getByGroupAndItem(
						$loggedUserGroup,
						$currentItem
					);

				if($itemPermission) {

					if(
						$currentElement->getUser()
						&& $currentElement->getUser()->getId() == $loggedUser->getId()
					) {
						$permission = $itemPermission->getOwnerPermission();
					} elseif(
						$currentElement->getGroup()
						&& $currentElement->getGroup()->getId() == $loggedUserGroup->getId()
					) {
						$permission = $itemPermission->getGroupPermission();
					} else {
						$permission = $itemPermission->getWorldPermission();
					}

				} else {

					if(
						$currentElement->getUser()
						&& $currentElement->getUser()->getId() == $loggedUser->getId()
					) {
						$permission = $loggedUserGroup->getOwnerPermission();
					} elseif(
						$currentElement->getGroup()
						&& $currentElement->getGroup()->getId() == $loggedUserGroup->getId()
					) {
						$permission = $loggedUserGroup->getGroupPermission();
					} else {
						$permission = $loggedUserGroup->getWorldPermission();
					}
				}
			}

			$parentList = $currentElement->getParentList();

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

			$model->set('mode', 'edit');
			$model->set('currentItem', $currentItem);
			$model->set('currentElement', $currentElement);
			$model->set('permission', $permission);
			$model->set('parentList', $parentList);
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
				$form = $property->getClass(null)->add2form($form);
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
				$form = $property->getClass(null)->add2form($form);
			}

			$form->
			import($_POST)->
			importMore($_FILES);

			return $form;
		}
	}
?>
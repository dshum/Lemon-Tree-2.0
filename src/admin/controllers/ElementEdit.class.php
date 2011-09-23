<?php
	final class ElementEdit extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('drop', 'dropElement')->
				setMethodMapping('restore', 'restoreElement')->
				setMethodMapping('add', 'addElement')->
				setMethodMapping('save', 'saveElement')->
				setMethodMapping('create', 'createElement')->
				setMethodMapping('edit', 'editElement')->
				setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function dropElement($request)
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

					$result =
						$currentElement->getStatus() == 'trash'
						? $itemClass->dao()->dropElement($currentElement)
						: $itemClass->dao()->moveElementToTrash($currentElement);

					if($result) {
						$model->set('returnElementId', $returnElementId);
						$model->set('dropped', $result->getPolymorphicId());
					} else {
						$model->set('restrict', 'error');
					}

				} catch (BaseException $e) {

				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function restoreElement($request)
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
					}

					$model->set('returnElementId', $returnElementId);

					# Refresh tree
					if($currentItem->getIsFolder()) {
						$tree = Tree::getTree();
						$model->set('tree', $tree);
					}

				} catch (BaseException $e) {
					echo ErrorMessageUtils::printMessage($e);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementEdit');
		}

		public function addElement($request)
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

				$propertyList =
					Property::dao()->getPropertyList($currentItem);

				$form = $this->makeAddForm($currentItem);

				if($form->getErrors()) {

					$model->set('form', $form);
					$model->set('propertyList', $propertyList);

				} else {

					try {

						$currentElement = $currentItem->getClass();

						$currentElement->
						setElementName($form->getValue('elementName'))->
						setControllerName($form->getValue('controllerName'))->
						setPluginName($form->getValue('pluginName'))->
						setShortName($form->getValue('shortName'));
						foreach($propertyList as $property) {
							$property->getClass($currentElement)->set($form);
						}

						# Add element
						$currentElement =
							$currentItem->getClass()->dao()->
							addElement($currentElement);

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

		public function saveElement($request)
		{
			$model = Model::create();

			$form0 = $this->makeEditForm();

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentElement = $form0->getValue('elementId');
				$currentItem = $currentElement->getItem();

				$propertyList =
					Property::dao()->getPropertyList($currentItem);

				$form = $this->makeSaveForm($currentItem);

				if($form->getErrors()) {

					$model->set('form', $form);
					$model->set('propertyList', $propertyList);

				} else {

					try {

						$currentElement->
						setElementName($form->getValue('elementName'))->
						setControllerName($form->getValue('controllerName'))->
						setPluginName($form->getValue('pluginName'))->
						setShortName($form->getValue('shortName'));
						foreach($propertyList as $property) {
							$propertyClass = $property->getClass($currentElement);
							$propertyClass->set($form);
						}

						# Save element
						$currentElement =
							$currentItem->getClass()->dao()->
							saveElement($currentElement);

						# Update properties content
						$propertyContent = array();
						foreach($propertyList as $property) {
							$propertyClass = $property->getClass($currentElement);
							if($propertyClass->isUpdate()) {
								$content = $propertyClass->editOnElement();
								$content = str_replace(
									array('<', '>'),
									array('[', ']'),
									$content
								);
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

		public function createElement($request)
		{
			$form = $this->makeCreateForm();

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ElementList'));
			}

			$currentItem = $form->getValue('itemId');
			$parentElement = $form->getValue('parentId');

			$currentElement =
				$currentItem->getClass()->
				setId(null);

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
			if(Session::exist('activeElement')) {
				$activeElement = Session::get('activeElement');
			} else {
				$activeElement = Root::me();
			}

			if(Session::exist('oldActiveElement')) {
				$oldActiveElement = Session::get('oldActiveElement');
			} else {
				$oldActiveElement = Root::me();
			}

			$model = Model::create();
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

		public function editElement($request)
		{
			$form = $this->makeEditForm();

			if($form->getErrors()) {

				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ElementList'));

			} else {

				$currentElement = $form->getValue('elementId');
				$currentItem = $currentElement->getItem();

				if($currentItem->getClassType() == 'abstract' || $currentItem->getClassType() == 'virtual') {
					return
						ModelAndView::create()->
						setModel(Model::create())->
						setView(new RedirectToView('ElementList'));
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

				$model = Model::create();
				$model->set('mode', 'edit');
				$model->set('currentItem', $currentItem);
				$model->set('currentElement', $currentElement);
				$model->set('parentList', $parentList);
				$model->set('propertyList', $propertyList);
				$model->set('activeElement', $activeElement);

				return
					ModelAndView::create()->
					setModel($model)->
					setView('ElementEdit');
			}
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
				Primitive::string('controllerName')->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				setMax(50)
			)->
			add(
				Primitive::string('pluginName')->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				setMax(50)
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
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::string('controllerName')->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				setMax(50)
			)->
			add(
				Primitive::string('pluginName')->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				setMax(50)
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
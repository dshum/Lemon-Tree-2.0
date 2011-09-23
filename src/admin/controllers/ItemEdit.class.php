<?php
	final class ItemEdit extends MethodMappedController
	{
		private $dropped = array();
		private $added = array();
		private $refreshTree = false;

		public function __construct()
		{
			$this->
			setMethodMapping('add', 'add')->
			setMethodMapping('save', 'save')->
			setMethodMapping('create', 'create')->
			setMethodMapping('edit', 'edit')->
			setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();

			return parent::handleRequest($request);
		}

		public function add($request)
		{
			$form = $this->makeAddForm();

			$model = Model::create();

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$newItem = Item::create();
				$newItem->
				setItemName($form->getValue('itemName'))->
				setItemDescription($form->getValue('itemDescription'))->
				setClassType($form->getValue('classType'))->
				setParentClass($form->getValue('parentClass'));

				switch($form->getValue('classType')) {
					case 'abstract': case 'virtual':
						break;

					case 'default': default:
						$newItem->
						setMainPropertyDescription($form->getValue('mainPropertyDescription'))->
						setPathPrefix($form->getValue('pathPrefix'))->
						setIsFolder($form->getValue('isFolder'))->
						setOrderField($form->getValue('orderField'))->
						setOrderDirection($form->getValue('orderDirection') == 1)->
						setPerPage($form->getValue('perPage'));
						break;
				}

				try {
					# Add item
					$newItem = Item::dao()->addItem($newItem);
					$model->set('item', $newItem);

					# Auto generation
					try {
						Site::generateAuto();
						$model->set('autoGeneration', 'ok');
					} catch (BaseException $e) {
						$model->set('autoGeneration', 'error');
						echo ErrorMessageUtils::printMessage($e);
					}

					$model->set('addItem', 'ok');

				} catch (BaseException $e) {
					$model->set('addItem', 'error');
					echo ErrorMessageUtils::printMessage($e);
				}

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ItemEdit.add');
		}

		public function save($request)
		{
			$model = Model::create();

			$form0 = $this->makeItemEditForm();

			if($form0->getErrors()) {

				$model->set('form0', $form0);

			} else {

				$currentItem = $form0->getValue('itemId');

				$propertyList = Property::dao()->getPropertyList($currentItem);

				$form = $this->makeSaveForm($currentItem);

				if($form->getErrors()) {

					$model->set('form', $form);
					$model->set('currentItem', $currentItem);
					$model->set('propertyList', $propertyList);

				} else {

					try {

						$refreshTree = false;

						if($form->getErrors()) {

							$model->set('form', $form);
							$model->set('propertyList', $propertyList);

						} else {

							# Save item
							$this->saveItem($currentItem, $form);

							# Save properties
							$this->saveProperties($currentItem, $form);

							if(sizeof($this->dropped)) {
								$model->set('dropped', $this->dropped);
							}

							# Add properties
							$this->addFieldProperty($currentItem, $form);
							$this->addOneToOneProperty($currentItem, $form);
							$this->addOneToManyProperty($currentItem, $form);
							$this->addManyToManyProperty($currentItem, $form);

							if(sizeof($this->added)) {
								$model->set('added', $this->added);
							}

							# Auto generation
							try {
								Site::generateAuto();
								$model->set('autoGeneration', 'ok');
							} catch (BaseException $e) {
								$model->set('autoGeneration', 'error');
								echo ErrorMessageUtils::printMessage($e);
							}

							# Refresh tree
							if($this->refreshTree) {
								$tree = Tree::getTree();
								$model->set('tree', $tree);
							}

							$model->set('saveItem', 'ok');

						}

					} catch (BaseException $e) {
						$model->set('saveItem', 'error');
						echo ErrorMessageUtils::printMessage($e);
					}
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ItemEdit.save');
		}

		public function create($request)
		{
			# Current item
			$currentItem = Item::create();

			# Item list
			$itemList = Item::dao()->getItemList();

			# Property class list
			$propertyClassList = Property::getPropertyClassList();

			$fetchClassList = array();

			foreach($itemList as $item) {
				if(!$item->isAbstract()) {
					$fetchClassList[] = $item;
				}
			}

			$model =
				Model::create()->
				set('mode', 'create')->
				set('currentItem', $currentItem)->
				set('itemList', $itemList)->
				set('propertyList', array())->
				set('propertyClassList', $propertyClassList)->
				set('fetchClassList', $fetchClassList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ItemEdit');
		}

		public function edit($request)
		{
			$form = $this->makeItemEditForm();

			if(!$form->getErrors()) {

				# Current item
				$currentItem = $form->getValue('itemId');

				# Item list
				$itemList = Item::dao()->getItemList();

				# Property list
				$propertyList = Property::dao()->getPropertyList($currentItem);

				# Property class list
				$propertyClassList = Property::getPropertyClassList();

				$fetchClassList = array();

				foreach($itemList as $item) {
					if(!$item->isAbstract()) {
						$fetchClassList[] = $item;
					}
				}

				$model =
					Model::create()->
					set('mode', 'edit')->
					set('currentItem', $currentItem)->
					set('itemList', $itemList)->
					set('propertyList', $propertyList)->
					set('propertyClassList', $propertyClassList)->
					set('fetchClassList', $fetchClassList);

				return
					ModelAndView::create()->
					setModel($model)->
					setView('ItemEdit');

			} else {

				return
					ModelAndView::create()->
					setView(new RedirectToView('ItemList'));
			}
		}

		private function makeItemEditForm()
		{
			$form = Form::create();

			$form->
			add(
				Primitive::identifier('itemId')->
				of('Item')->
				required()
			)->
			import($_GET);

			return $form;
		}

		private function makeAddForm()
		{
			$itemList = Item::dao()->getItemList();

			$itemNameList = array();

			$classTypeList = array(
				'default' => 'default',
				'abstract' => 'abstract',
				'virtual' => 'virtual',
			);

			$orderFieldList = array(
				'elementOrder' => 'elementOrder',
				'elementName' => 'elementName',
			);

			$orderDirectionList = array(
				'0' => '0',
				'1' => '1',
			);

			$form = Form::create();

			$form->
			add(
				Primitive::string('itemName')->
				required()->
				setMax(50)->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('itemDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::choice('classType')->
				setList($classTypeList)->
				required()
			)->
			add(
				Primitive::string('parentClass')->
				setMax(50)->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				addImportFilter(Filter::trim())
			)->
			import($_POST)->
			addRule(
				'itemNameUnique',
				Expression::notIn(
					new FormField('itemName'),
					$itemNameList
				)
			)->
			checkRules();

			switch($form->getValue('classType')) {

				case 'abstract':
					break;

				case 'virtual':
					$form->
					add(
						Primitive::identifier('parentProperty')->
						of('Property')
					);
					break;

				case 'default': default:
					$form->
					add(
						Primitive::string('mainPropertyDescription')->
						required()->
						setMax(255)->
						addImportFilter(Filter::trim())
					)->
					add(
						Primitive::string('pathPrefix')->
						required()->
						setMax(50)->
						setAllowedPattern('/^([a-z0-9_\-]*)$/')->
						addImportFilter(Filter::trim())
					)->
					add(
						Primitive::boolean('isFolder')
					)->
					add(
						Primitive::choice('orderField')->
						setList($orderFieldList)
					)->
					add(
						Primitive::choice('orderDirection')->
						setList($orderDirectionList)
					)->
					add(
						Primitive::integer('perPage')->
						optional()->
						setMax(500)
					)->
					add(
						Primitive::identifier('parentProperty')->
						of('Property')
					);
					break;
			}

			$form->importMore($_POST);

			return $form;
		}

		private function makeSaveForm(Item $currentItem)
		{
			$form = Form::create();

			$itemList = Item::dao()->getItemList();
			$propertyList = Property::dao()->getPropertyList($currentItem);
			$propertyClassList = Property::getPropertyClassList();

			$fetchClassList = array();
			$fetchClassExtendedList = array();

			$fetchClassExtendedList['Root'] = 'Root';
			foreach($itemList as $item) {
				if($item->getClassType() != 'abstract') {
					$fetchClassList[$item->getItemName()] = $item->getItemName();
					$fetchClassExtendedList[$item->getItemName()] = $item->getItemName();
				}
			}

			$classTypeList = array(
				'default' => 'default',
				'abstract' => 'abstract',
				'virtual' => 'virtual',
			);

			$orderFieldList = array(
				'elementOrder' => 'elementOrder',
				'elementName' => 'elementName',
			);
			foreach($propertyList as $property) {
				$orderFieldList[$property->getPropertyName()] = $property->getPropertyName();
			}

			$orderDirectionList = array(
				'0' => '0',
				'1' => '1',
			);

			$allowedClassList = array();
			foreach($propertyClassList as $propertyClass => $propertyDescription) {
				$allowedClassList[$propertyClass] = $propertyClass;
			}
			$allowedClassList['OneToOneProperty'] = 'OneToOneProperty';
			$allowedClassList['OneToManyProperty'] = 'OneToManyProperty';
			$allowedClassList['ManyToManyProperty'] = 'ManyToManyProperty';

			$fetchStrategyIdList = array(
				FetchStrategy::JOIN => FetchStrategy::JOIN,
				FetchStrategy::CASCADE => FetchStrategy::CASCADE,
				FetchStrategy::LAZY => FetchStrategy::LAZY,
			);

			$onDeleteActionList = array();
			foreach(Property::getOnDeleteActionList() as $actionName => $actionDescription) {
				$onDeleteActionList[$actionName] = $actionName;
			}

			$form->
			add(
				Primitive::string('itemDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('parentClass')->
				setMax(50)->
				setAllowedPattern('/^([a-z0-9_]*)$/i')->
				addImportFilter(Filter::trim())
			)->
			import($_POST);

			switch($currentItem->getClassType()) {

				case 'abstract':
					break;

				case 'virtual':
					$form->
					add(
						Primitive::identifier('parentProperty')->
						of('Property')
					);
					break;

				case 'default': default:
					$form->
					add(
						Primitive::string('mainPropertyDescription')->
						required()->
						setMax(255)->
						addImportFilter(Filter::trim())
					)->
					add(
						Primitive::string('pathPrefix')->
						required()->
						setMax(50)->
						setAllowedPattern('/^([a-z0-9_\-]*)$/')->
						addImportFilter(Filter::trim())
					)->
					add(
						Primitive::boolean('isFolder')
					)->
					add(
						Primitive::choice('orderField')->
						setList($orderFieldList)
					)->
					add(
						Primitive::choice('orderDirection')->
						setList($orderDirectionList)
					)->
					add(
						Primitive::integer('perPage')->
						optional()->
						setMax(500)
					)->
					add(
						Primitive::identifier('parentProperty')->
						of('Property')
					);
					break;
			}

			foreach($propertyList as $property) {
				$form->
				add(
					Primitive::boolean('isShow_'.$property->getId().'')
				)->
				add(
					Primitive::boolean('isRequired_'.$property->getId().'')
				)->
				add(
					Primitive::string('propertyDescription_'.$property->getId().'')->
					required()->
					setMax(255)->
					addImportFilter(Filter::trim())
				)->
				add(
					Primitive::string('propertyName_'.$property->getId().'')->
					required()->
					setMax(50)->
					addImportFilter(Filter::trim())->
					setAllowedPattern('/^([a-z0-9]*)$/i')
				)->
				add(
					Primitive::choice('propertyClass_'.$property->getId().'')->
					setList($allowedClassList)->
					required()
				)->
				add(
					Primitive::choice('fetchClass_'.$property->getId().'')->
					setList($fetchClassExtendedList)
				)->
				add(
					Primitive::choice('fetchStrategyId_'.$property->getId().'')->
					setList($fetchStrategyIdList)
				)->
				add(
					Primitive::choice('onDelete_'.$property->getId().'')->
					setList($onDeleteActionList)
				)->
				add(
					Primitive::boolean('drop_'.$property->getId().'')
				);
			}

			$form->
			add(
				Primitive::boolean('isShow_add_field')
			)->
			add(
				Primitive::boolean('isRequired_add_field')
			)->
			add(
				Primitive::string('propertyDescription_add_field')->
				setMax(255)
			)->
			add(
				Primitive::string('propertyName_add_field')->
				setMax(50)->
				addImportFilter(Filter::trim())->
				setAllowedPattern('/^([a-z0-9]*)$/i')
			)->
			add(
				Primitive::choice('propertyClass_add_field')->
				setList($propertyClassList)
			)->
			add(
				Primitive::boolean('isShow_add_onetoone')
			)->
			add(
				Primitive::boolean('isRequired_add_onetoone')
			)->
			add(
				Primitive::string('propertyDescription_add_onetoone')->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('propertyName_add_onetoone')->
				setMax(50)->
				addImportFilter(Filter::trim())->
				setAllowedPattern('/^([a-z0-9_]*)$/i')
			)->
			add(
				Primitive::choice('fetchClass_add_onetoone')->
				setList($fetchClassExtendedList)
			)->
			add(
				Primitive::choice('fetchStrategyId_add_onetoone')->
				setList($fetchStrategyIdList)
			)->
			add(
				Primitive::choice('onDelete_add_onetoone')->
				setList($onDeleteActionList)
			)->
			add(
				Primitive::string('propertyDescription_add_onetomany')->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('propertyName_add_onetomany')->
				setMax(50)->
				addImportFilter(Filter::trim())->
				setAllowedPattern('/^([a-z0-9_]*)$/i')
			)->
			add(
				Primitive::choice('fetchClass_add_onetomany')->
				setList($fetchClassList)
			)->
			add(
				Primitive::string('propertyDescription_add_manytomany')->
				setMax(255)
			)->
			add(
				Primitive::string('propertyName_add_manytomany')->
				setMax(50)->
				addImportFilter(Filter::trim())->
				setAllowedPattern('/^([a-z0-9_]*)$/i')
			)->
			add(
				Primitive::choice('fetchClass_add_manytomany')->
				setList($fetchClassList)
			)->
			import($_POST);

			return $form;
		}

		private function saveItem(Item $currentItem, Form $form)
		{
			$currentItem->
			setItemDescription($form->getValue('itemDescription'))->
			setParentClass($form->getValue('parentClass'));

			switch($currentItem->getClassType()) {
				case 'abstract': case 'virtual':
					break;

				case 'default': default:
					if(
						$currentItem->getIsFolder() != $form->getValue('isFolder')
						|| $currentItem->getOrderField() != $form->getValue('orderField')
						|| $currentItem->getOrderDirection() != ($form->getValue('orderDirection') == 1)
					) {
						$this->refreshTree = true;
					}
					$currentItem->
					setMainPropertyDescription($form->getValue('mainPropertyDescription'))->
					setPathPrefix($form->getValue('pathPrefix'))->
					setIsFolder($form->getValue('isFolder'))->
					setOrderField($form->getValue('orderField'))->
					setOrderDirection($form->getValue('orderDirection') == 1)->
					setPerPage($form->getValue('perPage'));
					break;
			}

			return Item::dao()->saveItem($currentItem);
		}

		private function saveProperties(Item $currentItem, Form $form)
		{
			$propertyList = Property::dao()->getPropertyList($currentItem);

			$dropped = array();

			foreach($propertyList as $property) {
				if($form->getValue('drop_'.$property->getId().'')) {

					# Drop property
					Property::dao()->dropProperty($property);
					$this->dropped[$property->getId()] = $property->getId();

				} else {

					# Save property
					$newProperty = Property::create();
					$newProperty->
					setId($property->getId())->
					setItem($property->getItem())->
					setPropertyOrder($property->getPropertyOrder())->
					setPropertyParameters($property->getPropertyParameters())->
					setIsShow($form->getValue('isShow_'.$property->getId().''))->
					setIsRequired($form->getValue('isRequired_'.$property->getId().''))->
					setPropertyDescription($form->getValue('propertyDescription_'.$property->getId().''))->
					setPropertyName($form->getValue('propertyName_'.$property->getId().''))->
					setPropertyClass($form->getValue('propertyClass_'.$property->getId().''));

					switch($form->getValue('propertyClass_'.$property->getId().'')) {
						case 'OneToOneProperty':
							$newProperty->setFetchClass($form->getValue('fetchClass_'.$property->getId().''));
							if($form->getValue('fetchClass_'.$property->getId().'') instanceof Root) {
								$newProperty->setFetchStrategyId(FetchStrategy::LAZY);
							} else {
								$newProperty->setFetchStrategyId($form->getValue('fetchStrategyId_'.$property->getId().''));
							}
							$newProperty->setOnDelete($form->getValue('onDelete_'.$property->getId().''));
							break;
						case 'OneToManyProperty':
							$newProperty->
							setFetchClass($form->getValue('fetchClass_'.$property->getId().''))->
							setFetchStrategyId(null)->
							setOnDelete(null);
							break;
						case 'ManyToManyProperty':
							$newProperty->
							setFetchClass($form->getValue('fetchClass_'.$property->getId().''))->
							setFetchStrategyId(null)->
							setOnDelete(null);
							break;
						default:
							$newProperty->
							setFetchClass(null)->
							setFetchStrategyId(null)->
							setOnDelete(null);
							break;
					}

					if(
						$form->primitiveExists('parentProperty')
						&& $property == $form->getValue('parentProperty')
					) {
						$newProperty->setIsParent(true);
					} else {
						$newProperty->setIsParent(false);
					}

					$newProperty = Property::dao()->saveProperty($newProperty);

					if($newProperty->getPropertyClass() == 'OneToManyProperty') {
						$this->checkOneToOneProperty($newProperty);
					}
				}
			}
		}

		private function addFieldProperty(Item $currentItem, Form $form)
		{
			if(
				$form->getValue('propertyDescription_add_field')
				&& $form->getValue('propertyName_add_field')
				&& $form->getValue('propertyClass_add_field')
			) {
				$property = Property::create();
				$property->
				setId(null)->
				setItem($currentItem)->
				setIsShow($form->getValue('isShow_add_field'))->
				setIsRequired($form->getValue('isRequired_add_field'))->
				setPropertyDescription($form->getValue('propertyDescription_add_field'))->
				setPropertyName($form->getValue('propertyName_add_field'))->
				setPropertyClass($form->getValue('propertyClass_add_field'))->
				setIsParent(false);

				$property = Property::dao()->addProperty($property);

				$this->added[] = array(
					'table' => 'field',
					'id' => $property->getId(),
					'isShow' => $property->getIsShow(),
					'isRequired' => $property->getIsRequired(),
					'propertyDescription' => $property->getPropertyDescription(),
					'propertyName' => $property->getPropertyName(),
					'propertyClass' => $property->getPropertyClass(),
				);
			}
		}

		private function addOneToOneProperty(Item $currentItem, Form $form)
		{
			if(
				$form->getValue('propertyDescription_add_onetoone')
				&& $form->getValue('propertyName_add_onetoone')
				&& $form->getValue('fetchClass_add_onetoone')
			) {
				$property = Property::create();
				$property->
				setId(null)->
				setItem($currentItem)->
				setIsShow($form->getValue('isShow_add_onetoone'))->
				setIsRequired($form->getValue('isRequired_add_onetoone'))->
				setPropertyDescription($form->getValue('propertyDescription_add_onetoone'))->
				setPropertyName($form->getValue('propertyName_add_onetoone'))->
				setPropertyClass('OneToOneProperty')->
				setFetchClass($form->getValue('fetchClass_add_onetoone'))->
				setOnDelete($form->getValue('onDelete_add_onetoone'))->
				setIsParent(false);

				if($form->getValue('fetchStrategyId_add_onetoone') instanceof Root) {
					$property->setFetchStrategyId(FetchStrategy::LAZY);
				} else {
					$property->setFetchStrategyId($form->getValue('fetchStrategyId_add_onetoone'));
				}

				$property = Property::dao()->addProperty($property);

				$this->added[] = array(
					'table' => 'onetoone',
					'id' => $property->getId(),
					'isShow' => $property->getIsShow(),
					'isRequired' => $property->getIsRequired(),
					'propertyDescription' => $property->getPropertyDescription(),
					'propertyName' => $property->getPropertyName(),
					'propertyClass' => $property->getPropertyClass(),
					'fetchClass' => $property->getFetchClass(),
					'fetchStrategyId' => $property->getFetchStrategyId(),
					'onDelete' => $property->getOnDelete(),
				);
			}
		}

		private function addOneToManyProperty(Item $currentItem, Form $form)
		{
			if(
				$form->getValue('propertyDescription_add_onetomany')
				&& $form->getValue('propertyName_add_onetomany')
				&& $form->getValue('fetchClass_add_onetomany')
			) {
				$property = Property::create();
				$property->
				setId(null)->
				setItem($currentItem)->
				setPropertyDescription($form->getValue('propertyDescription_add_onetomany'))->
				setPropertyName($form->getValue('propertyName_add_onetomany'))->
				setPropertyClass('OneToManyProperty')->
				setFetchClass($form->getValue('fetchClass_add_onetomany'))->
				setIsParent(false);

				$property = Property::dao()->addProperty($property);

				$this->added[] = array(
					'table' => 'onetomany',
					'id' => $property->getId(),
					'propertyDescription' => $property->getPropertyDescription(),
					'propertyName' => $property->getPropertyName(),
					'propertyClass' => $property->getPropertyClass(),
					'fetchClass' => $property->getFetchClass(),
				);

				$this->checkOneToOneProperty($property);
			}
		}

		private function addManyToManyProperty(Item $currentItem, Form $form)
		{
			if(
				$form->getValue('propertyDescription_add_manytomany')
				&& $form->getValue('propertyName_add_manytomany')
				&& $form->getValue('fetchClass_add_manytomany')
			) {
				$property = Property::create();
				$property->
				setId(null)->
				setItem($currentItem)->
				setPropertyDescription($form->getValue('propertyDescription_add_manytomany'))->
				setPropertyName($form->getValue('propertyName_add_manytomany'))->
				setPropertyClass('ManyToManyProperty')->
				setFetchClass($form->getValue('fetchClass_add_manytomany'))->
				setIsParent(false);

				$property = Property::dao()->addProperty($property);

				$this->added[] = array(
					'table' => 'manytomany',
					'id' => $property->getId(),
					'propertyDescription' => $property->getPropertyDescription(),
					'propertyName' => $property->getPropertyName(),
					'propertyClass' => $property->getPropertyClass(),
					'fetchClass' => $property->getFetchClass(),
				);
			}
		}

		private function checkOneToOneProperty(Property $property)
		{
			$currentItem = $property->getItem();

			try {
				$fetchItem = Item::dao()->getItemByName($property->getFetchClass());

				$fetchItemPropertyList =
					Property::dao()->getPropertyList($fetchItem);

				$check = false;
				foreach($fetchItemPropertyList as $fetchItemProperty) {
					if(
						$fetchItemProperty->getPropertyClass() == 'OneToOneProperty'
						&& $fetchItemProperty->getFetchClass() == $currentItem->getItemName()
						&& Property::getColumnName($fetchItemProperty->getPropertyName()) == Property::getColumnName($currentItem->getItemName())
					) {
						$check = true;
					}
				}

				if(!$check) {
					$parentProperty = Property::create();
					$parentProperty->
					setId(null)->
					setItem($fetchItem)->
					setPropertyDescription($currentItem->getItemDescription())->
					setPropertyName(Item::getColumnName($currentItem->getItemName()))->
					setPropertyClass('OneToOneProperty')->
					setFetchClass($currentItem->getItemName())->
					setFetchStrategyId(FetchStrategy::LAZY)->
					setIsParent(false);

					$parentProperty = Property::dao()->addProperty($parentProperty);

					if($fetchItem->getId() == $currentItem->getId()) {
						$this->added[] = array(
							'table' => 'onetoone',
							'id' => $parentProperty->getId(),
							'isShow' => $parentProperty->getIsShow(),
							'propertyDescription' => $parentProperty->getPropertyDescription(),
							'propertyName' => $parentProperty->getPropertyName(),
							'propertyClass' => $parentProperty->getPropertyClass(),
							'fetchClass' => $parentProperty->getFetchClass(),
						);
					}
				}
			} catch (ObjectNotFoundException $e) {}
		}
	}
?>
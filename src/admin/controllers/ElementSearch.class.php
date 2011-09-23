<?php
	final class ElementSearch extends MethodMappedController
	{
		private $defaultPerPage = 25;
		private $lastMaxNumber = 5;

		public function __construct()
		{
			$this->
			setMethodMapping('expand', 'expand')->
			setMethodMapping('show', 'show')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		public function expand($request)
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
					$content = $property->getClass(null)->printOnElementSearch($form);
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

		public function show($request)
		{
			$model = Model::create();

			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			$itemList = Item::dao()->getDefaultItemList();

			if($loggedUserGroup->getId() == 12) {
				$allowedItemList = array();
				foreach($itemList as $item) {
					if(
						$item->getItemName() == 'QiwiTerminal'
						|| $item->getItemName() == 'QiwiTerminalPhoto'
					) {
						$allowedItemList[] = $item;
					}
				}
			} else {
				$allowedItemList = $itemList;
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
				import($_GET);

			if(!$form0->getErrors()) {

				$currentItem = $form0->getValue('itemId');
				$itemClass = $currentItem->getClass();

				if($search['last'][0] != $currentItem->getId()) {
					array_unshift($search['last'], $currentItem->getId());
					array_pop($search['last']);
				}

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
				$page = $form->getValue('page');

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
							new DBField('short_name', $itemClass->dao()->getTable()),
							new DBValue('%'.$shortName.'%')
						)
					);
				}

				foreach($propertyList as $property) {
					$criteria = $property->getClass(null)->add2criteria($criteria, $form);
				}

				$criteria->addOrder($currentItem->getOrderBy());

				$pager = CriteriaPager::create($criteria);

				$perpage =
					$currentItem->getPerPage()
					? $currentItem->getPerPage()
					: $this->defaultPerPage;

				$pager->setPerpage($perpage)->setCurrentPage($page);

				$elementList = $pager->getList();

				$model->set('currentItem', $currentItem);
				$model->set('allowedItemList', $allowedItemList);
				$model->set('lastItemList', $lastItemList);
				$model->set('propertyList', $propertyList);
				$model->set('elementId', $elementId);
				$model->set('elementName', $elementName);
				$model->set('shortName', $shortName);
				$model->set('form', $form);
				$model->set('elementList', $elementList);
				$model->set('pager', $pager);

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
				$model->set('elementList', null);
				$model->set('pager', null);

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementSearch');
		}

		private function makeElementSearchForm(Item $currentItem)
		{
			$form =
				Form::create()->
				add(
					Primitive::integer('elementId')->
					setMin(0)
				)->
				add(
					Primitive::string('elementName')->
					setMin(3)->
					setMax(255)
				)->
				add(
					Primitive::string('parent')
				)->
				add(
					Primitive::string('shortName')->
					setMin(3)->
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
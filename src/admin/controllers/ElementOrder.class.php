<?php
	final class ElementOrder extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('save', 'saveList')->
			setMethodMapping('show', 'showList')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		public function saveList(HttpRequest $request)
		{
			$model = Model::create();

			$form0 = $this->makeElementListForm();

			if(!$form0->getErrors()) {

				$parent = $form0->getValue("elementId");
				$item = $form0->getValue("itemId");
				$itemClass = $item->getClass();

				$form = $this->makeSaveListForm();

				if(!$form->getErrors()) {

					$orderList = $form->getValue('orderList');
					$orderField = $form->getValue('orderField');
					$orderDirection = $form->getValue('orderDirection');

					if($orderField == 'elementOrder') {

						foreach($orderList as $id => $order) {
							try {
								$element = $itemClass->dao()->getById($id);
								$element->setElementOrder($order);
								$element = $itemClass->dao()->save($element);
							} catch (ObjectNotFoundException $e) {}
						}

					} else {

						$orderBy = OrderBy::create($orderField);
						if($orderDirection == 'desc') {
							$orderBy->desc();
						} else {
							$orderBy->asc();
						}

						$elementList =
							$itemClass->dao()->getChildren($parent)->
							addOrder($orderBy)->
							getList();

						foreach($elementList as $order => $element) {
							$element->setElementOrder($order);
							$element = $itemClass->dao()->save($element);
						}
					}

					# User log
					UserLog::me()->log(
						UserActionType::ACTION_TYPE_ORDER_ELEMENT_LIST_ID,
						$parent->getPolymorphicId()
					);

					Site::updateLastModified();
				}

				$model->set('form', $form);
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementOrder');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeElementListForm();

			if(!$form->getErrors()) {

				$parent = $form->getValue("elementId");
				$item = $form->getValue("itemId");

				$propertyList = Property::dao()->getPropertyList($item);

				$itemClass = $item->getClass();
				$elementList =
					$itemClass->dao()->getChildren($parent)->
					addOrder($item->getOrderBy())->
					getList();

				$model->set("parentElement", $parent);
				$model->set("item", $item);
				$model->set("propertyList", $propertyList);
				$model->set("elementList", $elementList);

			}

			$model->set("form", $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementOrder');
		}

		private function makeElementListForm()
		{
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
			import($_GET);

			return $form;
		}

		private function makeSaveListForm()
		{
			$form =
				Form::create()->
				add(
					Primitive::set('orderList')->
					required()
				)->
				add(
					Primitive::string('orderField')->
					required()
				)->
				add(
					Primitive::choice('orderDirection')->
					setList(array('asc' => 'asc', 'desc' => 'desc'))->
					required()
				)->
				import($_POST);

			return $form;
		}
	}
?>
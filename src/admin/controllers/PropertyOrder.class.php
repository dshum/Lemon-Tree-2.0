<?php
	final class PropertyOrder extends MethodMappedController
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
			return parent::handleRequest($request);
		}

		public function saveList(HttpRequest $request)
		{
			$model = Model::create();

			$form0 = $this->makeItemForm();

			if(!$form0->getErrors()) {

				$item = $form0->getValue("itemId");

				$form = $this->makeSaveListForm();

				if(!$form->getErrors()) {

					$orderList = $form->getValue('orderList');

					foreach($orderList as $id => $order) {
						try {
							$property = Property::dao()->getById($id);
							$property->setPropertyOrder($order);
							$property =Property::dao()->save($property);
						} catch (ObjectNotFoundException $e) {}
					}

				}

			}

			$model->set("form", $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/PropertyOrder');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeItemForm();

			if(!$form->getErrors()) {

				$item = $form->getValue("itemId");

				$propertyList = Property::dao()->getPropertyList($item);

				$model->set("item", $item);
				$model->set("propertyList", $propertyList);

			}

			$model->set("form", $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('PropertyOrder');
		}

		private function makeItemForm()
		{
			$form = Form::create();

			$form->add(
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
				import($_POST);

			return $form;
		}
	}
?>
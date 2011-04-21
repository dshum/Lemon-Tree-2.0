<?php
	final class ItemOrder extends MethodMappedController
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

			return parent::handleRequest($request);
		}

		public function saveList(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeSaveListForm();

			if(!$form->getErrors()) {

				$orderList = $form->getValue('orderList');

				foreach($orderList as $id => $order) {
					try {
						$item = Item::dao()->getById($id);
						$item->setItemOrder($order);
						$item = Item::dao()->save($item);
					} catch (ObjectNotFoundException $e) {}
				}

			}

			$model->set("form", $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ItemOrder');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$itemList = Item::dao()->getItemList();

			$model->set('itemList', $itemList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ItemOrder');
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
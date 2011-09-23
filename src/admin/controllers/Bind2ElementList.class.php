<?php
	final class Bind2ElementList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('save', 'saveBindList')->
				setMethodMapping('edit', 'editBindList')->
				setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();

			return parent::handleRequest($request);
		}

		public function saveBindList($request)
		{
			$model = Model::create();

			$form = $this->makeElementForm();

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				$form->add(
					Primitive::boolean('check_'.$item->getId())
				);
			}
			$form->importMore($_POST);

			$currentElement = $form->getValue('elementId');

			try {
				Bind2Element::dao()->dropByElement($currentElement);

				foreach($itemList as $item) {
					if($form->getValue('check_'.$item->getId())) {

						$bind2Element =
							Bind2Element::create()->
							setElementId($currentElement->getPolymorphicId())->
							setBindItem($item);

						$bind2Element =
							Bind2Element::dao()->add($bind2Element);

					}
				}

			} catch (BaseException $e) {
				$model->set('bind', 'error');
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/Bind2ElementList');

		}

		public function editBindList($request)
		{
			$model = Model::create();

			$form = $this->makeElementForm();

			$currentElement = $form->getValue('elementId');

			$itemList = Item::dao()->getItemList();

			$bind2ItemList =
				Criteria::create(Bind2Item::dao())->
				add(
					Expression::eq(
						new DBField("item_id", Bind2Item::dao()->getTable()),
						new DBValue($currentElement->getItemId())
					)
				)->
				getList();

			$bind2ItemMap = array();
			foreach($bind2ItemList as $bind2Item) {
				$bind2ItemMap[$bind2Item->getBindItem()->getId()] = $bind2Item->getBindItem()->getId();
			}

			$bind2ElementList =
				Criteria::create(Bind2Element::dao())->
				add(
					Expression::eq(
						new DBField("element_id", Bind2Element::dao()->getTable()),
						new DBValue($currentElement->getPolymorphicId())
					)
				)->
				getList();

			$bind2ElementMap = array();
			foreach($bind2ElementList as $bind2Element) {
				$bind2ElementMap[$bind2Element->getBindItem()->getId()] = $bind2Element->getBindItem()->getId();
			}

			$model->set('currentElement', $currentElement);
			$model->set('bind2ItemMap', $bind2ItemMap);
			$model->set('bind2ElementMap', $bind2ElementMap);
			$model->set('itemList', $itemList);

			return ModelAndView::create()->
				setModel($model)->
				setView('Bind2ElementList');


		}

		private function makeElementForm()
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
			importMore($_GET);

			return $form;
		}
	}
?>
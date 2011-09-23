<?php
	final class ItemList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('drop', 'dropList')->
			setMethodMapping('show', 'showList')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function dropList(HttpRequest $request)
		{
			$form = $this->makeDropForm();

			$model = Model::create();

			$itemList = Item::dao()->getItemList();

			$refreshTree = false;

			try {

				foreach($itemList as $item) {
					if($form->getValue('drop_'.$item->getId().'')) {
						if($item->getIsFolder()) {
							$refreshTree = true;
						}
						$dropped[$item->getId()] = $item->getId();
						Item::dao()->dropItem($item);
					}
				}

				# Auto generation
				try {
					Site::generateAuto();
					$model->set('autoGeneration', 'ok');
				} catch (BaseException $e) {
					$model->set('autoGeneration', 'error');
					print_r($e);
				}

				# Refresh tree
				if($refreshTree) {
					$tree = Tree::getTree();
					$model->set('tree', $tree);
				}

				$model->set('dropList', 'ok');

			} catch (BaseException $e) {
				$model->set('dropList', 'error');
				print_r($e);
			}

			$model->set('dropped', $dropped);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ItemList');
		}

		public function showList(HttpRequest $request)
		{
			$itemList = Item::dao()->getItemList();

			$bindList = array();
			$bind2ItemList =
				Criteria::create(Bind2Item::dao())->
				getList();
			foreach($bind2ItemList as $bind2Item) {
				$bindList[$bind2Item->getItem()->getId()][] = $bind2Item->getBindItem()->getId();
			}

			$model = Model::create();
			$model->set("itemList", $itemList);
			$model->set("bindList", $bindList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ItemList');
		}

		private function makeDropForm()
		{
			$itemList = Item::dao()->getItemList();

			$form = Form::create();

			foreach($itemList as $item) {
				$form->
				add(
					Primitive::boolean('drop_'.$item->getId().'')
				);
			}

			$form->import($_POST);

			return $form;
		}
	}
?>
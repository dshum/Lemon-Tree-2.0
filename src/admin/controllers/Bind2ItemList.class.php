<?php
	final class Bind2ItemList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('save', 'save')->
			setMethodMapping('edit', 'edit')->
			setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			if(!$loggedUser->getGroup()->getIsDeveloper()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			Item::dao()->setItemList();

			return parent::handleRequest($request);
		}

		public function save(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->add(
					Primitive::identifier('itemId')->
					of('Item')->
					required()
				)->
				import($request->getGet());

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				$form->add(
					Primitive::boolean('check_'.$item->getId())
				);
			}
			$form->importMore($request->getPost());

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentItem = $form->getValue('itemId');

				try {
					Bind2Item::dao()->dropByItem($currentItem);

					foreach($itemList as $item) {
						if($form->getValue('check_'.$item->getId())) {

							$bind2Item =
								Bind2Item::create()->
								setId(null)->
								setItem($currentItem)->
								setBindItem($item);

							$bind2Item =
								Bind2Item::dao()->add($bind2Item);

						}
					}

				} catch (BaseException $e) {
					$model->set('bind', 'error');
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/Bind2ItemList');

		}

		public function edit(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('itemId')->
					of('Item')->
					required()
				)->
				import($request->getGet());

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$currentItem = $form->getValue('itemId');

			$itemList = Item::dao()->getItemList();

			$bind2ItemList =
				Criteria::create(Bind2Item::dao())->
				add(
					Expression::eqId(
						new DBField('item_id'),
						$currentItem
					)
				)->
				getList();

			$bind2ItemMap = array();
			foreach($bind2ItemList as $bind2Item) {
				$bind2ItemMap[$bind2Item->getBindItem()->getId()] = $bind2Item->getBindItem()->getId();
			}

			$model->set('itemList', $itemList);
			$model->set('currentItem', $currentItem);
			$model->set('bind2ItemMap', $bind2ItemMap);

			return ModelAndView::create()->
				setModel($model)->
				setView('Bind2ItemList');
		}
	}
?>
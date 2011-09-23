<?php
	final class ElementMove extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('move', 'moveList')->
			setMethodMapping('show', 'showList')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			return parent::handleRequest($request);
		}

		public function moveList(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('targetId')->
				ofBase('Element')->
				required()
			)->
			add(
				Primitive::set('check')
			)->
			import($request->getGet())->
			importMore($request->getPost());

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$target = $form->getValue('targetId');
				$check = $form->getValue('check');

				$itemList = Item::dao()->getItemList();

				$moved = array();
				$refreshTree = false;

				# Move element list
				$moveElementList = Element::getListByPolymorphicIds($check);

				foreach($moveElementList as $element) {
					$item = $element->getItem();
					if($item->getIsFolder()) {
						$refreshTree = true;
					}
					try {
						$element->dao()->moveElement($element, $target);
						$moved[] = $element->getPolymorphicId();
					} catch (BaseException $e) {
						ErrorMessageUtils::sendMessage($e);
					}
				}

				# User log
				UserLog::me()->log(
					UserActionType::ACTION_TYPE_MOVE_ELEMENT_LIST_ID,
					implode(', ', $moved)
				);

				# Refresh tree
				if($refreshTree) {
					$tree = Tree::getTree();
					$model->set('tree', $tree);
				}

			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ElementMove');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('sourceId')->
				ofBase('Element')->
				required()
			)->
			add(
				Primitive::set('check')
			)->
			import($request->getPost());

			if($form->getErrors()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));
			}

			$sourceElement = $form->getValue('sourceId');
			$check = $form->getValue('check');

			if($sourceElement instanceof Root) {

				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('ViewBrowse'));

			}

			$itemList = Item::dao()->getItemList();

			# Move element list
			$moveElementList = Element::getListByPolymorphicIds($check);

			# Show tree
			$itemElementList = array();

			$sourceItem = $sourceElement->getItem();
			$itemClass = $sourceItem->getClass();
			$itemElementList[$sourceItem->getId()] =
				$itemClass->dao()->getValid()->
				addOrder($sourceItem->getOrderBy())->
				getList();

			$checkList = array();
			$itemParentList = array();
			$tmpList = $itemElementList[$sourceItem->getId()];

			while(true) {
				$parentList = array();

				foreach($tmpList as $element) {
					$parent = $element->getParent();
					if(
						$parent instanceof Element
						&& !isset($checkList[$parent->getPolymorphicId()])
						&& $parent->getItem()
					) {
						$itemParentList[$parent->getItem()->getId()][] = $parent->getId();
						$parentList[] = $parent;
						$checkList[$parent->getPolymorphicId()] = 1;
					}
				}

				if(empty($parentList)) break;

				$tmpList = $parentList;
			}

			foreach($itemParentList as $itemId => $elementIdList) {
				if($itemId == $sourceItem->getId()) continue;

				$item = Item::dao()->getItemById($itemId);
				$itemClass = $item->getClass();

				$itemElementList[$item->getId()] =
					$itemClass->dao()->getValid()->
					add(
						Expression::in(
							new DBField('id', $itemClass->dao()->getTable()),
							$elementIdList
						)
					)->
					addOrder($item->getOrderBy())->
					getList();
			}

			$model->set('moveElementList', $moveElementList);
			$model->set('itemList', $itemList);
			$model->set('sourceItem', $sourceItem);
			$model->set('itemElementList', $itemElementList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ElementMove');
		}
	}
?>
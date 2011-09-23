<?php
	final class ViewTree extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('open', 'open')->
				setMethodMapping('view', 'view')->
				setDefaultAction('view');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function open(HttpRequest $request)
		{
			$form = Form::create();

			$form->
			add(
				Primitive::polymorphicIdentifier('elementId')->
				ofBase('Element')
			)->
			add(
				Primitive::choice('value')->
				setList(array('close' => 'close', 'open' => 'open'))->
				required()
			)->
			import($request->getPost());

			$isParent = 0;

			if(!$form->getErrors()) {

				$element = $form->getValue('elementId');
				$value = $form->getValue('value');
				$openFolderList = Session::get('openFolderList');

				if($value == 'open') {
					$openFolderList[$element->getPolymorphicId()] = 1;
				} elseif(isset($openFolderList[$element->getPolymorphicId()])) {
					unset($openFolderList[$element->getPolymorphicId()]);
					try {
						if(Session::exist('activeElement')) {
							$activeElement = Session::get('activeElement');
							if($activeElement instanceof Element) {
								$parentList = $activeElement->getParentList();
								foreach($parentList as $parent) {
									if($parent->getId() == $element->getId()) {
										$isParent = 1;
										break;
									}
								}
							}
						}
					} catch (BaseException $e) {}
				}
				Session::assign('openFolderList', $openFolderList);

			}

			$model = Model::create();
			$model->set('isParent', $isParent);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ViewTree');
		}

		public function view(HttpRequest $request)
		{
			Item::dao()->setItemList();
			Property::dao()->setPropertyList();

			$tree = Tree::getTree();

			if(Session::exist('activeElement')) {
				$activeElement = Session::get('activeElement');
			} else {
				$activeElement = Root::me();
			}

			$model = Model::create();
			$model->set('tree', $tree);
			$model->set('activeElement', $activeElement);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ViewTree');
		}
	}
?>
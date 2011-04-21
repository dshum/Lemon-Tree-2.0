<?php
	final class ParameterList extends MethodMappedController
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

			$form =
				Form::create()->
				add(
					Primitive::identifier('propertyId')->
					of('Property')->
					required()
				)->
				import($_GET);

			if(!$form->getErrors()) {

				$property = $form->getValue('propertyId');
				$propertyClass = $property->getClass(null)->setParameters();
				$parameterList = $propertyClass->getParameterList();

				foreach($parameterList as $name => $parameter) {
					$form->add($parameter->primitive());
				}

				$form->importMore($_POST);

				if(!$form->getErrors()) {

					$propertyParameterList = array();

					foreach($parameterList as $name => $parameter) {

						$value = $form->getValue($name);
						$default = $parameter->getDefault();
						$rawValue = $parameter->toRaw($value);
						$rawDefault = $parameter->toRaw($default);

						if($rawValue != $rawDefault) {
							$propertyParameterList[$name] = $rawValue;
						}
					}

					$property->setParameterList($propertyParameterList);

					Property::dao()->save($property);
				}
			}

			$model->set('parameterList', $parameterList);
			$model->set('form', $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/ParameterList');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('propertyId')->
					of('Property')->
					required()
				)->
				import($_GET);

			if(!$form->getErrors()) {

				$property = $form->getValue('propertyId');
				$propertyClass = $property->getClass(null)->setParameters();
				$parameterList = $propertyClass->getParameterList();

				$model->set("property", $property);
				$model->set("parameterList", $parameterList);

			}

			$model->set('form', $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('ParameterList');
		}
	}
?>
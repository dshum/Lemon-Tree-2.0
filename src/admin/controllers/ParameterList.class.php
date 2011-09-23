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
				$propertyClass = $property->getClass(null);
				$parameterList = $propertyClass->getParameterList();

				foreach($parameterList as $name => $parameter) {
					$form->add($parameter->primitive());
				}

				$form->importMore($_POST);

				if(!$form->getErrors()) {

					foreach($parameterList as $name => $parameter) {

						$value = $form->getValue($name);
						$default = $parameter->getDefault();
						$rawValue = $parameter->toRaw($value);
						$rawDefault = $parameter->toRaw($default);

						$property = $parameter->getProperty();

						try {
							$propertyParameter = Parameter::dao()->getParameterByName($property, $name);
						} catch (ObjectNotFoundException $e) {
							$propertyParameter = null;
						}

						if($rawValue == $rawDefault) {
							if($propertyParameter) {
								Parameter::dao()->dropParameter($propertyParameter);
							}
						} else {
							if($propertyParameter) {
								$propertyParameter->setParameterValue($rawValue);
								Parameter::dao()->saveParameter($propertyParameter);
							} else {
								$propertyParameter =
									Parameter::create()->
									setProperty($property)->
									setParameterName($name)->
									setParameterValue($rawValue);
								Parameter::dao()->addParameter($propertyParameter);
							}
						}

						$parameter->setValue($rawValue);
					}
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
				$propertyClass = $property->getClass(null);
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
<?php
	final class VirtualProperty extends BaseProperty
	{
		public function getElementListView()
		{
			$model =
				Model::create()->
				set('value', $this->value);

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementListView()
		{
			return $this->getElementListView();
		}

		public function getEditElementView()
		{
			$getter = $this->property->getter();
			$value = $this->element->$getter(true);

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $value);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function getElementSearchView(Form $form)
		{
			return null;
		}
	}
?>
<?php
	final class LinkProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::VARCHAR)->setSize(255);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" size="255" required="false" />';
		}

		public function add2form(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags());

			return $form->add($primitive);
		}

		public function add2multiform(Form $form)
		{
			$primitiveName =
				'edit_'.$this->element->getClass()
				.'_'.$this->element->getId()
				.'_'.$this->property->getPropertyName();

			$primitive =
				Primitive::string($primitiveName)->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags());

			return $form->add($primitive);
		}

		public function element()
		{
			return Element::getByPolymorphicId($this->value);
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('value', $this->value)->
				set('element', $this->element());

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementView()
		{
			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $this->value)->
				set('element', $this->element());

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function printOnElementSearch(Form $form)
		{
			$fetchClass = $this->property->getFetchClass();
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			$str = $this->property->getPropertyDescription().' (класс и ID элемента): ';
			$str .= '<input type="text" class="prop-mini" name="'.$this->property->getPropertyName().'" value="'.$value.'" style="width: 125px;">';
			return $str;
		}
	}
?>
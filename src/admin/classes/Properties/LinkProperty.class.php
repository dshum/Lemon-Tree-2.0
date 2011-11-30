<?php
	final class LinkProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('showItems', 'itemList', 'Классы элементов', array());

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
				Primitive::polymorphicIdentifier($this->property->getPropertyName())->
				ofBase('Element');

			if($this->property->getIsRequired()) {
				$primitive->required();
			}

			return $form->add($primitive);
		}

		public function element()
		{
			return Element::getByPolymorphicId($this->value);
		}

		public function isUpdate()
		{
			return true;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == false
				&& $form->primitiveExists($this->property->getPropertyName())
				&& $this->element instanceof Element
			) {
				$setter = $this->property->setter();
				$value = $form->getRawValue($this->property->getPropertyName());
				$this->element->$setter($value);
			}
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
			$required = $this->property->getIsRequired();
			$readonly = $this->getParameterValue('readonly');
			$showItemList = $this->getParameterValue('showItems');

			$fetchItems = array();
			foreach($showItemList as $showItem) {
				$fetchItems[] = $showItem->getId();
			}
			$fetchItems = implode(',', $fetchItems);

			$model =
				Model::create()->
				set('propertyId', $this->property->getId())->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $this->element())->
				set('required', $required)->
				set('readonly', $readonly)->
				set('showItemList', $showItemList);

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
<?php
	final class PasswordProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::VARCHAR)->setSize(255);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" size="255" required="false" />';
		}

		public function add2form(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(255);
			return $form->add($primitive);
		}

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':<br>';
			$str .= '<table class="ie"><tr><td><input type="password" class="prop-pass" name="'.$this->property->getPropertyName().'" value="'.$this->value.'" maxlength="255"></td></tr></table><br>';
			return $str;
		}

		public function printOnElementList()
		{
			$str = $this->value;
			return $str;
		}

		public function editOnElementList()
		{

		}
	}
?>
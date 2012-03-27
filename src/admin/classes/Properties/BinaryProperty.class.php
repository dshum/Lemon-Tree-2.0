<?php
	final class BinaryProperty extends BaseProperty
	{
		const SIZE = 16777216;

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::TEXT)->setName('MEDIUMBLOB');
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" required="false" />';
		}

		public function add2form(Form $form)
		{
			return
				$form->
				add(
					Primitive::boolean($this->property->getPropertyName().'_drop')
				)->
				add(
					Primitive::file($this->property->getPropertyName())->
					setMax(self::SIZE)
				);
		}

		public function isUpdate()
		{
			return true;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == true
				|| $this->getParameterValue('readonly') == true
			) {
				return false;
			}

			$file = $form->getValue($this->property->getPropertyName());
			$drop = $form->getValue($this->property->getPropertyName().'_drop');

			$setter = $this->property->setter();

			if($drop) {
				$this->element->$setter(null);
			} elseif($file) {
				$primitive = $form->get($this->property->getPropertyName());
				if(is_readable($primitive->getValue())) {
					$data = file_get_contents($primitive->getValue());
					$this->element->$setter($data);
					unlink($primitive->getValue());
				}
			}
		}
	}
?>
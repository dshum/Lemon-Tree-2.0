<?php
	final class TextareaProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);
			$this->addParameter('typograph', 'boolean', 'Типографика', true);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::TEXT);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" required="false" />';
		}

		public function add2form(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(65536)->
				addImportFilter(Filter::trim());

			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(MyRussianTypograph::me());
			}

			return $form->add($primitive);
		}

		public function add2multiform(Form $form)
		{
			$primitiveName =
				'edit_'.$this->element->getClass()
				.'_'.$this->element->getId()
				.'_'.$this->property->getPropertyName().'';

			$primitive =
				Primitive::string($primitiveName)->
				setMax(65536)->
				addImportFilter(Filter::trim());

			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(MyRussianTypograph::me());
			}

			return $form->add($primitive);
		}

		public function htmltext()
		{
			return nl2br($this->value);
		}
	}
?>
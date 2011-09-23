<?php
	final class TextfieldProperty extends BaseProperty
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
				addImportFilter(Filter::stripTags()->setAllowableTags('<a><b><i><u><strong><em><sup><sub>'));
			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(RussianTypograph::me());
			}
			return $form->add($primitive);
		}

		public function add2multiform(Form $form)
		{
			$primitiveName = 'edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'';
			$primitive =
				Primitive::string($primitiveName)->
				setMax(255)->
				addImportFilter(Filter::trim());
			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(RussianTypograph::me());
			}
			return $form->add($primitive);
		}
	}
?>
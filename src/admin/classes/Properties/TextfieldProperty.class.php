<?php
	final class TextfieldProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::VARCHAR)->setSize(255);

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);
			$this->addParameter('typograph', 'boolean', 'Типографика', true);
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

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':<br>';
			$str .= '<table class="ie"><tr><td><input type="text" class="prop" name="'.$this->property->getPropertyName().'" value="'.$this->value.'" maxlength="255"></td></tr></table><br>';
			return $str;
		}

		public function editOnElementList()
		{
			$str = '';
			$str .= '<input type="hidden" id="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" value="0">';
			$str .= '<div id="show['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']"><span class="dh">'.$this->value.'</span></div>';
			$str .= '<input type="text" id="edit['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'" default="'.$this->value.'" value="'.$this->value.'" class="prop-browse" maxlength="255">';
			return $str;
		}
	}
?>
<?php
	final class TextareaProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::TEXT);

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);
			$this->addParameter('typograph', 'boolean', 'Типографика', true);
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
			$primitiveName = 'edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'';
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

		public function editOnElement()
		{
			$str = '<span class="hand dashed" title="Увеличить" onclick="f = document.ElementEditForm; if(f.'.$this->property->getPropertyName().'.rows == 8) {f.'.$this->property->getPropertyName().'.rows = 40; this.title = \'Уменьшить\';} else {f.'.$this->property->getPropertyName().'.rows = 8; this.title = \'Увеличить\';}">'.$this->property->getPropertyDescription().'</span>:<br>';
			$str .= '<table class="ie"><tr><td><textarea class="prop" rows="8" name="'.$this->property->getPropertyName().'">'.$this->value.'</textarea></td></tr></table><br>';
			return $str;
		}

		public function printOnElementList()
		{
			$str = nl2br($this->value);
			return $str;
		}

		public function editOnElementList()
		{
			$str = '';
			$str .= '<input type="hidden" id="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" value="0">';
			$str .= '<div id="show['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']"><span class="dh">'.nl2br($this->value).'</span></div>';
			$str .= '<textarea id="edit['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'" class="prop-browse" default="'.$this->value.'">'.$this->value.'</textarea>';
			return $str;
		}
	}
?>
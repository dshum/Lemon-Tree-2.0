<?php
	final class BooleanProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::BOOLEAN)->setNull(false);

			if($this->element && $this->value === null) {
				$this->value = false;
				$setter = $property->setter();
				$this->element->$setter(false);
			}
			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Boolean" required="false" />';
		}

		public function add2form(Form $form)
		{
			return
				$form->
				add(
					Primitive::boolean($this->property->getPropertyName())
				);
		}

		public function add2multiform(Form $form)
		{
			$primitiveName = 'edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'';
			return
				$form->
				add(
					Primitive::boolean($primitiveName)
				);
		}

		public function add2search(Form $form)
		{
			return
				$form->
				add(
					Primitive::boolean($this->property->getPropertyName())
				);
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$value = $form->getValue($this->property->getPropertyName());
			if($value) {
				$columnName = Property::getColumnName($this->property->getPropertyName());
				$tableName = $criteria->getDao()->getTable();
				$criteria->
				add(
					Expression::isTrue(
						new DBField($columnName, $tableName)
					)
				);
			}
			return $criteria;
		}

		public function editOnElement()
		{
			$str = '<input type="checkbox" id="'.$this->property->getPropertyName().'" name="'.$this->property->getPropertyName().'" value="1"'.($this->value ? " checked" : "").' title="Выбрать"><label for="'.$this->property->getPropertyName().'">&nbsp;'.$this->property->getPropertyDescription().'</label><br><br>';
			return $str;
		}

		public function printOnElementList()
		{
			$str = $this->value ? 'Да' : 'Нет';
			return $str;
		}

		public function printOnElementSearch(Form $form)
		{
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			$str = '<input type="checkbox" id="'.$this->property->getPropertyName().'" name="'.$this->property->getPropertyName().'" value="1"'.($value ? " checked" : "").' title="Выбрать"><label for="'.$this->property->getPropertyName().'">&nbsp;'.$this->property->getPropertyDescription().'</label>';
			return $str;
		}

		public function editOnElementList()
		{
			$str = '';
			$str .= '<input type="hidden" id="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" value="0">';
			$str .= '<div id="show['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']"><span class="dh">'.($this->value ? 'Да' : 'Нет').'</span></div>';
			$str .= '<input type="checkbox" id="edit['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'" default="'.($this->value ? '1' : '0').'" value="1"'.($this->value ? ' checked' : '').' style="display: none;">';
			return $str;
		}
	}
?>
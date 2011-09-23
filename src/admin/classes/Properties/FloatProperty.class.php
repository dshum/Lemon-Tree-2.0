<?php
	final class FloatProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::REAL)->setSize(11);

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Float" required="false" />';
		}

		public function add2form(Form $form)
		{
			return $form->add(Primitive::float($this->property->getPropertyName()));
		}

		public function add2multiform(Form $form)
		{
			$primitiveName = 'edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'';
			return $form->add(Primitive::float($primitiveName));
		}

		public function add2search(Form $form)
		{
			return $form->
				add(Primitive::float($this->property->getPropertyName().'_from'))->
				add(Primitive::float($this->property->getPropertyName().'_to'));
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$from = $form->getValue($this->property->getPropertyName().'_from');
			$to = $form->getValue($this->property->getPropertyName().'_to');

			$columnName = Property::getColumnName($this->property->getPropertyName());
			$tableName = $criteria->getDao()->getTable();

			if($from !== null) {
				$criteria->
				add(
					Expression::gtEq(
						new DBField($columnName, $tableName),
						new DBValue($from)
					)
				);
			}

			if($to !== null && $to >= $from) {
				$criteria->
				add(
					Expression::ltEq(
						new DBField($columnName, $tableName),
						new DBValue($to)
					)
				);
			}

			return $criteria;
		}

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':&nbsp; ';
			$str .= '<input type="text" name="'.$this->property->getPropertyName().'" value="'.$this->value.'" class="prop-mini"><br><br>';
			return $str;
		}

		public function printOnElementSearch(Form $form)
		{
			$from =
				$form->primitiveExists($this->property->getPropertyName().'_from')
				? $form->getValue($this->property->getPropertyName().'_from')
				: null;
			$to =
				$form->primitiveExists($this->property->getPropertyName().'_to')
				? $form->getValue($this->property->getPropertyName().'_to')
				: null;

			$str = $this->property->getPropertyDescription().' ';
			$str .= 'от <input type="text" class="prop-mini" name="'.$this->property->getPropertyName().'_from" value="'.$from.'" style="width: 75px;">';
			$str .= ' до <input type="text" class="prop-mini" name="'.$this->property->getPropertyName().'_to" value="'.$to.'" style="width: 75px;">';
			return $str;
		}

		public function editOnElementList()
		{
			$str = '';
			$str .= '<input type="hidden" id="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edited['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" value="0">';
			$str .= '<div id="show['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']"><span class="dh">'.$this->value.'</span></div>';
			$str .= '<input type="text" id="edit['.$this->element->getPolymorphicId().']['.$this->property->getPropertyName().']" name="edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'" default="'.$this->value.'" value="'.$this->value.'" class="prop-number" maxlength="32">';
			return $str;
		}
	}
?>
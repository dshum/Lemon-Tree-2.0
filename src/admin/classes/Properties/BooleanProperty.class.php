<?php
	final class BooleanProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::BOOLEAN)->setNull(false);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Boolean" required="false" />';
		}

		public function fixValue()
		{
			if($this->element && $this->value === null) {
				$setter = $this->property->setter();
				$this->element->$setter(false);
			}
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
					Primitive::ternary($this->property->getPropertyName())->
					setFalseValue('false')->
					setTrueValue('true')
				);
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			if($value !== null) {
				$columnName = Property::getColumnName($this->property->getPropertyName());
				$tableName = $criteria->getDao()->getTable();
				if($value === true) {
					$criteria->
					add(
						Expression::isTrue(
							new DBField($columnName, $tableName)
						)
					);
				} else {
					$criteria->
					add(
						Expression::isFalse(
							new DBField($columnName, $tableName)
						)
					);
				}
			}
			return $criteria;
		}

		public function printOnElementSearch(Form $form)
		{
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			$str = $this->property->getPropertyDescription().': ';
			$str .= '<input type="radio" id="'.$this->property->getPropertyName().'_true" name="'.$this->property->getPropertyName().'" value="true"'.($value === true ? ' checked' : '').'><label for="'.$this->property->getPropertyName().'_true">Да</label>';
			$str .= '<input type="radio" id="'.$this->property->getPropertyName().'_false" name="'.$this->property->getPropertyName().'" value="false"'.($value === false ? ' checked' : '').'><label for="'.$this->property->getPropertyName().'_false">Нет</label>';
			$str .= '<input type="radio" id="'.$this->property->getPropertyName().'_null" name="'.$this->property->getPropertyName().'" value="null"'.($value === null ? ' checked' : '').'><label for="'.$this->property->getPropertyName().'_null">Не важно</label>';
			return $str;
		}
	}
?>
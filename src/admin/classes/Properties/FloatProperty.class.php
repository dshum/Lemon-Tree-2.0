<?php
	final class FloatProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('editable', 'boolean', 'Редактировать в списке', false);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::REAL)->setSize(11);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Float" required="false" />';
		}

		public function add2form(Form $form)
		{
			return $form->add(
				Primitive::float($this->property->getPropertyName())->
				addImportFilter(Filter::replaceSymbols(
					array(',', ' '),
					array('.', '')
				))
			);
		}

		public function add2multiform(Form $form)
		{
			$primitiveName = 'edit_'.$this->element->getClass().'_'.$this->element->getId().'_'.$this->property->getPropertyName().'';

			return $form->add(
				Primitive::float($primitiveName)->
				addImportFilter(Filter::replaceSymbols(
					array(',', ' '),
					array('.', '')
				))
			);
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

		public function getElementSearchView(Form $form)
		{
			$propertyDescription = $this->property->getPropertyDescription();
			if(mb_strlen($propertyDescription) > 50) {
				$propertyDescription = mb_substr($propertyDescription, 0, 50).'...';
			}

			$propertyName = $this->property->getPropertyName();

			$from =
				$form->primitiveExists($propertyName.'_from')
				? $form->getValue($propertyName.'_from')
				: null;
			$to =
				$form->primitiveExists($propertyName.'_to')
				? $form->getValue($propertyName.'_to')
				: null;

			$model =
				Model::create()->
				set('propertyName', $propertyName)->
				set('propertyDescription', $propertyDescription)->
				set('from', $from)->
				set('to', $to);

			$viewName = 'properties/'.get_class($this).'.search';

			return $this->render($model, $viewName);
		}
	}
?>
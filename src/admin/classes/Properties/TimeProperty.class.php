<?php
	final class TimeProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->value = $this->value ? $this->value : null;
		}

		public function getDataType()
		{
			return DataType::create(DataType::TIME);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Time" required="false" />';
		}

		public function add2form(Form $form)
		{
			return $form->
				add(Primitive::time($this->property->getPropertyName()));
		}

		public function add2search(Form $form)
		{
			return $form->
				add(Primitive::time($this->property->getPropertyName().'_from'))->
				add(Primitive::time($this->property->getPropertyName().'_to'));
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$from = $form->getValue($this->property->getPropertyName().'_from');
			$to = $form->getValue($this->property->getPropertyName().'_to');

			$columnName = Property::getColumnName($this->property->getPropertyName());
			$tableName = $criteria->getDao()->getTable();

			if($from && $to && $from->toString() > $to->toString()) {
				$tmp = $to;
				$to = $from;
				$from = $tmp;
			}

			if($from) {
				$criteria->
				add(
					Expression::gtEq(
						new DBField($columnName, $tableName),
						new DBValue($from->toString())
					)
				);
			}

			if($to) {
				$criteria->
				add(
					Expression::ltEq(
						new DBField($columnName, $tableName),
						new DBValue($to->toString())
					)
				);
			}

			return $criteria;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == false
				&& $this->getParameterValue('readonly') == false
				&& $form->primitiveExists($this->property->getPropertyName())
			) {
				$setter = $this->property->setter();
				$dropper = $this->property->dropper();
				$value = $form->getValue($this->property->getPropertyName());
				if($value instanceof Time) {
					$this->element->$setter($value);
				} else {
					$this->element->$dropper();
				}
			}
		}

		public function hour()
		{
			return $this->value ? $this->doublize($this->value->getHour()) : null;
		}

		public function minute()
		{
			return $this->value ? $this->doublize($this->value->getMinute()) : null;
		}

		public function second()
		{
			return $this->value ? $this->doublize($this->value->getSecond()) : null;
		}

		public function time($delimeter = ':', $seconds = false)
		{
			return
				$this->value
				? (
					$this->hour()
					.$delimeter.$this->minute()
					.($seconds ? $delimeter.$this->second() : null)
				)
				: null;
		}

		public function getEditElementView()
		{
			$readonly = $this->getParameterValue('readonly');
			$fillToday = $this->getParameterValue('fillToday');

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('readonly', $readonly)->
				set('fillToday', $fillToday)->
				set('value', $this->value);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
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
				&& $form->getValue($propertyName.'_from')
				? $form->getValue($propertyName.'_from')
				: Time::create('00:00');

			$to =
				$form->primitiveExists($propertyName.'_to')
				&& $form->getValue($propertyName.'_to')
				? $form->getValue($propertyName.'_to')
				: Time::create('23:59');

			if($from->toString() > $to->toString()) {
				$tmp = $to;
				$to = $from;
				$from = $tmp;
			}

			$open = $from->toString() > '00:00' || $to->toString() < '23:59';

			$model =
				Model::create()->
				set('propertyName', $propertyName)->
				set('propertyDescription', $propertyDescription)->
				set('open', $open)->
				set('from', $from)->
				set('to', $to);

			$viewName = 'properties/'.get_class($this).'.search';

			return $this->render($model, $viewName);
		}

		private function doublize($int)
		{
			return sprintf('%02d', $int);
		}
	}
?>

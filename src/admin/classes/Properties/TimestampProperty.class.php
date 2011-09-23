<?php
	final class TimestampProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->value = $this->value ? $this->value : Timestamp::makeNow();
		}

		public function getDataType()
		{
			return DataType::create(DataType::TIMESTAMP);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Timestamp" required="false" />';
		}

		public function add2form(Form $form)
		{
			return $form->
				add(Primitive::timestamp($this->property->getPropertyName()));
		}

		public function add2search(Form $form)
		{
			return $form->
				add(Primitive::timestamp($this->property->getPropertyName().'_from'))->
				add(Primitive::timestamp($this->property->getPropertyName().'_to'));
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			return $criteria;

			$from = $form->getValue($this->property->getPropertyName().'_from');
			$to = $form->getValue($this->property->getPropertyName().'_to');

			$columnName = Property::getColumnName($this->property->getPropertyName());
			$tableName = $criteria->getDao()->getTable();

			if($from) {
				$criteria->
				add(
					Expression::gtEq(
						new DBField($columnName, $tableName),
						new DBValue($from->toString())
					)
				);
			}

			if($to && $to->toString() >= $from->toString()) {
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

		public function year()
		{
			return $this->value->getYear();
		}

		public function month()
		{
			return $this->doublize($this->value->getMonth());
		}

		public function day()
		{
			return (int)$this->value->getDay();
		}

		public function hour()
		{
			return $this->doublize($this->value->getHour());
		}

		public function minute()
		{
			return $this->doublize($this->value->getMinute());
		}

		public function second()
		{
			return $this->doublize($this->value->getSecond());
		}

		public function humanMonth()
		{
			return RussianTextUtils::getMonthInSubjectiveCase((integer)$this->value->getMonth());
		}

		public function humanMonth2()
		{
			return RussianTextUtils::getMonthInGenitiveCase((integer)$this->value->getMonth());
		}

		public function printf($format = 'Y-m-d H:i:s')
		{
			return date($format, $this->value->toStamp());
		}

		public function printOnElementSearch(Form $form)
		{
			$from =
				$form->primitiveExists($this->property->getPropertyName().'_from')
				? $form->getValue($this->property->getPropertyName().'_from')
				: Timestamp::create('2000-01-01 00:00:00');
			$to =
				$form->primitiveExists($this->property->getPropertyName().'_to')
				? $form->getValue($this->property->getPropertyName().'_to')
				: Timestamp::makeNow();

			$str = '';

			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_from" name="'.$this->property->getPropertyName().'_from" value="'.$from->toString().'">';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_to" name="'.$this->property->getPropertyName().'_to" value="'.$to->toString().'">';

			$str .= $this->property->getPropertyDescription().'';
			$str .= ' от <span id="'.$this->property->getPropertyName().'_from_show" class="dashed" style="cursor: pointer;">'.sprintf('%02d.%02d.%04d %02d:%02d', $from->getDay(), $from->getMonth(), $from->getYear(), $from->getHour(), $from->getMinute()).'</span>';
			$str .= ' до <span id="'.$this->property->getPropertyName().'_to_show" class="dashed" style="cursor: pointer;">'.sprintf('%02d.%02d.%04d %02d:%02d', $to->getDay(), $to->getMonth(), $to->getYear(), $to->getHour(), $to->getMinute()).'</span>';


			$str .= '<script type="text/javascript">';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'_from",';
			$str .= 'ifFormat: "%Y-%m-%d %H:%M:00",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_from_show",';
			$str .= 'daFormat: "%d.%m.%Y %H:%M",';
			$str .= 'align: "Tr",';
			$str .= 'weekNumbers: false,';
			$str .= 'showsTime: true,';
			$str .= 'singleClick: true';
			$str .= '});';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'_to",';
			$str .= 'ifFormat: "%Y-%m-%d %H:%M:00",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_to_show",';
			$str .= 'daFormat: "%d.%m.%Y %H:%M",';
			$str .= 'align: "Tr",';
			$str .= 'weekNumbers: false,';
			$str .= 'showsTime: true,';
			$str .= 'singleClick: true';
			$str .= '});';
			$str .= '</script>';

			return $str;
		}

		private function doublize($int)
		{
			return sprintf('%02d', $int);
		}
	}
?>

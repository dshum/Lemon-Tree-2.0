<?php
	final class TimestampProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::TIMESTAMP);

			$getter = $this->property->getter();
			$this->value = ($this->element && $this->element->$getter()) ? $this->element->$getter() : Timestamp::makeNow();
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

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':&nbsp;';
			$str .= '<span id="'.$this->property->getPropertyName().'_show" class="dashed" style="cursor: pointer;">'.sprintf('%d&nbsp;%s %04d года, %02d:%02d', $this->day(), $this->humanMonth2(), $this->year(), $this->hour(), $this->minute()).'</span>';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'" name="'.$this->property->getPropertyName().'" value="'.$this->value->toString().'">';
			$str .= '<script type="text/javascript">';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'",';
			$str .= 'ifFormat: "%Y-%m-%d %H:%M:%S",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_show",';
			$str .= 'daFormat: "%e %G %Y года, %H:%M",';
			$str .= 'align: "tR",';
			$str .= 'weekNumbers: false,';
			$str .= 'showsTime: true,';
			$str .= 'singleClick: true';
			$str .= '});';
			$str .= '</script>';
			$str .= '<br><br>';
			return $str;
		}

		public function printOnElementList()
		{
			if($this->year() == Date::makeToday()->getYear()) {
				$str = sprintf('%d&nbsp;%s<br>%d:%02d', $this->day(), $this->humanMonth2(), $this->hour(), $this->minute());
			} else {
				$str = sprintf('%d&nbsp;%s %04d года<br>%d:%02d', $this->day(), $this->humanMonth2(), $this->year(), $this->hour(), $this->minute());
			}
			return $str;
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

		public function editOnElementList()
		{

		}

		private function doublize($int)
		{
			return sprintf('%02d', $int);
		}
	}
?>

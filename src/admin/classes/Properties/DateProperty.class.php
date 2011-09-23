<?php
	final class DateProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::DATE);

			$getter = $this->property->getter();
			$this->value = ($this->element && $this->element->$getter()) ? $this->element->$getter() : Date::makeToday();
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="Date" required="false" />';
		}

		public function add2form(Form $form)
		{
			return $form->
				add(Primitive::date($this->property->getPropertyName()));
		}

		public function add2search(Form $form)
		{
			return $form->
				add(Primitive::date($this->property->getPropertyName().'_from'))->
				add(Primitive::date($this->property->getPropertyName().'_to'));
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

		public function humanMonth()
		{
			return RussianTextUtils::getMonthInSubjectiveCase($this->value->getMonth());
		}

		public function humanMonth2()
		{
			return RussianTextUtils::getMonthInGenitiveCase($this->value->getMonth());
		}

		public function printf($format = 'Y-m-d')
		{
			return date($format, $this->value->toStamp());
		}

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':&nbsp;';
			$str .= '<span id="'.$this->property->getPropertyName().'_show" class="dashed" style="cursor: pointer;">'.sprintf('%d&nbsp;%s %04d года', $this->day(), $this->humanMonth2(), $this->year()).'</span>';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'" name="'.$this->property->getPropertyName().'" value="'.$this->value->toString().'">';
			$str .= '<script type="text/javascript">';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'",';
			$str .= 'ifFormat: "%Y-%m-%d",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_show",';
			$str .= 'daFormat: "%e %G %Y года",';
			$str .= 'align: "tR",';
			$str .= 'weekNumbers: false,';
			$str .= 'singleClick: true';
			$str .= '});';
			$str .= '</script>';
			$str .= '<br><br>';
			return $str;
		}

		public function printOnElementList()
		{
			if($this->year() == Date::makeToday()->getYear()) {
				$str = sprintf('%d&nbsp;%s', $this->day(), $this->humanMonth2());
			} else {
				$str = sprintf('%d&nbsp;%s %04d года', $this->day(), $this->humanMonth2(), $this->year());
			}
			return $str;
		}

		public function printOnElementSearch(Form $form)
		{
			$from =
				$form->primitiveExists($this->property->getPropertyName().'_from')
				? $form->getValue($this->property->getPropertyName().'_from')
				: Date::create('2000-01-01');
			$to =
				$form->primitiveExists($this->property->getPropertyName().'_to')
				? $form->getValue($this->property->getPropertyName().'_to')
				: Date::makeToday();

			$str = '';

			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_from" name="'.$this->property->getPropertyName().'_from" value="'.$from->toString().'">';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_to" name="'.$this->property->getPropertyName().'_to" value="'.$to->toString().'">';

			$str .= $this->property->getPropertyDescription().'';
			$str .= ' от <span id="'.$this->property->getPropertyName().'_from_show" class="dashed" style="cursor: pointer;">'.sprintf('%02d.%02d.%04d', $from->getDay(), $from->getMonth(), $from->getYear()).'</span>';
			$str .= ' до <span id="'.$this->property->getPropertyName().'_to_show" class="dashed" style="cursor: pointer;">'.sprintf('%02d.%02d.%04d', $to->getDay(), $to->getMonth(), $to->getYear()).'</span>';


			$str .= '<script type="text/javascript">';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'_from",';
			$str .= 'ifFormat: "%Y-%m-%d",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_from_show",';
			$str .= 'daFormat: "%d.%m.%Y",';
			$str .= 'align: "Tr",';
			$str .= 'weekNumbers: false,';
			$str .= 'singleClick: true';
			$str .= '});';
			$str .= 'Calendar.setup({';
			$str .= 'inputField: "'.$this->property->getPropertyName().'_to",';
			$str .= 'ifFormat: "%Y-%m-%d",';
			$str .= 'displayArea: "'.$this->property->getPropertyName().'_to_show",';
			$str .= 'daFormat: "%d.%m.%Y",';
			$str .= 'align: "Tr",';
			$str .= 'weekNumbers: false,';
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
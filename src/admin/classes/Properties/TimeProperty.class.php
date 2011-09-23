<?php
	final class TimeProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::TIME);

			$getter = $this->property->getter();
			$this->value = ($this->element && $this->element->$getter()) ? $this->element->$getter() : Time::create(date('H:i:s'));
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

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':&nbsp; ';
			$str .= '<script type="text/javascript">';
			$str .= '$(function() {';
			$str .= '$(\'input[name^=$this->property->getPropertyName()]\').change(function() {';
			$str .= '$(\'input[name='.$this->property->getPropertyName().']\').val(';
			$str .= '$(\'input[name='.$this->property->getPropertyName().'_hour]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_minute]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_second]\').val()';
			$str .= ');';
			$str .= '}).keyup(function() {';
			$str .= '$(\'input[name='.$this->property->getPropertyName().']\').val(';
			$str .= '$(\'input[name='.$this->property->getPropertyName().'_hour]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_minute]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_second]\').val()';
			$str .= ');';
			$str .= '});';
			$str .= '});';
			$str .= '</script>';
			$str .= '<input type="hidden" name="'.$this->property->getPropertyName().'" value="'.$this->value->toString().'">';
			$str .= '<input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_hour" value="'.$this->hour().'" maxlength="2">&nbsp;:&nbsp;';
			$str .= '<input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_minute" value="'.$this->minute().'" maxlength="2">&nbsp;:&nbsp;';
			$str .= '<input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_second" value="'.$this->second().'" maxlength="2">';
			$str .= '<br><br>';

			return $str;
		}

		public function printOnElementList()
		{
			$str = $this->value->toString();

			return $str;
		}

		public function printOnElementSearch(Form $form)
		{
			$from =
				$form->primitiveExists($this->property->getPropertyName().'_from')
				? $form->getValue($this->property->getPropertyName().'_from')
				: Time::create('00:00');
			$to =
				$form->primitiveExists($this->property->getPropertyName().'_to')
				? $form->getValue($this->property->getPropertyName().'_to')
				: Time::create('23:59');

			$str = '';

			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_from" name="'.$this->property->getPropertyName().'_from" value="'.$from->toString().'">';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_to" name="'.$this->property->getPropertyName().'_to" value="'.$to->toString().'">';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_from_second" name="'.$this->property->getPropertyName().'_from_second" value="'.$this->doublize($from->getSecond()).'">';
			$str .= '<input type="hidden" id="'.$this->property->getPropertyName().'_to_second" name="'.$this->property->getPropertyName().'_to_second" value="'.$this->doublize($to->getSecond()).'">';

			$str .= $this->property->getPropertyDescription().'';
			$str .= ' от <input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_from_hour" value="'.$this->doublize($from->getHour()).'" maxlength="2" onchange="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_from\')" onkeyup="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_from\')">&nbsp;:&nbsp;';
			$str .= '<input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_from_minute" value="'.$this->doublize($from->getMinute()).'" maxlength="2" onchange="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_from\')" onkeyup="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_from\')">';
			$str .= ' до <input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_to_hour" value="'.$this->doublize($to->getHour()).'" maxlength="2" onchange="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_to\')" onkeyup="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_to\')">&nbsp;:&nbsp;';
			$str .= '<input class="prop-time" type="text" name="'.$this->property->getPropertyName().'_to_minute" value="'.$this->doublize($to->getMinute()).'" maxlength="2" onchange="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_to\')" onkeyup="oElement.setTime(this.form, \''.$this->property->getPropertyName().'_to\')">';

			$str .= '<script type="text/javascript">';
			$str .= '$(function() {';
			$str .= '$(\'input[name^=$this->property->getPropertyName()]\').change(function() {';
			$str .= '$(\'input[name='.$this->property->getPropertyName().']\').val(';
			$str .= '$(\'input[name='.$this->property->getPropertyName().'_hour]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_minute]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_second]\').val()';
			$str .= ');';
			$str .= '}).keyup(function() {';
			$str .= '$(\'input[name='.$this->property->getPropertyName().']\').val(';
			$str .= '$(\'input[name='.$this->property->getPropertyName().'_hour]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_minute]\').val()+\':\'';
			$str .= '+$(\'input[name='.$this->property->getPropertyName().'_second]\').val()';
			$str .= ');';
			$str .= '});';
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

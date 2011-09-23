<?php
	final class BooleanParameter extends BaseParameter
	{
		public function primitive()
		{
			return Primitive::boolean($this->name);
		}

		public function toRaw($value)
		{
			return $value ? 1 : 0;
		}

		public function printOnEdit()
		{
			$str = '<input type="checkbox" name="'.$this->name.'" value="1"'.($this->value ? ' checked' : '').'>';

			return $str;
		}

		public function setValue($raw)
		{
			$this->value = $raw == true;

			return $this;
		}
	}
?>
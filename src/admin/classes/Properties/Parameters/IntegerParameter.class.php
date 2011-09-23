<?php
	final class IntegerParameter extends BaseParameter
	{
		public function primitive()
		{
			return Primitive::integer($this->name);
		}

		public function printOnEdit()
		{
			$str = '<input class="item-name" type="text" name="'.$this->name.'" value="'.$this->value.'" style="width: 75px;">';

			return $str;
		}

		public function setValue($raw)
		{
			$this->value = (int)$raw;

			return $this;
		}
	}
?>
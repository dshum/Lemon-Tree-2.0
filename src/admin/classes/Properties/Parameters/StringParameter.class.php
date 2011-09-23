<?php
	final class StringParameter extends BaseParameter
	{
		public function printOnEdit()
		{
			$str = '<input class="item-name" type="text" name="'.$this->name.'" value="'.$this->value.'">';

			return $str;
		}

		public function setValue($raw)
		{
			$this->value = $raw;

			return $this;
		}
	}
?>
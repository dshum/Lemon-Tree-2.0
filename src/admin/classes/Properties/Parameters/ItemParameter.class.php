<?php
	final class ItemParameter extends BaseParameter
	{
		public function primitive()
		{
			return Primitive::identifier($this->name)->of('Item');
		}

		public function toRaw(Item $item)
		{
			return $item->getId();
		}

		public function printOnEdit()
		{
			$itemList = Item::dao()->getDefaultItemList();

			$str = '<select name="'.$this->name.'">';
			foreach($itemList as $item) {
				$str .= '<option value="'.$item->getId().'">'.$item->getItemDescription().'</option>';
			}
			$str .= '</select>';

			return $str;
		}

		public function setValue($raw)
		{
			try {
				$this->value = Item::dao()->getById($raw);
			} catch (ObjectNotFoundException $e) {
				$this->value = $this->default;
			}

			return $this;
		}
	}
?>
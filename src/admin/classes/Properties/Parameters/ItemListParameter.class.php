<?php
	final class ItemListParameter extends BaseParameter
	{
		public function primitive()
		{
			return Primitive::identifierlist($this->name)->of('Item');
		}

		public function toRaw($itemList)
		{
			$ids = array();
			foreach($itemList as $item) {
				$ids[] = $item->getId();
			}

			return implode(',', $ids);
		}

		public function printOnEdit()
		{
			$itemList = Item::dao()->getDefaultItemList();

			$str = '<div style="height: 105px; overflow-y: scroll; border: 1px solid #CCC;">';
			foreach($itemList as $item) {
				$checked = in_array($item, $this->value) ? ' checked' : '';
				$str .= '<input type="checkbox" id="'.$this->name.'_'.$item->getId().'" name="'.$this->name.'['.$item->getId().']" value="'.$item->getId().'"'.$checked.'>&nbsp;<label for="'.$this->name.'_'.$item->getId().'">'.$item->getItemDescription().'</label><br>';
			}
			$str .= '</div>';

			return $str;
		}

		public function setValue($raw)
		{
			$this->value = array();

			$ids = explode(',', $raw);

			foreach($ids as $id) {
				try {
					$this->value[] = Item::dao()->getById($id);
				} catch (ObjectNotFoundException $e) {}
			}

			return $this;
		}
	}
?>
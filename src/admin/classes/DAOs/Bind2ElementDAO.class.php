<?php
	final class Bind2ElementDAO extends AutoBind2ElementDAO
	{
		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function dropByItem(Item $item)
		{
			$db = DBPool::me()->getByDao($this);

			$query =
				OSQL::delete()->
				from($this->getTable())->
				where(
					Expression::eq(
						new DBField('bind_item_id', $this->getTable()),
						new DBValue($item->getId())
					)
				);

			try {
				$db->query($query);
				$this->uncacheLists();
			} catch (DatabaseException $e) {}
		}

		public function dropByElement(Element $element)
		{
			$db = DBPool::me()->getByDao($this);

			$query =
				OSQL::delete()->
				from($this->getTable())->
				where(
					Expression::eq(
						new DBField('element_id', $this->getTable()),
						new DBValue($element->getPolymorphicId())
					)
				);

			try {
				$db->query($query);
				$this->uncacheLists();
			} catch (DatabaseException $e) {}
		}
	}
?>
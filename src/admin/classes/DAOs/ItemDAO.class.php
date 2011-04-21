<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-0.10.8 at 2008-01-19 16:07:06                        *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/
/* $Id$ */

	final class ItemDAO extends AutoItemDAO
	{
		private $itemList = array();
		private $itemMap = array();

		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function setItemList()
		{
			$itemList =
				Criteria::create(Item::dao())->
				addOrder(new DBField('item_order', Item::dao()->getTable()))->
				getList();

			foreach($itemList as $item) {
				$this->itemList[$item->getId()] = $item;
				$this->itemMap[$item->getItemName()] = $item;
			}
		}

		public function setSimpleItemList()
		{
			$query =
				OSQL::select()->
				get(new DBField('id', $this->getTable()))->
				get(new DBField('item_name', $this->getTable()))->
				get(new DBField('class_type', $this->getTable()))->
				get(new DBField('parent_class', $this->getTable()))->
				get(new DBField('path_prefix', $this->getTable()))->
				from($this->getTable())->
				orderBy(new DBField('item_order', $this->getTable()));

			try {
				$itemList = $this->getCustomList($query);

				foreach($itemList as $custom) {
					$item =
						Item::create()->
						setId($custom['id'])->
						setItemName($custom['item_name'])->
						setClassType($custom['class_type'])->
						setParentClass($custom['parent_class'])->
						setPathPrefix($custom['path_prefix']);

					$this->itemList[$item->getId()] = $item;
					$this->itemMap[$item->getItemName()] = $item;
				}
			} catch (ObjectNotFoundException $e) {}
		}

		public function getItemList()
		{
			$itemList = array();
			foreach($this->itemList as $item) {
				$itemList[] = $item;
			}

			return $itemList;
		}

		public function getDefaultItemList()
		{
			$itemList = $this->getItemList();

			$defaultItemList = array();
			foreach($itemList as $item) {
				if($item->isDefault()) {
					$defaultItemList[] = $item;
				}
			}

			return $defaultItemList;
		}

		public function getExtendableItemList(Item $currentItem)
		{
			$itemList = $this->getItemList();

			$extendableItemList = array();
			if(!$currentItem) {
				foreach($itemList as $item) {
					if(!sizeof(Property::dao()->getPropertyList($item))) {
						$extendableItemList[] = $item;
					}
				}
			} else {
				foreach($itemList as $item) {
					if($currentItem->isExtendable($item)) {
						$extendableItemList[] = $item;
					}
				}
			}

			return $extendableItemList;
		}

		public function getItemById($itemId)
		{
			if(isset($this->itemList[$itemId])) {
				return $this->itemList[$itemId];
			} else {
				throw new ObjectNotFoundException('Item with id '.$itemId.' is not found.');
			}
		}

		public function getItemByName($itemName)
		{
			if(isset($this->itemMap[$itemName])) {
				return $this->itemMap[$itemName];
			} else {
				throw new ObjectNotFoundException('Item with name '.$itemName.' is not found.');
			}
		}

		public function addItem(Item $newItem)
		{
			$db = DBPool::me()->getByDao(Item::dao());

			# Create table
			if($newItem->isDefault()) {

				$tableName = $newItem->getDefaultTableName();

				$table =
					DBTable::create($tableName)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::INTEGER)->setNull(false),
							'id'
						)->
						setPrimaryKey(true)->setAutoincrement(true)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::VARCHAR)->setNull(false)->setSize(255),
							'element_name'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::INTEGER),
							'element_order'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::VARCHAR)->setNull(false)->setSize(50),
							'status'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::VARCHAR)->setNull(false)->setSize(255),
							'element_path'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::INTEGER),
							'group_id'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::INTEGER),
							'user_id'
						)
					);
				$query = OSQL::createTable($table);
				$db->query($query);

				# Indexes
				$query = 'CREATE UNIQUE INDEX path ON '.$tableName.' (status, element_path)';
				$db->queryRaw($query);
			}

			# Set item order
			$itemList = Item::dao()->getItemList();
			$nextItemOrder = 0;
			foreach($itemList as $item) {
				if($nextItemOrder < (integer)$item->getItemOrder()) {
					$nextItemOrder = $item->getItemOrder();
				}
			}
			$nextItemOrder++;
			$newItem->setItemOrder($nextItemOrder);

			# Add item
			$newItem = Item::dao()->add($newItem);

			$this->itemList[$newItem->getId()] = $newItem;
			$this->itemMap[$newItem->getItemName()] = $newItem;

			return $newItem;
		}

		public function saveItem(Item $item)
		{
			# Save item
			$item = Item::dao()->save($item);

			$this->itemList[$item->getId()] = $item;
			$this->itemMap[$item->getItemName()] = $item;

			return $item;
		}

		public function dropItem(Item $item)
		{
			if(isset($this->itemList[$item->getId()])) {

				if($item->isDefault()) {
					$itemClass = $item->getClass();

					# Drop table
					if($itemClass) {
						$db = DBPool::me()->getByDao($itemClass->dao());
						$dialect = $db->getDialect();
						$tableName = $itemClass->dao()->getTable();
						$query = 'DROP TABLE '.$dialect->quoteTable($tableName);
						try {
							$db->queryRaw($query);
						} catch (DatabaseException $e) {}
					}

					# Drop properties
					Property::dao()->dropByItem($item);

					# Drop binds
					Bind2Item::dao()->dropByItem($item);
					Bind2Item::dao()->dropByBindItem($item);
					Bind2Element::dao()->dropByItem($item);

					# Drop item
					Item::dao()->drop($item);

					if(isset($this->itemList[$item->getId()])) {
						unset($this->itemList[$item->getId()]);
					}
					if(isset($this->itemMap[$item->getItemName()])) {
						unset($this->itemMap[$item->getItemName()]);
					}

					# Drop auto files
					Site::dropAuto($item);

					# Drop data folder
					// ...todo
				}
			}
		}
	}
?>
<?php
/*****************************************************************************
 *   Copyright (C) 2006-2007, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-0.10.8 at 2008-01-19 16:07:06                        *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/
/* $Id$ */

	final class ItemDAO extends AutoItemDAO
	{
		private static $itemList = array();
		private static $itemReferenceList = array();

		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public static function setItemList()
		{
			$itemList =
				Criteria::create(Item::dao())->
				addOrder(new DBField("item_order", Item::dao()->getTable()))->
				addOrder(new DBField("id", Item::dao()->getTable()))->
				getList();

			foreach($itemList as $item) {
				self::$itemList[$item->getId()] = $item;
				self::$itemReferenceList[$item->getItemName()] = $item;
			}
		}

		public static function getItemList()
		{
			$itemList = array();
			foreach(self::$itemList as $item) {
				$itemList[] = $item;
			}

			return $itemList;
		}

		public function getDefaultItemList()
		{
			$itemList = self::getItemList();

			$defaultItemList = array();
			foreach($itemList as $item) {
				if(!in_array($item->getClassType(), array('abstract', 'virtual'))) {
					$defaultItemList[] = $item;
				}
			}

			return $defaultItemList;
		}

		public function getDAOConnectedItemList()
		{
			$itemList = self::getItemList();

			$DAOConnectedItemList = array();
			foreach($itemList as $item) {
				$itemClass = $item->getClass();
				if($itemClass instanceof DAOConnected) {
					$DAOConnectedItemList[] = $item;
				}
			}

			return $DAOConnectedItemList;
		}

		public function getExtendableItemList(Item $currentItem)
		{
			$itemList = self::getItemList();

			$extendableItemList = array();
			if(!$currentItem) {
				foreach($itemList as $item) {
					if(
						$item->getClassType() != 'final'
						&& !sizeof(Property::dao()->getPropertyList($item))
					) {
						$extendableItemList[] = $item;
					}
				}
			} else {
				foreach($itemList as $item) {
					if(
						$item->getClassType() != 'final'
						&& $currentItem->isExtendable($item)
					) {
						$extendableItemList[] = $item;
					}
				}
			}

			return $extendableItemList;
		}

		public function getItemById($itemId)
		{
			return Item::dao()->getById($itemId);
		}

		public function getItemByName($itemName)
		{
			if(isset(self::$itemReferenceList[$itemName])) {
				return self::$itemReferenceList[$itemName];
			} else {
				throw new ObjectNotFoundException('Item with name '.$itemName.' is not found.');
			}
		}

		public function addItem(Item $newItem)
		{
			$db = DBPool::me()->getByDao(Item::dao());

			# Create table
			if($newItem->getClassType() == 'default') {

				$tableName =
					$newItem->getTableName()
					? $newItem->getTableName()
					: $newItem->getDefaultTableName();

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
							DataType::create(DataType::VARCHAR)->setNull(false)->setSize(255),
							'short_name'
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
							DataType::create(DataType::VARCHAR)->setSize(50),
							'controller_name'
						)
					)->
					addColumn(
						DBColumn::create(
							DataType::create(DataType::VARCHAR)->setSize(50),
							'plugin_name'
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

			self::$itemList[$item->getId()] = $newItem;
			self::$itemReferenceList[$item->getItemName()] = $newItem;

			return $newItem;
		}

		public static function saveItem(Item $item)
		{
			# Save item
			$item = Item::dao()->save($item);

			self::$itemList[$item->getId()] = $item;
			self::$itemReferenceList[$item->getItemName()] = $item;

			return $item;
		}

		public static function dropItem(Item $item)
		{
			$itemId = $item->getId();
			$itemName = $item->getItemName();

			if(isset(self::$itemList[$itemId])) {

				# Drop table
				if($item->getClassType() == 'default') {
					$itemClass = $item->getClass();
					$linkName = $itemClass->dao()->getLinkName();
					$db = DBPool::me()->getLink($linkName);
					$dialect = $db->getDialect();

					$query =
						'DROP TABLE '
						.$dialect->quoteTable($itemClass->dao()->getTable())
						.'';

					try {

						$db->queryRaw($query);

						# Drop properties
						Property::dao()->dropPropertyList($item);

						# Drop binds
						Bind2Item::dao()->dropByItem($item);

						# Drop item
						Item::dao()->dropById($itemId);

						unset(self::$itemList[$itemId]);
						unset(self::$itemReferenceList[$itemName]);

						# Drop auto files
						Site::dropAuto($item);

						# Drop data folder
						// ...todo

					} catch (DatabaseException $e) {}
				}
			}
		}
	}
?>
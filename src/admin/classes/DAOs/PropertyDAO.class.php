<?php
	final class PropertyDAO extends AutoPropertyDAO
	{
		private $propertyList = array();
		private $propertyMap = array();

		public function getTable()
		{
			return TABLE_PREFIX.parent::getTable();
		}

		public function getSequence()
		{
			return TABLE_PREFIX.parent::getSequence();
		}

		public function setPropertyList()
		{
			$propertyList =
				Criteria::create($this)->
				addOrder(new DBField('property_order', $this->getTable()))->
				addOrder(new DBField('id', $this->getTable()))->
				getList();

			foreach($propertyList as $property) {
				$this->propertyList[$property->getItem()->getId()][$property->getId()] = $property;
				$this->propertyMap[$property->getItem()->getId()][$property->getPropertyName()] = $property;
			}
		}

		public function setSimplePropertyList()
		{
			$query =
				OSQL::select()->
				get(new DBField('id', $this->getTable()))->
				get(new DBField('item_id', $this->getTable()))->
				get(new DBField('property_name', $this->getTable()))->
				get(new DBField('property_class', $this->getTable()))->
				get(new DBField('fetch_class', $this->getTable()))->
				get(new DBField('is_parent', $this->getTable()))->
				from($this->getTable())->
				orderBy(new DBField('property_order', $this->getTable()));

			try {
				$propertyList = $this->getCustomList($query);

				foreach($propertyList as $custom) {
					$item = Item::dao()->getItemById($custom['item_id']);

					$property =
						Property::create()->
						setId($custom['id'])->
						setItem($item)->
						setPropertyName($custom['property_name'])->
						setPropertyClass($custom['property_class'])->
						setFetchClass($custom['fetch_class'])->
						setIsParent((boolean)$custom['is_parent']);

					$this->propertyList[$item->getId()][$property->getId()] = $property;
					$this->propertyMap[$item->getId()][$property->getPropertyName()] = $property;
				}
			} catch (ObjectNotFoundException $e) {}
		}

		public function getPropertyList(Item $item)
		{
			$propertyList = array();

			if(isset($this->propertyList[$item->getId()])) {

				foreach($this->propertyList[$item->getId()] as $property) {
					$propertyList[] = $property;
				}

			} else {

				$propertyList =
					Criteria::create($this)->
					add(
						Expression::eqId(
							new DBField('item_id', $this->getTable()),
							$item
						)
					)->
					addOrder(new DBField('property_order', $this->getTable()))->
					addOrder(new DBField('id', $this->getTable()))->
					getList();

				foreach($propertyList as $property) {
					$this->propertyList[$property->getItem()->getId()][$property->getId()] = $property;
					$this->propertyMap[$property->getItem()->getId()][$property->getPropertyName()] = $property;
				}
			}

			return $propertyList;
		}

		public function getParentPropertyList(Item $item)
		{
			$parentPropertyList = array();

			$propertyList = Property::dao()->getPropertyList($item);

			foreach($propertyList as $property) {
				if($property->getPropertyClass() == 'OneToOneProperty') {
					$parentPropertyList[] = $property;
				}
			}

			return $parentPropertyList;
		}

		public function dropPropertyList(Item $item)
		{
			if(isset($this->propertyList[$item->getId()])) {
				foreach($this->propertyList[$item->getId()] as $property) {
					$this->dropProperty($property);
				}
				unset($this->propertyList[$item->getId()]);
				unset($this->propertyMap[$item->getId()]);
			}
		}

		public function dropByItem(Item $item)
		{
			$db = DBPool::me()->getByDao($this);

			$query =
				OSQL::delete()->
				from($this->getTable())->
				where(
					Expression::eq(
						new DBField('item_id', $this->getTable()),
						new DBValue($item->getId())
					)
				);

			try {
				$db->query($query);

				$this->uncacheLists();

				if(isset($this->propertyList[$item->getId()])) {
					unset($this->propertyList[$item->getId()]);
				}
				if(isset($this->propertyMap[$item->getId()])) {
					unset($this->propertyMap[$item->getId()]);
				}
			} catch (DatabaseException $e) {}
		}

		public function dropByFetchClass(Item $item)
		{
			$db = DBPool::me()->getByDao($this);

			$query =
				OSQL::delete()->
				from($this->getTable())->
				where(
					Expression::eq(
						new DBField('fetch_class', $this->getTable()),
						new DBValue($item->getItemName())
					)
				);

			try {
				$db->query($query);

				$this->uncacheLists();

				if(isset($this->propertyList[$item->getId()])) {
					unset($this->propertyList[$item->getId()]);
				}
				if(isset($this->propertyMap[$item->getId()])) {
					unset($this->propertyMap[$item->getId()]);
				}
			} catch (DatabaseException $e) {}
		}

		public function getPropertyByName(Item $item, $propertyName)
		{
			if(isset($this->propertyMap[$item->getId()][$propertyName])) {
				return $this->propertyMap[$item->getId()][$propertyName];
			} else {
				throw new ObjectNotFoundException('Property with name '.$propertyName.' is not found.');
			}
		}

		public function addProperty(Property $property)
		{
			$item = $property->getItem();
			$itemClass = $item->getClass();

			if(
				$item->isDefault()
				&& $property->getPropertyClass() != 'VirtualProperty'
				&& $property->getPropertyClass() != 'OneToManyProperty'
				&& $property->getPropertyClass() != 'ManyToManyProperty'
			) {
				# Add table column
				$propertyClass = $property->getClass(null);

				$db = DBPool::me()->getByDao($itemClass->dao());
				$dialect = $db->getDialect();

				$tableName = $itemClass->dao()->getTable();
				$columnName = $propertyClass->column()->toDialectString($dialect);

				$query =
					'ALTER TABLE '.$dialect->quoteTable($tableName)
					.' ADD COLUMN '.$columnName.'';

				try {
					$db->queryRaw($query);
				} catch (DatabaseException $e) {}
			}

			if(
				$item->isDefault()
				&& $property->getPropertyClass() == 'ManyToManyProperty'
			) {
				# Add helper table for N:N relation
				$db = DBPool::me()->getByDao($itemClass->dao());

				$propertyClass = $property->getClass(null);

				$tableName =
					$itemClass->dao()->getTable()
					.'_'.$propertyClass->getColumnName()
					.'_helper';

				$parentIdField = $item->getColumnName().'_id';

				$childIdField = $propertyClass->getColumnName().'_id';

				$query =
					OSQL::createTable(
						DBTable::create($tableName)->
						addColumn(
							DBColumn::create(
								DataType::create(DataType::INTEGER),
								$parentIdField
							)
						)->
						addColumn(
							DBColumn::create(
								DataType::create(DataType::INTEGER),
								$childIdField
							)
						)
					);

				try {
					$db->query($query);
				} catch (DatabaseException $e) {}

				$query =
					'CREATE UNIQUE INDEX pair ON '.$tableName
					.' ('.$parentIdField.', '.$childIdField.')';

				try {
					$db->queryRaw($query);
				} catch (DatabaseException $e) {}
			}

			# Property order
			$nextPropertyOrder = 0;
			$propertyList = $this->getPropertyList($item);
			foreach($propertyList as $prop) {
				if($nextPropertyOrder < $prop->getPropertyOrder()) {
					$nextPropertyOrder = $prop->getPropertyOrder();
				}
			}
			$nextPropertyOrder++;
			$property->setPropertyOrder($nextPropertyOrder);

			# Add property
			$property = $this->add($property);

			$this->propertyList[$item->getId()][$property->getId()] = $property;
			$this->propertyMap[$item->getId()][$property->getPropertyName()] = $property;

			return $property;
		}

		public function saveProperty(Property $property)
		{
			$oldProperty = $this->getById($property->getId());

			$item = $property->getItem();
			$itemClass = $item->getClass();

			if(
				$item->isDefault()
				&& $property->getPropertyClass() != 'VirtualProperty'
				&& $property->getPropertyClass() != 'OneToManyProperty'
				&& $property->getPropertyClass() != 'ManyToManyProperty'
				&& (
					$oldProperty->getPropertyName() != $property->getPropertyName()
					|| $oldProperty->getPropertyClass() != $property->getPropertyClass()
					|| $oldProperty->getIsRequired() != $property->getIsRequired()
				)
			) {
				# Save table column
				$propertyClass = $property->getClass(null);
				$oldPropertyClass = $oldProperty->getClass(null);

				$db = DBPool::me()->getByDao($itemClass->dao());
				$dialect = $db->getDialect();

				$tableName = $itemClass->dao()->getTable();
				$oldColumName = $oldPropertyClass->getColumnName();
				$columnName = $propertyClass->column()->toDialectString($dialect);

				$query =
					'ALTER TABLE '.$dialect->quoteTable($tableName)
					.' CHANGE COLUMN '.$dialect->quoteField($oldColumName)
					.' '.$columnName.'';

				try {
					$db->queryRaw($query);
				} catch (DatabaseException $e) {}
			}

			# Save property
			$property = $this->save($property);

			$this->propertyList[$item->getId()][$property->getId()] = $property;
			$this->propertyMap[$item->getId()][$property->getPropertyName()] = $property;

			return $property;
		}

		public function dropProperty(Property $property)
		{
			$item = $property->getItem();
			$itemClass = $item->getClass();

			if(
				$item->isDefault()
				&& $property->getPropertyClass() != 'VirtualProperty'
				&& $property->getPropertyClass() != 'OneToManyProperty'
				&& $property->getPropertyClass() != 'ManyToManyProperty'
			) {

				# Drop table column

				$db = DBPool::me()->getByDao($itemClass->dao());
				$dialect = $db->getDialect();

				$propertyClass = $property->getClass(null);

				$tableName = $itemClass->dao()->getTable();
				$columnName = $propertyClass->getColumnName();

				$query =
					'ALTER TABLE '.$dialect->quoteTable($tableName)
					.' DROP COLUMN '.$dialect->quoteField($columnName);

				try {

					$db->queryRaw($query);

					$this->drop($property);

					if(isset($this->propertyList[$item->getId()][$property->getId()])) {
						unset($this->propertyList[$item->getId()][$property->getId()]);
					}
					if(isset($this->propertyMap[$item->getId()][$property->getPropertyName()])) {
						unset($this->propertyMap[$item->getId()][$property->getPropertyName()]);
					}

				} catch (DatabaseException $e) {
					echo ErrorMessageUtils::printMessage($e);
				}

			} elseif(
				!$item->isDefault()
				|| $property->getPropertyClass() == 'VirtualProperty'
			) {

				$this->drop($property);

				if(isset($this->propertyList[$item->getId()][$property->getId()])) {
					unset($this->propertyList[$item->getId()][$property->getId()]);
				}
				if(isset($this->propertyMap[$item->getId()][$property->getPropertyName()])) {
					unset($this->propertyMap[$item->getId()][$property->getPropertyName()]);
				}

			}

			if(
				$item->isDefault()
				&& $property->getPropertyClass() == 'ManyToManyProperty'
			) {

				# Drop helper table for N:N relation

				$db = DBPool::me()->getByDao($itemClass->dao());
				$dialect = $db->getDialect();

				$propertyClass = $property->getClass(null);

				$tableName =
					$itemClass->dao()->getTable()
					.'_'.$propertyClass->getColumnName()
					.'_helper';

				$query = 'DROP TABLE '.$dialect->quoteTable($tableName);

				try {
					$db->queryRaw($query);
				} catch (DatabaseException $e) {}
			}
		}
	}
?>
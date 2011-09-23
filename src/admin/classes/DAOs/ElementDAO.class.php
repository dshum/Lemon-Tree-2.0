<?php
	abstract class ElementDAO extends AutoElementDAO
	{
		const MAX_NUMBER_OF_DROP_ELEMENTS = 100;
		const ERROR_TOO_MANY_ELEMENTS = 1;

		public function getByElementPath($elementPath)
		{
			return
				Criteria::create($this)->
				add(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue('root')
					)
				)->
				add(
					Expression::eq(
						new DBField('element_path', $this->getTable()),
						new DBValue($elementPath)
					)
				)->
				setLimit(1)->
				get();
		}

		public function getCachedObjectByElementPath($elementPath)
		{
			try {
				$id = $this->getIdByElementPath($elementPath);
				$object = $this->getById($id);
			} catch (ObjectNotFoundException $e) {
				$object = null;
			}

			return $object;
		}

		public function getIdByElementPath($elementPath)
		{
			$query =
				OSQL::select()->
				get( new DBField('id', $this->getTable()))->
				from($this->getTable())->
				where(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue('root')
					)
				)->
				andWhere(
					Expression::eq(
						new DBField('element_path', $this->getTable()),
						new DBValue($elementPath)
					)
				)->
				limit(1);

			$custom = $this->getCustom($query);

			return $custom['id'];
		}

		public function getValid()
		{
			$criteria =
				Criteria::create($this)->
				add(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue('root')
					)
				);

			return $criteria;
		}

		public function getByStatus($status = 'root')
		{
			$criteria =
				Criteria::create($this)->
				add(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue($status)
					)
				);

			return $criteria;
		}

		public function getChildren(Element $element)
		{
			$item = Item::dao()->getItemByName($this->getObjectName());
			$propertyList = Property::dao()->getPropertyList($item);

			$chain = new LogicalChain();

			foreach($propertyList as $property) {
				if(
					$property->getPropertyClass() == 'OneToOneProperty'
					&& $property->getFetchClass() == $element->getClass()
				) {
					$propertyClass = $property->getClass(null);
					$chain->expOr(
						Expression::eq(
							new DBField($propertyClass->getColumnName(), $this->getTable()),
							new DBValue($element->getId())
						)
					);
				}
			}

			if(!$chain->getSize()) {
				$chain->expAnd(Expression::isTrue(false));
			}

			$criteria =
				Criteria::create($this)->
				add(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue($element->getStatus())
					)
				)->
				add($chain);

			return $criteria;
		}

		public function getChildrenByProperty(Element $element, Property $property)
		{
			$propertyClass = $property->getClass(null);

			$criteria =
				Criteria::create($this)->
				add(
					Expression::eq(
						new DBField('status', $this->getTable()),
						new DBValue($element->getStatus())
					)
				)->
				add(
					Expression::eq(
						new DBField($propertyClass->getColumnName(), $this->getTable()),
						new DBValue($element->getId())
					)
				);

			return $criteria;
		}

		public function getChildrenCount(Element $element)
		{
			$projection = new ProjectionChain();
			$projection->add(Projection::count('id', 'count'));

			$criteria =
				$this->getChildren($element)->
				setProjection($projection);

			return $criteria->getCustom('count');
		}

		public function getChildrenCountByProperty(Element $element, Property $property)
		{
			$projection = new ProjectionChain();
			$projection->add(Projection::count('id', 'count'));

			$criteria =
				$this->getChildrenByProperty($element, $property)->
				setProjection($projection);

			return $criteria->getCustom('count');
		}

		public function getOffspringsCount(Element $element)
		{
			$query =
				OSQL::select()->
				get(
					new SQLFunction('COUNT', 'id'),
					'count'
				)->
				from(
					$this->getTable()
				)->
				where(
					Expression::andBlock(
						Expression::eq(
							new SQLFunction(
								'SUBSTRING',
								new DBField('element_path'),
								new DBValue(1),
								new DBValue(strlen($element->getElementPath().'/'))
							),
							new DBValue($element->getElementPath().'/')
						),
						Expression::eq(
							new DBField('status'),
							new DBValue($element->getStatus())
						)
					)
				);

			$custom = $this->getCustom($query);

			return $custom['count'];
		}

		public function addElement(Element $element)
		{
			$parent = $element->getParent();
			$item = $element->getItem();

			$rand = substr(md5(rand()), 0, 8);

			if(!$element->getElementName()) {
				$element->setElementName($rand);
			}

			if($element->getControllerName() == $item->getControllerName()) {
				$element->setControllerName(null);
			}

			$nextElementOrder = $this->getMaxElementOrder($parent) + 1;
			$element->setElementOrder($nextElementOrder);

			$status = $parent->getStatus();
			$element->setStatus($status);

			if(!$element->getShortName()) {
				$element->
				setShortName($rand)->
				setElementPath($parent->getElementPath().'/'.$rand);
			} else {
				$elementPath = $parent->getElementPath().'/'.$element->getShortName();

				$hasDuplicates =
					$item->getClass()->dao()->hasDuplicates(
						null,
						$element->getStatus(),
						$elementPath
					);

				if($hasDuplicates) {
					$element->
					setShortName($rand)->
					setElementPath($parent->getElementPath().'/'.$rand);
				} else {
					$element->
					setElementPath($elementPath);
				}
			}

			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element);
			}

			# Add element
			$element = $item->getClass()->dao()->add($element);

			$isUpdate = false;

			# Update element name
			if($element->getElementName() == $rand) {
				$elementName = sprintf('id%d', $element->getId());
				$element->setElementName($elementName);
				$isUpdate = true;
			}

			# Update element path
			if($element->getShortName() == $rand) {
				$shortName = $item->getPathPrefix().$element->getId();
				$elementPath = $parent->getElementPath().'/'.$shortName;

				$hasDuplicates =
					$item->getClass()->dao()->hasDuplicates(
						$element->getId(),
						$element->getStatus(),
						$elementPath
					);

				if(!$hasDuplicates) {
					$element->
					setShortName($shortName)->
					setElementPath($elementPath);
					$isUpdate = true;
				}
			}

			if($isUpdate) {
				$element = $item->getClass()->dao()->save($element);
			}

			return $element;
		}

		public function saveElement(Element $element)
		{
			$parent = $element->getRealParent();
			$item = $element->getItem();

			if(!$element->getElementName()) {
				$element->setElementName(sprintf('id%d', $element->getId()));
			}

			if($element->getControllerName() == $item->getControllerName()) {
				$element->setControllerName(null);
			}

			# Update element path
			$shortName =
				$element->getShortName()
				? $element->getShortName()
				: $item->getPathPrefix().$element->getId();
			$elementPath = $parent->getElementPath().'/'.$shortName;

			if($elementPath != $element->getElementPath()) {
				$hasDuplicates =
					$item->getClass()->dao()->hasDuplicates(
						$element->getId(),
						$element->getStatus(),
						$elementPath
					);

				if($hasDuplicates) {
					$shortName = substr(md5(rand()), 0, 8);
					$elementPath = $parent->getElementPath().'/'.$shortName;
				}

				$this->updateOffspringElementPath($element, $elementPath);
			}

			$element->setShortName($shortName)->setElementPath($elementPath);

			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element);
			}

			# Save element
			$element = $item->getClass()->dao()->save($element);

			return $element;
		}

		public function moveElement(Element $element, Element $target)
		{
			$item = $element->getItem();
			$propertyList = Property::dao()->getPropertyList($item);

			$move = false;

			foreach($propertyList as $property) {
				if(
					$property->getPropertyClass() == 'OneToOneProperty'
					&& $property->getFetchClass() == $target->getClass()
				) {
					$setter = $property->setter();
					$element->$setter($target);
					$move = true;
				}
			}

			if($move) {
				$parent = $element->getParent();
				$newElementPath = $parent->getElementPath().'/'.$element->getShortName();
				$newStatus = $parent->getStatus();

				$this->updateOffspringElementPath($element, $newElementPath);

				$element->setElementPath($newElementPath)->setStatus($newStatus);
				$element = $element->dao()->saveElement($element);
			}

			return $element;
		}

		public function moveElementToTrash(Element $currentElement)
		{
			$currentItem = $currentElement->getItem();
			if(!$currentItem || !$currentElement->checkRestriction()) return false;

			$this->uncacheLists();

			return $this->moveNodeToTrash($currentElement);
		}

		public function restoreElementFromTrash(Element $currentElement)
		{
			$currentItem = $currentElement->getItem();

			$this->uncacheLists();

			return $this->restoreNodeFromTrash($currentElement);
		}

		public function dropElement(Element $currentElement)
		{
			$currentItem = $currentElement->getItem();
			if(!$currentItem || !$currentElement->checkRestriction()) return false;

			return $this->dropNode($currentElement);
		}

		private function hasDuplicates($id, $status, $elementPath)
		{
			$id = (int)$id;

			$query =
				OSQL::select()->
				get(new SQLFunction('COUNT', new DBField('id', $this->getTable())), 'count')->
				from($this->getTable())->
				where(
					Expression::andBlock(
						Expression::eq(
							new DBField('status', $this->getTable()),
							new DBValue($status)
						),
						Expression::eq(
							new DBField('element_path', $this->getTable()),
							new DBValue($elementPath)
						),
						Expression::notEq(
							new DBField('id', $this->getTable()),
							new DBValue($id)
						)
					)
				)->
				limit(1);

			$custom = $this->getCustom($query, Cache::DO_NOT_CACHE);

			return $custom['count'] > 0;
		}

		private function updateOffspringElementPath(Element $element, $newElementPath)
		{
			$oldStatus = $element->getStatus();
			$oldElementPath = $element->getElementPath();

			$offspringItemList = $element->getOffspringItemList();

			foreach($offspringItemList as $offspringItem) {

				try {

					$offspringItemClass = $offspringItem->getClass();

					$query =
						OSQL::update($offspringItemClass->dao()->getTable())->
						set(
							'element_path',
							new SQLFunction(
								'REPLACE',
								new DBField('element_path'),
								new DBValue($oldElementPath.'/'),
								new DBValue($newElementPath.'/')
							)
						)->
						where(
							Expression::andBlock(
								Expression::eq(
									new SQLFunction(
										'SUBSTRING',
										new DBField('element_path'),
										new DBValue(1),
										new DBValue(strlen($oldElementPath.'/'))
									),
									new DBValue($oldElementPath.'/')
								),
								Expression::eq(
									new DBField('status'),
									new DBValue($oldStatus)
								)
							)
						);

					DBPool::me()->getByDao($offspringItemClass->dao())->query($query);

				} catch (BaseException $e) {
					ErrorMessageUtils::sendMessage($e);
				}
			}
		}

		private function getMaxElementOrder(Element $element)
		{
			$projection = new ProjectionChain();
			$projection->add(Projection::max('elementOrder', 'max'));

			$criteria =
				$this->getChildren($element)->
				setProjection($projection);

			try {
				return $criteria->getCustom('max');
			} catch (MissingElementException $e) {
				return 0;
			}
		}

		private function moveNodeToTrash(Element $currentElement)
		{
			# Move children to trash (recursively)

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				if($item->getClassType() == 'abstract') continue;

				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == 'OneToOneProperty'
						&& $property->getFetchClass() == $currentElement->getClass()
					) {
						switch($property->getOnDelete()) {
							case 'set_null':
								$propertyClass = $property->getClass(null);
								$query =
									OSQL::update($itemClass->dao()->getTable())->
									set(
										$propertyClass->getColumnName(),
										null
									)->
									where(
										Expression::eq(
											new DBField($propertyClass->getColumnName()),
											new DBValue($currentElement->getId())
										)
									);
								DBPool::me()->getByDao($itemClass->dao())->query($query);
								$itemClass->dao()->uncacheLists();
								break;
							case 'cascade':
								$elementList =
									$itemClass->dao()->
									getChildrenByProperty($currentElement, $property)->
									getList();
								foreach($elementList as $element) {
									$itemClass->dao()->moveNodeToTrash($element);
								}
								break;

							case 'restrict': default:
								break;
						}
						break;
					}
				}
			}

			# Set trash status

			$currentElement->setStatus('trash');
			$currentElement = $currentElement->dao()->save($currentElement);

			return $currentElement;
		}

		private function restoreNodeFromTrash(Element $currentElement)
		{
			# Restore children from trash (recursively)

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				if($item->getClassType() == 'abstract') continue;

				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == 'OneToOneProperty'
						&& $property->getFetchClass() == $currentElement->getClass()
					) {
						$elementList =
							$itemClass->dao()->
							getChildrenByProperty($currentElement, $property)->
							getList();
						foreach($elementList as $element) {
							$itemClass->dao()->restoreNodeFromTrash($element);
						}
					}
				}
			}

			# Set root status

			$currentElement->setStatus('root');
			$currentElement = $currentElement->dao()->save($currentElement);

			return $currentElement;
		}

		private function dropNode(Element $currentElement)
		{
			# Drop children (recursively)

			$itemList = Item::dao()->getItemList();

			foreach($itemList as $item) {
				if($item->getClassType() == 'abstract') continue;

				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == 'OneToOneProperty'
						&& $property->getFetchClass() == $currentElement->getClass()
					) {
						switch($property->getOnDelete()) {
							case 'set_null':
								$propertyClass = $property->getClass(null);
								$query =
									OSQL::update($itemClass->dao()->getTable())->
									set($propertyClass->getColumnName(), null)->
									where(
										Expression::eq(
											new DBField($propertyClass->getColumnName(), $itemClass->dao()->getTable()),
											new DBValue($currentElement->getId())
										)
									);
								DBPool::me()->getByDao($itemClass->dao())->query($query);
								$itemClass->dao()->uncacheLists();
								break;
							case 'cascade':
								$elementList =
									$itemClass->dao()->
									getChildrenByProperty($currentElement, $property)->
									getList();
								foreach($elementList as $element) {
									$itemClass->dao()->dropNode($element);
								}
								break;

							case 'restrict': default:
								break;
						}
						break;
					}
				}
			}

			# Drop element

			$currentElement = $this->dropOnlyElement($currentElement);

			return $currentElement;
		}

		private function dropOnlyElement(Element $element)
		{
			# Drop from element_permission
			ElementPermission::dao()->dropByElement($element);

			# Drop from bind_to_element
			Bind2Element::dao()->dropByElement($element);

			# Drop files
			$item = $element->getItem();
			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element);
				$propertyClass->drop();
			}

			# Drop element
			return $element->dao()->drop($element);
		}
	}
?>
<?php
	abstract class ElementDAO extends AutoElementDAO
	{
		const MAX_NUMBER_OF_DROP_ELEMENTS = 100;
		const ERROR_TOO_MANY_ELEMENTS = 1;

		public function getItem()
		{
			$item = Item::dao()->getItemByName($this->getObjectName());

			return $item;
		}

		public function getFolderName()
		{
			return $this->getTable();
		}

		public function getHashFolderName()
		{
			return null;
		}

		public function getFolderPath()
		{
			return PATH_LTDATA.$this->getFolderName().DIRECTORY_SEPARATOR;
		}

		public function getFolderWebPath()
		{
			return PATH_WEB_LTDATA.$this->getFolderName().DIRECTORY_SEPARATOR;
		}

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

		public function getByShortName($shortName)
		{
			$strlen = strlen($shortName) + 1;

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
						new SQLFunction(
							'SUBSTRING',
							new DBField('element_path', $this->getTable()),
							'-'.$strlen
						),
						new DBValue('/'.$shortName)
					)
				)->
				setLimit(1)->
				get();
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
			$criteria =
				$this->getChildren($element)->
				addProjection(Projection::count('id', 'count'));

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

			$loggedUser = LoggedUser::getUser();

			if($loggedUser instanceof User) {
				$element->
				setGroup($loggedUser->getGroup())->
				setUser($loggedUser);
			}

			$elementPathPrefix = $parent->getElementPath().'/';

			$rand = substr(md5(rand()), 0, 16);

			if(!$element->getElementName()) {
				$element->setElementName($rand);
			}

			$nextElementOrder = $this->getMaxElementOrder($parent) + 1;
			$element->setElementOrder($nextElementOrder);

			$status = $parent->getStatus();
			$element->setStatus($status);

			if($item->getIsUpdatePath()) {
				if(!$element->getShortName()) {
					$element->
					setShortName($rand)->
					setElementPath($elementPathPrefix.$rand);
				} else {
					$elementPath = $elementPathPrefix.$element->getShortName();

					$hasDuplicates =
						$item->getClass()->dao()->hasDuplicates(
							null,
							$element->getStatus(),
							$elementPath
						);

					if($hasDuplicates) {
						$element->
						setShortName($rand)->
						setElementPath($elementPathPrefix.$rand);
					} else {
						$element->
						setElementPath($elementPath);
					}
				}
			} else {
				$element->
				setElementPath(null);
			}

			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element);
				if($propertyClass) {
					$propertyClass->fixValue();
				}
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
			if($item->getIsUpdatePath()) {
				if($element->getShortName() == $rand) {
					$shortName = $item->getPathPrefix().$element->getId();
					$elementPath = $elementPathPrefix.$shortName;

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
			}

			if($isUpdate) {
				$element = $item->getClass()->dao()->save($element);
			}

			return $element;
		}

		public function saveElement(Element $element)
		{
			$parent = $element->getParent();
			$item = $element->getItem();

			if(!$element->getElementName()) {
				$element->setElementName(sprintf('id%d', $element->getId()));
			}

			# Update element path
			if($item->getIsUpdatePath()) {
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

				$element->setElementPath($elementPath);
			}

			# Update properties
			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element);
				if($propertyClass) {
					$propertyClass->fixValue();
				}
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
				$status = $parent->getStatus();

				$element->setStatus($status);

				# Update element path
				if($item->getIsUpdatePath()) {
					$elementPath = $parent->getElementPath().'/'.$element->getShortName();

					$hasDuplicates =
						$item->getClass()->dao()->hasDuplicates(
							$element->getId(),
							$status,
							$elementPath
						);

					if($hasDuplicates) {
						$shortName = substr(md5(rand()), 0, 8);
						$elementPath = $parent->getElementPath().'/'.$shortName;
						$element->setShortName($shortName);
					}

					$this->updateOffspringElementPath($element, $elementPath);

					$element->setElementPath($elementPath);
				}

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

		public function cacheEmptyResult(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER)
		{
			$className = $this->getObjectName();

			$watermark =
				Cache::me() instanceof WatermarkedPeer
				? Cache::me()->mark($className)->getActualWatermark()
				: null;

			$key = $className.'_query_'.$query->getId().$watermark;

			Cache::me()->mark($className)->add($key, Cache::NOT_FOUND, $expires);
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

		private function getOffspringItemList(Element $element)
		{
			$offspringItemList = array();

			$itemList = Item::dao()->getDefaultItemList();

			foreach($itemList as $item) {

			}

			return $offspringItemList;
		}

		private function updateOffspringElementPath(Element $element, $newElementPath)
		{
			$oldStatus = $element->getStatus();
			$oldElementPath = $element->getElementPath();
			$currentItem = $element->getItem();

			$itemList = Item::dao()->getOffspringItemList($currentItem);

			$offspringItemList = array();

			foreach($itemList as $item) {
				$count = $item->getClass()->dao()->getOffspringsCount($element);
				if($count > 0) {
					$offspringItemList[] = $item;
				}
			}

			foreach($offspringItemList as $offspringItem) {

				try {

					$offspringItemClass = $offspringItem->getClass();
					$dao = $offspringItemClass->dao();
					$db = DBPool::me()->getByDao($dao);

					$query =
						OSQL::update($dao->getTable())->
						set(
							'element_path',
							new SQLFunction(
								'REPLACE',
								new DBField('element_path', $dao->getTable()),
								new DBValue($oldElementPath.'/'),
								new DBValue($newElementPath.'/')
							)
						)->
						where(
							Expression::andBlock(
								Expression::eq(
									new SQLFunction(
										'SUBSTRING',
										new DBField('element_path', $dao->getTable()),
										new DBValue(1),
										new DBValue(strlen($oldElementPath.'/'))
									),
									new DBValue($oldElementPath.'/')
								),
								Expression::eq(
									new DBField('status', $dao->getTable()),
									new DBValue($oldStatus)
								)
							)
						);

					$db->query($query);
					$dao->uncacheLists();

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

			$currentItem = $currentElement->getItem();
			$parentElement = $currentElement->getParent();

			$elementPathPrefix =
				$currentItem->getIsUpdatePath()
				? $parentElement->getElementPath().'/'
				: '/';

			$status = 'trash';
			$elementPath = $currentElement->getElementPath();

			$hasDuplicates =
				$currentItem->getClass()->dao()->hasDuplicates(
					$currentElement->getId(),
					$status,
					$elementPath
				);

			if($hasDuplicates) {
				$shortName = substr(md5(rand()), 0, 8);
				$elementPath = $elementPathPrefix.$shortName;
				$currentElement->setShortName($shortName)->setElementPath($elementPath);
			}

			$currentElement->setStatus($status);

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

			$currentItem = $currentElement->getItem();
			$parentElement = $currentElement->getParent();

			$elementPathPrefix =
				$currentItem->getIsUpdatePath()
				? $parentElement->getElementPath().'/'
				: '/';

			$status = 'root';
			$elementPath = $currentElement->getElementPath();

			$hasDuplicates =
				$currentItem->getClass()->dao()->hasDuplicates(
					$currentElement->getId(),
					$status,
					$elementPath
				);

			if($hasDuplicates) {
				$shortName = substr(md5(rand()), 0, 8);
				$elementPath = $elementPathPrefix.$shortName;
				$currentElement->setShortName($shortName)->setElementPath($elementPath);
			}

			$currentElement->setStatus($status);
			$currentElement = $currentElement->dao()->save($currentElement);

			return $currentElement;
		}

		private function dropNode(Element $currentElement)
		{
			# Drop children (recursively)

			$itemList = Item::dao()->getDefaultItemList();

			foreach($itemList as $item) {
				$itemClass = $item->getClass();

				$propertyList = Property::dao()->getPropertyList($item);

				foreach($propertyList as $property) {
					if(
						$property->getPropertyClass() == Property::ONE_TO_ONE_PROPERTY
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

			# Drop properties
			$item = $element->getItem();
			$propertyList = Property::dao()->getPropertyList($item);
			foreach($propertyList as $property) {
				$propertyClass = $property->getClass($element)->setParameters();
				$propertyClass->drop();
			}

			# Drop element
			return $element->dao()->drop($element);
		}
	}
?>
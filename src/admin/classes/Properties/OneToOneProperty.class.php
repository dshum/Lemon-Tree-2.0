<?php
	final class OneToOneProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('node', 'element', 'Вершина дерева', Root::me());
			$this->addParameter('showItems', 'itemList', 'Показывать классы элементов', array());
			$this->addParameter('plainList', 'boolean', 'Плоский вид списка', false);
			$this->addParameter('showList', 'boolean', 'Выводить список элементов', true);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::INTEGER);
		}

		public function meta()
		{
			$required = $this->property->getIsRequired() ? 'true' : 'false';
			$fetchClass = $this->property->getFetchClass();
			$type = $fetchClass ? $fetchClass : 'Integer';
			switch($this->property->getFetchStrategyId()) {
				case FetchStrategy::CASCADE: $fetch = ' fetch="cascade"'; break;
				case FetchStrategy::LAZY: $fetch = ' fetch="lazy"'; break;
				default: $fetch = ''; break;
			}

			return '<property name="'.$this->property->getPropertyName().'" type="'.$type.'" relation="OneToOne"'.$fetch.' required="'.$required.'" />';
		}

		public function getColumnName()
		{
			return Property::getColumnName($this->property->getPropertyName()).'_id';
		}

		public function add2form(Form $form)
		{
			if(!$this->property->getFetchClass()) {
				return $form;
			}

			$primitive =
				Primitive::identifier($this->property->getPropertyName())->
				of($this->property->getFetchClass());

			if($this->property->getIsRequired()) {
				$primitive->required();
			}

			return $form->add($primitive);
		}

		public function add2search(Form $form)
		{
			if(!$this->property->getFetchClass()) {
				return $form;
			}

			$primitive1 =
				Primitive::identifier($this->property->getPropertyName())->
				of($this->property->getFetchClass());

			$primitive2 =
				Primitive::string($this->property->getPropertyName().'_name');

			return
				$form->
				add($primitive1)->
				add($primitive2);
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$raw = $form->getRawValue($this->property->getPropertyName());
			$value = $form->getValue($this->property->getPropertyName());

			$columnName = Property::getColumnName($this->property->getPropertyName()).'_id';
			$tableName = $criteria->getDao()->getTable();

			if($value instanceof Element) {
				$criteria->
				add(
					Expression::eqId(
						new DBField($columnName, $tableName),
						$value
					)
				);
			} elseif($raw) {
				$criteria->
				add(
					Expression::isTrue(
						DBValue::create(false)
					)
				);
			}

			return $criteria;
		}

		public function isUpdate()
		{
			$readonly = $this->getParameterValue('readonly');
			$showItemList = $this->getParameterValue('showItems');
			$plainList = $this->getParameterValue('plainList');

			return
				!$readonly && !$plainList && empty($showItemList)
				? true
				: false;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == false
				&& $form->primitiveExists($this->property->getPropertyName())
				&& $this->element instanceof Element
			) {
				$setter = $this->property->setter();
				$value = $form->getValue($this->property->getPropertyName());
				if($value) {
					$this->element->$setter($value);
				} else {
					$dropper = $this->property->dropper();
					$this->element->$dropper();
				}
			}
		}

		public function getEditElementView()
		{
			$required = $this->property->getIsRequired();
			$readonly = $this->getParameterValue('readonly');
			$node = $this->getParameterValue('node');
			$showItemList = $this->getParameterValue('showItems');
			$plainList = $this->getParameterValue('plainList');
			$checked = $this->value ? '' : ' checked';

			if($plainList) {

				$tree = Tree::getLinkPlainList(
					null,
					$this->property->getFetchClass(),
					$this->value
				);

			} elseif($node == 'parent' && $this->element instanceof Element) {

				$node = $this->element->getParent();

				$tree = Tree::getLinkPlainList(
					$node,
					$this->property->getFetchClass(),
					$this->value
				);

			} elseif(!$readonly && $node instanceof Element) {

				$openIdList = array();

				if($this->value) {
					$parentList = $this->value->getParentList();
					foreach($parentList as $parent) {
						$openIdList[$parent->getPolymorphicId()] = 1;
					}
				}

				$tree = Tree::getLinkTree(
					$showItemList,
					$openIdList,
					$this->property->getFetchClass(),
					$this->value
				);

			} else {

				$tree = array();

			}

			$fetchClass = $this->property->getFetchClass();

			try {
				$fetchItem = Item::dao()->getItemByName($fetchClass);
			} catch (ObjectNotFoundException $e) {
				$fetchItem = null;
			}

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $this->value)->
				set('required', $required)->
				set('readonly', $readonly)->
				set('node', $node)->
				set('checked', $checked)->
				set('showItemList', $showItemList)->
				set('plainList', $plainList)->
				set('tree', $tree)->
				set('fetchItem', $fetchItem);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function getElementSearchView(Form $form)
		{
			$fetchClass = $this->property->getFetchClass();

			try {
				$fetchItem = Item::dao()->getItemByName($fetchClass);
			} catch (ObjectNotFoundException $e) {
				return null;
			}

			$propertyDescription = $this->property->getPropertyDescription();
			if(mb_strlen($propertyDescription) > 50) {
				$propertyDescription = mb_substr($propertyDescription, 0, 50).'...';
			}

			$propertyName = $this->property->getPropertyName();

			$value =
				$form->primitiveExists($propertyName)
				? $form->getValue($propertyName)
				: null;

			$model =
				Model::create()->
				set('propertyName', $propertyName)->
				set('propertyDescription', $propertyDescription)->
				set('value', $value)->
				set('fetchItem', $fetchItem);

			$viewName = 'properties/'.get_class($this).'.search';

			return $this->render($model, $viewName);
		}
	}
?>
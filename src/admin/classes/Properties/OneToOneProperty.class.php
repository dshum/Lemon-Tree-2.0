<?php
	final class OneToOneProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('node', 'element', 'Вершина дерева', Root::me());
			$this->addParameter('mount', 'integer', 'Глубина дерева', 1);
			$this->addParameter('showItems', 'itemList', 'Показывать классы элементов', array());
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
			$primitive =
				Primitive::identifier($this->property->getPropertyName())->
				of($this->property->getFetchClass());

			return $form->add($primitive);
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
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
			}

			return $criteria;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == false
				&& $form->primitiveExists($this->property->getPropertyName())
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
			$mount = $this->getParameterValue('mount');
			$showItemList = $this->getParameterValue('showItems');
			$checked = $this->value ? '' : ' checked';

			if(!$readonly && $node instanceof Element) {
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
					$this->value,
					$this->property->getFetchClass()
				);
			} else {
				$tree = array();
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
				set('tree', $tree);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function printOnElementSearch(Form $form)
		{
			$fetchClass = $this->property->getFetchClass();
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			$str = $this->property->getPropertyDescription().' (ID элемента): ';
			$str .= '<input type="text" class="prop-mini" name="'.$this->property->getPropertyName().'" value="'.($value ? $value->getId() : '').'" style="width: 75px;">';
			return $str;
		}
	}
?>
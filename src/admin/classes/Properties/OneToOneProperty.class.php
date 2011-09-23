<?php
	final class OneToOneProperty extends BaseProperty
	{
		private $tree = array();
		private $openIdList = array();

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::INTEGER);

			$this->addParameter('node', 'element', 'Вершина дерева', Root::me());
			$this->addParameter('mount', 'integer', 'Глубина дерева', 1);
			$this->addParameter('showItems', 'itemList', 'Показывать классы элементов', array());
			$this->addParameter('showList', 'boolean', 'Выводить список элементов', true);
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
			if($form->PrimitiveExists($this->property->getPropertyName())) {
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

		public function editOnElement()
		{
			$required = $this->property->getIsRequired();
			$readonly = $this->getParameterValue('readonly');
			$node = $this->getParameterValue('node');
			$mount = $this->getParameterValue('mount');
			$showItemList = $this->getParameterValue('showItems');
			$checked = $this->value ? '' : ' checked';

			if($readonly) {

				$str = $this->property->getPropertyDescription().': ';
				$str .=
					$this->value
					? (
						$this->value->getItem()
						? '<input type="hidden" name="'.$this->property->getPropertyName().'" value="'.$this->value->getId().'"><a href="'.PATH_ADMIN_BROWSE.'?module=ElementEdit&elementId='.$this->value->getPolymorphicId().'" title="'.$this->value->getItem()->getItemDescription().'">'.$this->value->getElementName().'</a><br>'
						: '<input type="hidden" name="'.$this->property->getPropertyName().'" value="'.$this->value->getId().'">'.$this->value->getElementName().'<br>'
					)
					: 'Не определено<br>';
				$str .= '<br>';

				return $str;

			} else {

				if($this->value) {
					$parentList = $this->value->getParentList();
					foreach($parentList as $parent) {
						$this->openIdList[$parent->getPolymorphicId()] = 1;
					}
				}

				if($this->element && $this->element->getStatus() == 'trash') {
					$this->tree = Tree::getTreeForLink($showItemList);
				} else {
					$this->tree = Tree::getValidTreeForLink($showItemList);
				}

				if(
					$node instanceof Element
					&& isset($this->tree[$node->getPolymorphicId()])
				) {

					$str = $this->property->getPropertyDescription().': ';
					$str .=
						$this->value
						? '<span id="'.$this->property->getPropertyName().'_description" class="dashed hand" onetoone="span" propertyname="'.$this->property->getPropertyName().'">'.$this->value->getElementName().'</span><br>'
						: '<span id="'.$this->property->getPropertyName().'_description" class="dashed hand" onetoone="span" propertyname="'.$this->property->getPropertyName().'">Не определено</span><br>';
					$str .= '<div id="'.$this->property->getPropertyName().'_block" onetoone="block" class="prop_block" style="overflow-y: scroll; display: none;" overflow="true">';
					$str .= '<div><img src="img/p.gif" width="11" height="11" alt="">&nbsp;<input type="radio" id="'.$this->property->getPropertyName().'_check_0" name="'.$this->property->getPropertyName().'" onetoone="radio" elementname="Не определено" value=""'.$checked.' title="Выбрать">&nbsp;<label for="'.$this->property->getPropertyName().'_check_0">Не определено</label></div>';
					$str .= $this->showKids($node, $mount);
					$str .= '</div><br>';

				} else {

					$str = $this->property->getPropertyDescription().': ';
					$str .=
						$this->value
						? (
							$this->value->getItem()
							? '<input type="hidden" name="'.$this->property->getPropertyName().'" value="'.$this->value->getId().'"><a href="'.PATH_ADMIN_BROWSE.'?module=ElementEdit&elementId='.$this->value->getPolymorphicId().'" title="'.$this->value->getItem()->getItemDescription().'">'.$this->value->getElementName().'</a><br>'
							: '<input type="hidden" name="'.$this->property->getPropertyName().'" value="'.$this->value->getId().'">'.$this->value->getElementName().'<br>'
						)
						: 'Не определено<br>';
					$str .= '<br>';

				}

				return $str;

			}
		}

		public function printOnElementList()
		{
			$str =
				$this->value
				? '<a href="'.PATH_ADMIN_BROWSE.'?module=ElementEdit&elementId='.$this->value->getPolymorphicId().'" title="'.$this->value->getItem()->getItemDescription().'">'.$this->value->getElementName().'</a>'
				: '';

			return $str;
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

		private function showKids($node, $mount)
		{
			$str = '';

			if($mount == 0) return $str;

			$kidList =
				isset($this->tree[$node->getPolymorphicId()])
				? $this->tree[$node->getPolymorphicId()]
				: array();


			foreach($kidList as $kid) {

				$hasKids =
					isset($this->tree[$kid->getPolymorphicId()])
					? true
					: false;
				$checked =
					(
						$this->value
						&& $kid instanceof $this->value
						&& $kid->getId() == $this->value->getId()
					)
					? ' checked'
					: '';

				$str .= '<div>';
				if($hasKids && $mount > 1 && isset($this->openIdList[$kid->getPolymorphicId()])) {
					$str .= '<a href="" class="plus" onetoone="link" open="true" elementid="'.$kid->getPolymorphicId() . '" propertyname="'.$this->property->getPropertyName().'"><img id="'.$this->property->getPropertyName().'_plus_'.$kid->getPolymorphicId().'" src="img/ico_minus.gif" width="13" height="13" alt="Свернуть ветку" title="Свернуть ветку"></a>';
				} elseif($hasKids && $mount > 1) {
					$str .= '<a href="" class="plus" onetoone="link" open="false" elementid="'.$kid->getPolymorphicId() . '" propertyname="'.$this->property->getPropertyName().'"><img id="'.$this->property->getPropertyName().'_plus_'.$kid->getPolymorphicId().'" src="img/ico_plus.gif" width="13" height="13" alt="Раскрыть ветку" title="Раскрыть ветку"></a>';
				} else {
					$str .= '<img src="img/p.gif" width="11" height="11" alt="">&nbsp;';
				}
				if($kid->getItem()->getItemName() == $this->property->getFetchClass()) {
					$str .= '<input type="radio" id="'.$this->property->getPropertyName().'_check_'.$kid->getPolymorphicId().'" name="'.$this->property->getPropertyName().'" onetoone="radio" elementname="'.$kid->getElementName().'" value="'.$kid->getId().'"'.$checked.' title="Выбрать">&nbsp;<label for="'.$this->property->getPropertyName().'_check_'.$kid->getPolymorphicId().'">'.$kid->getElementName().'</label>';
				} else {
					$str .= '&nbsp;'.$kid->getElementName();
				}
				if($hasKids && $mount > 1 && isset($this->openIdList[$kid->getPolymorphicId()])) {
					$str .= '<div id="'.$this->property->getPropertyName().'_node_'.$kid->getPolymorphicId().'" style="padding-left: 20px; display: block;">';
					$str .= $this->showKids($kid, $mount - 1);
					$str .= '</div>';
				} elseif($hasKids && $mount > 1) {
					$str .= '<div id="'.$this->property->getPropertyName().'_node_'.$kid->getPolymorphicId().'" style="padding-left: 20px; display: none;">';
					$str .= $this->showKids($kid, $mount - 1);
					$str .= '</div>';
				}
				$str .= '</div>';
			}

			return $str;
		}
	}
?>
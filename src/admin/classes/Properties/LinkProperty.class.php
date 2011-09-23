<?php
	final class LinkProperty extends BaseProperty
	{
		private $tree = array();
		private $openIdList = array();

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::VARCHAR)->setSize(50);

			try {
				list($itemId, $elementId) = explode(':', $this->value);
				$item = Item::dao()->getById($itemId);
				$element = $item->getClass()->dao()->getById($elementId);
				$this->value = $element;
			} catch (BaseException $e) {
				$this->value = null;
			}

			$this->addParameter('node', 'element', 'Вершина дерева', Root::me());
			$this->addParameter('mount', 'integer', 'Глубина дерева', 1);
			$this->addParameter('showItems', 'itemList', 'Показывать классы элементов', array());
			$this->addParameter('editItems', 'itemList', 'Редактировать классы элементов', array());
		}

		public function meta()
		{
			$required = $this->property->getIsRequired() ? 'true' : 'false';
			return '<property name="'.$this->property->getPropertyName().'" type="String" size="50" required="'.$required.'" />';
		}

		public function add2form(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(50);
			if($this->property->getIsRequired()) {
				$primitive->required();
			}
			return $form->add($primitive);
		}

		public function isUpdate()
		{
			return false;
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
					? '<a href="'.PATH_ADMIN_BROWSE.'?module=ElementEdit&elementId='.$this->value->getPolymorphicId().'" title="'.$this->value->getItem()->getItemDescription().'">'.$this->value->getElementName().'</a><br>'
					: 'Не определено<br>';
				$str .= '<br>';

				return $str;

			} else {

				if($this->value) {
					$parentList = $this->value->getParentList();
					foreach($parentList as $parent) {
						$this->openIdList[$parent->getItemId()][$parent->getId()] = 1;
					}
				}

				if($this->element && $this->element->getStatus() == 'trash') {
					$this->tree = Tree::getTreeForLink($showItemList);
				} else {
					$this->tree = Tree::getValidTreeForLink($showItemList);
				}

				$str = $this->property->getPropertyDescription().':<br>';
				$str .= '<div id="'.$this->property->getPropertyName().'_block" block="true" class="prop_block" style="overflow-y: scroll;" overflow="true">';
				$str .= '<div><img src="img/p.gif" width="11" height="11" alt="">&nbsp;<input type="radio" id="'.$this->property->getPropertyName().'_check_0" name="'.$this->property->getPropertyName().'" value=""'.$checked.' title="Выбрать">&nbsp;<label for="'.$this->property->getPropertyName().'_check_0">Не определено</label></div>';
				$str .= $this->showKids($node, $mount);
				$str .= '</div><br>';

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

		private function showKids($node, $mount)
		{
			$str = '';

			if($mount == 0) return $str;

			$editItemList = $this->getParameterValue('editItems');

			$kidList =
				isset($this->tree[$node->getItemId()][$node->getId()])
				? $this->tree[$node->getItemId()][$node->getId()]
				: array();


			foreach($kidList as $kid) {

				$hasKids =
					isset($this->tree[$kid->getItemId()][$kid->getId()])
					? true
					: false;
				$checked =
					(
						$this->value
						&& $kid->getItemId() == $this->value->getItemId()
						&& $kid->getId() == $this->value->getId()
					)
					? ' checked'
					: '';

				$str .= '<div>';
				if($hasKids && $mount > 1 && isset($this->openIdList[$kid->getItemId()][$kid->getId()])) {
					$str .= '<a href="" class="plus" link="true" open="true" elementid="'.$kid->getPolymorphicId() . '" propertyname="'.$this->property->getPropertyName().'"><img id="'.$this->property->getPropertyName().'_plus_'.$kid->getItemId().'_'.$kid->getId().'" src="img/ico_minus.gif" width="13" height="13" alt="Свернуть ветку" title="Свернуть ветку"></a>';
				} elseif($hasKids && $mount > 1) {
					$str .= '<a href="" class="plus" link="true" open="false" elementid="'.$kid->getPolymorphicId() . '" propertyname="'.$this->property->getPropertyName().'"><img id="'.$this->property->getPropertyName().'_plus_'.$kid->getItemId().'_'.$kid->getId().'" src="img/ico_plus.gif" width="13" height="13" alt="Раскрыть ветку" title="Раскрыть ветку"></a>';
				} else {
					$str .= '<img src="img/p.gif" width="11" height="11" alt="">&nbsp;';
				}
				if(in_array($kid->getItem(), $editItemList)) {
					$str .= '<input type="radio" id="'.$this->property->getPropertyName().'_check_'.$kid->getItemId().'_'.$kid->getId().'" name="'.$this->property->getPropertyName().'" value="'.$kid->getItemId().':'.$kid->getId().'"'.$checked.' title="Выбрать">&nbsp;<label for="'.$this->property->getPropertyName().'_check_'.$kid->getItemId().'_'.$kid->getId().'">'.$kid->getElementName().'</label>';
				} else {
					$str .= '&nbsp;'.$kid->getElementName();
				}
				if($hasKids && $mount > 1 && isset($this->openIdList[$kid->getItemId()][$kid->getId()])) {
					$str .= '<div id="'.$this->property->getPropertyName().'_node_'.$kid->getItemId().'_'.$kid->getId().'" style="padding-left: 20px; display: block;">';
					$str .= $this->showKids($kid, $mount - 1);
					$str .= '</div>';
				} elseif($hasKids && $mount > 1) {
					$str .= '<div id="'.$this->property->getPropertyName().'_node_'.$kid->getItemId().'_'.$kid->getId().'" style="padding-left: 20px; display: none;">';
					$str .= $this->showKids($kid, $mount - 1);
					$str .= '</div>';
				}
				$str .= '</div>';
			}

			return $str;
		}
	}
?>
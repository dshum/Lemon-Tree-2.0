<?php
	final class ManyToManyProperty extends BaseProperty
	{
		protected $list = array();

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			if($this->value instanceof ManyToManyLinked) {
				try {
					$this->list = $this->value->getList();
				} catch (BaseException $e) {}
			}
		}

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('node', 'element', 'Вершина дерева', Root::me());
			$this->addParameter('mount', 'integer', 'Глубина дерева', 1);
			$this->addParameter('showItems', 'itemList', 'Показывать классы элементов', array());
			$this->addParameter('showList', 'boolean', 'Выводить список элементов', true);

			return $this;
		}

		public function meta()
		{
			$fetchClass = $this->property->getFetchClass();
			$type = $fetchClass ? $fetchClass : 'Integer';

			return '<property name="'.$this->property->getPropertyName().'" type="'.$type.'" relation="ManyToMany" />';
		}

		public function column()
		{
			return null;
		}

		public function add2form(Form $form)
		{
			if(!$this->property->getFetchClass()) {
				return $form;
			}

			$primitive =
				Primitive::identifierlist($this->property->getPropertyName())->
				of($this->property->getFetchClass());

			return $form->add($primitive);
		}

		public function getEditElementView()
		{
			$readonly = $this->getParameterValue('readonly');
			$node = $this->getParameterValue('node');
			$mount = $this->getParameterValue('mount');
			$showItemList = $this->getParameterValue('showItems');

			if(!$readonly && $node instanceof Element) {
				$openIdList = array();
				foreach($this->list as $element) {
					$parentList = $element->getParentList();
					foreach($parentList as $parent) {
						$openIdList[$parent->getPolymorphicId()] = 1;
					}
				}
				$tree = Tree::getMultilinkTree(
					$showItemList,
					$openIdList,
					$this->list,
					$this->property->getFetchClass()
				);
			} else {
				$tree = array();
			}

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('list', $this->list)->
				set('readonly', $readonly)->
				set('node', $node)->
				set('tree', $tree);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function getElementListView()
		{
			return null;
		}

		public function getEditElementListView()
		{
			return null;
		}

		public function set(Form $form) {}

		public function setAfter(Form $form)
		{
			$elementList = $form->getValue($this->property->getPropertyName());
			if($this->value instanceof ManyToManyLinked) {
				$this->value->setList($elementList)->save();
			}
		}

		public function drop()
		{
			if($this->value instanceof ManyToManyLinked) {
				$this->value->setList(array())->save();
			}
		}
	}
?>
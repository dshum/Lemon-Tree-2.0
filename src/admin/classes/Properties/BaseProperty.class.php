<?php
	abstract class BaseProperty
	{
		protected $property = null;
		protected $element = null;
		protected $value = null;
		protected $parameters = array();

		public function __construct($property, $element)
		{
			$this->property = $property;
			$this->element = $element;

			if($element instanceof Element) {
				$getter = $property->getter();
				if(method_exists($element, $getter)) {
					try {
						$this->value = $element->$getter();
					} catch (BaseException $e) {}
				}
			}
		}

		public function __toString()
		{
			return $this->value ? $this->value : null;
		}

		public function setParameters()
		{
			$this->
			addParameter('readonly', 'boolean', 'Только чтение', false)->
			addParameter('hidden', 'boolean', 'Скрыть поле', false);

			return $this;
		}

		public function getDataType() {}

		public function meta() {}

		public function getColumnName()
		{
			return Property::getColumnName($this->property->getPropertyName());
		}

		public function fixValue() {}

		public function addParameter($name, $type, $description, $default)
		{
			$parameterClass = Property::getParameterClass($type);
			$parameter = new $parameterClass(
				$this->property,
				$name,
				$description,
				$default
			);
			$this->parameters[$name] = $parameter;

			return $this;
		}

		public function getParameter($name)
		{
			return
				isset($this->parameters[$name])
				? $this->parameters[$name]
				: null;
		}

		public function getParameterValue($name)
		{
			$parameter = $this->getParameter($name);
			return $parameter ? $parameter->getValue() : null;
		}

		public function getParameterList()
		{
			return $this->parameters;
		}

		public function isUpdate()
		{
			return false;
		}

		public function set(Form $form)
		{
			if(
				$this->getParameterValue('hidden') == false
				&& $this->getParameterValue('readonly') == false
				&& $form->primitiveExists($this->property->getPropertyName())
			) {
				$setter = $this->property->setter();
				$value = $form->getValue($this->property->getPropertyName());
				$this->element->$setter($value);
			}
		}

		public function setAfter(Form $form) {}

		public function drop() {}

		public function value()
		{
			return $this->value;
		}

		public function column()
		{
			$dataType = $this->getDataType();

			if($this->property->getIsRequired()) {
				$dataType->setNull(false);
			}

			return DBColumn::create($dataType, $this->getColumnName());
		}

		public function add2form(Form $form)
		{
			return $form;
		}

		public function add2multiform(Form $form)
		{
			return $form;
		}

		public function add2search(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(3)->
				setMax(255)->
				addImportFilter(Filter::trim());

			return $form->add($primitive);
		}

		public function add2criteria(Criteria $criteria, Form $form)
		{
			$value = $form->getValue($this->property->getPropertyName());

			$columnName = Property::getColumnName($this->property->getPropertyName());
			$tableName = $criteria->getDao()->getTable();

			if($value) {
				$criteria->
				add(
					Expression::like(
						new DBField($columnName, $tableName),
						new DBValue('%'.$value.'%')
					)
				);
			}

			return $criteria;
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('value', $this->value);

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementListView()
		{
			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('element', $this->element)->
				set('value', $this->value);

			$viewName = 'properties/'.get_class($this).'.editElementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementView()
		{
			$readonly = $this->getParameterValue('readonly');

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('readonly', $readonly)->
				set('value', $this->value);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function getElementSearchView(Form $form)
		{
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
				set('value', $value);

			$viewName = 'properties/'.get_class($this).'.search';

			return $this->render($model, $viewName);
		}

		protected function render($model, $viewName)
		{
			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView')->
				addPrefix(PATH_ADMIN_TEMPLATES);

			try {
				$view = $viewResolver->resolveViewName($viewName);
				return $view->toString($model);
			} catch (WrongArgumentException $e) {
				return null;
			}
		}
	}
?>
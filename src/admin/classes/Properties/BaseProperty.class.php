<?php
	abstract class BaseProperty
	{
		protected $item = null;
		protected $property = null;
		protected $element = null;
		protected $value = null;
		protected $dataType = null;
		protected $parameters = array();

		public function __construct($property, $element)
		{
			$this->property = $property;
			$this->item = $this->property->getItem();
			$this->element = $element;

			$this->value = null;
			if($element instanceof Element) {
				$getter = $property->getter();
				if(method_exists($element, $getter)) {
					try {
						$this->value = $element->$getter();
					} catch (BaseException $e) {}
				}
			}

			$this->addParameter('readonly', 'boolean', 'Только чтение', false);
		}

		public function __toString()
		{
			return $this->value ? $this->value : null;
		}

		public function getColumnName()
		{
			return Property::getColumnName($this->property->getPropertyName());
		}

		public function isUpdate()
		{
			return false;
		}

		public function set(Form $form)
		{
			if($form->primitiveExists($this->property->getPropertyName())) {
				$setter = $this->property->setter();
				$value = $form->getValue($this->property->getPropertyName());
				$this->element->$setter($value);
			}
		}

		public function addParameter($name, $type, $description, $default)
		{
			$parameterClass = Parameter::getParameterClass($type);
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
			if(isset($this->parameters[$name])) {
				return $this->parameters[$name];
			} else {
				throw new ObjectNotFoundException('Parameter '.$name.' is not found.');
			}
		}

		public function getParameterValue($name)
		{
			try {
				$parameter = $this->getParameter($name);
				return $parameter->getValue();
			} catch (ObjectNotFoundException $e) {
				return null;
			}
		}

		public function getParameterList()
		{
			return $this->parameters;
		}

		public function value()
		{
			return $this->value;
		}

		public function column()
		{
			if($this->property->getIsRequired()) {
				$this->dataType->setNull(false);
			}
			return DBColumn::create($this->dataType, $this->getColumnName());
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

		public function printOnElementSearch(Form $form)
		{
			$value =
				$form->primitiveExists($this->property->getPropertyName())
				? $form->getValue($this->property->getPropertyName())
				: null;
			$str = $this->property->getPropertyDescription().': ';
			$str .= '<input type="text" class="prop" name="'.$this->property->getPropertyName().'" value="'.$value.'" style="width: 50%;">';
			return $str;
		}

		public function printOnElementList()
		{
			$str = $this->value;
			return $str;
		}

		public function editOnElementList()
		{
			$str = $this->value;
			return $str;
		}

		public function meta() {}
		public function editOnElement() {}
		public function drop() {}
	}
?>
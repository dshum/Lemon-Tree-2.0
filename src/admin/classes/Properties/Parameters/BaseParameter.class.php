<?php
	abstract class BaseParameter
	{
		protected $property = null;
		protected $name = null;
		protected $description = null;
		protected $default = null;
		protected $value = null;

		public static function create($name, $description, $default)
		{
			return new self($name, $description, $default);
		}

		public function __construct(Property $property, $name, $description, $default)
		{
			$this->property = $property;
			$this->name = $name;
			$this->description = $description;
			$this->default = $default;

			try {

				$parameter = Parameter::dao()->getParameterByName($property, $name);

				$raw = $parameter->getParameterValue();

				$this->setValue($raw);

			} catch (ObjectNotFoundException $e) {
				$this->value = $default;
			}
		}

		public function getProperty()
		{
			return $this->property;
		}

		public function getName()
		{
			return $this->name;
		}

		public function getDescription()
		{
			return $this->description;
		}

		public function getDefault()
		{
			return $this->default;
		}

		public function getValue()
		{
			return $this->value;
		}

		public function primitive()
		{
			return Primitive::string($this->name);
		}

		public function toRaw($value)
		{
			return $value;
		}

		public function printOnEdit() {}
		public function setValue($raw) {}
	}
?>
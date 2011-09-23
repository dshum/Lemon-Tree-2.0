<?php
	final class Property extends AutoProperty implements Prototyped, DAOConnected
	{
		const PARAMETER_ROW_DELIMETER = EOL;
		const PARAMETER_VALUE_DELIMETER = ':';

		private static $propertyClassList = array(
			'TextfieldProperty' => 'Текстовое поле',
			'TextareaProperty' => 'Многострочное текстовое поле',
			'RichtextProperty' => 'Визуальный редактор (TinyMCE)',
			'IntegerProperty' => 'Целое число',
			'FloatProperty' => 'Число с плавающей запятой',
			'BooleanProperty' => 'Чекбокс',
			'ImageProperty' => 'Изображение',
			'FileProperty' => 'Файл',
			'DateProperty' => 'Дата',
			'TimeProperty' => 'Время',
			'TimestampProperty' => 'Дата и время',
			'PasswordProperty' => 'Пароль',
			'YandexMapProperty' => 'Яндекс.Карты',
		);

		private static $parameterClassMap = array(
			'boolean' => 'BooleanParameter',
			'integer' => 'IntegerParameter',
			'float' => 'FloatParameter',
			'item' => 'ItemParameter',
			'itemList' => 'ItemListParameter',
			'element' => 'ElementParameter',
			'string' => 'StringParameter',
		);

		private static $onDeleteActionList = array(
			'restrict' => 'RESTRICT',
			'set_null' => 'SET NULL',
			'cascade' => 'CASCADE',
		);

		private $parameterList = array();

		/**
		 * @return Property
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return PropertyDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('PropertyDAO');
		}

		/**
		 * @return ProtoProperty
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoProperty');
		}

		public static function getColumnName($name)
		{
			return trim(mb_strtolower(preg_replace(':([A-Z]):', '_\1', $name)), '_');
		}

		public static function getPropertyClassList()
		{
			return self::$propertyClassList;
		}

		public static function getParameterClassMap()
		{
			return self::$parameterClassMap;
		}

		public static function getParameterClass($type)
		{
			if(isset(self::$parameterClassMap[$type])) {
				return self::$parameterClassMap[$type];
			} else {
				throw new WrongArgumentException();
			}
		}

		public static function getOnDeleteActionList()
		{
			return self::$onDeleteActionList;
		}

		public function getClass($element)
		{
			try {
				$propertyClassName = $this->getPropertyClass();
				if(
					ClassUtils::isClassName($propertyClassName)
					&& ClassUtils::isInstanceOf($propertyClassName, 'BaseProperty')
				) {
					return new $propertyClassName($this, $element);
				}
			} catch (BaseException $e) {}

			return null;
		}

		public function getParameterList()
		{
			$parameterList = array();

			$parameters = $this->getPropertyParameters();
			$rows = explode(self::PARAMETER_ROW_DELIMETER, $parameters);
			foreach($rows as $row) {
				if($row) {
					list($name, $value) = explode(self::PARAMETER_VALUE_DELIMETER, $row);
					$parameterList[$name] = $value;
				}
			}

			return $parameterList;
		}

		public function setParameterList($parameterList)
		{
			$rows = array();
			foreach($parameterList as $name => $value) {
				$rows[] = $name.self::PARAMETER_VALUE_DELIMETER.$value;
			}
			$parameters =
				sizeof($rows)
				? implode(self::PARAMETER_ROW_DELIMETER, $rows)
				: null;

			$this->setPropertyParameters($parameters);

			return $this;
		}

		public function getParameterValue($name)
		{
			$parameterList = $this->getParameterList();

			if(isset($parameterList[$name])) {
				return $parameterList[$name];
			} else {
				throw new ObjectNotFoundException('Parameter with name '.$name.' is not found.');
			}
		}

		public function getFetchStrategyName()
		{
			switch($this->getFetchStrategyId()) {
				case FetchStrategy::CASCADE: return 'cascade';
				case FetchStrategy::LAZY: return 'lazy';
				default: return 'join';
			}
		}

		public function setter()
		{
			return 'set'.ucfirst($this->getPropertyName());
		}

		public function getter()
		{
			return 'get'.ucfirst($this->getPropertyName());
		}

		public function dropper()
		{
			return 'drop'.ucfirst($this->getPropertyName());
		}
	}
?>
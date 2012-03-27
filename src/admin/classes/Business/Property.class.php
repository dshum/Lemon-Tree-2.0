<?php
	final class Property extends AutoProperty implements Prototyped, DAOConnected
	{
		const TEXTFIELD_PROPERTY = 'TextfieldProperty';
		const TEXTAREA_PROPERTY = 'TextareaProperty';
		const RICHTEXT_PROPERTY = 'RichtextProperty';
		const INTEGER_PROPERTY = 'IntegerProperty';
		const FLOAT_PROPERTY = 'FloatProperty';
		const BOOLEAN_PROPERTY = 'BooleanProperty';
		const IMAGE_PROPERTY = 'ImageProperty';
		const FILE_PROPERTY = 'FileProperty';
		const LINK_PROPERTY = 'LinkProperty';
		const DATE_PROPERTY = 'DateProperty';
		const TIMESTAMP_PROPERTY = 'TimestampProperty';
		const TIME_PROPERTY = 'TimeProperty';
		const PASSWORD_PROPERTY = 'PasswordProperty';
		const VIRTUAL_PROPERTY = 'VirtualProperty';
		const YANDEX_MAP_PROPERTY = 'YandexMapProperty';
		const POINT_PROPERTY = 'PointProperty';
		const ONE_TO_ONE_PROPERTY = 'OneToOneProperty';
		const ONE_TO_MANY_PROPERTY = 'OneToManyProperty';
		const MANY_TO_MANY_PROPERTY = 'ManyToManyProperty';
		const BINARY_PROPERTY = 'BinaryProperty';

		const PARAMETER_ROW_DELIMETER = EOL;
		const PARAMETER_VALUE_DELIMETER = ':';

		private static $propertyClassList = array(
			self::TEXTFIELD_PROPERTY => 'Текстовое поле',
			self::TEXTAREA_PROPERTY => 'Многострочное текстовое поле',
			self::RICHTEXT_PROPERTY => 'Визуальный редактор (TinyMCE)',
			self::INTEGER_PROPERTY => 'Целое число',
			self::FLOAT_PROPERTY => 'Число с плавающей запятой',
			self::BOOLEAN_PROPERTY => 'Чекбокс',
			self::IMAGE_PROPERTY => 'Изображение',
			self::FILE_PROPERTY => 'Файл',
			self::LINK_PROPERTY => 'Ссылка на элемент',
			self::DATE_PROPERTY => 'Дата',
			self::TIMESTAMP_PROPERTY => 'Дата и время',
			self::TIME_PROPERTY => 'Время',
			self::PASSWORD_PROPERTY => 'Пароль',
			self::YANDEX_MAP_PROPERTY => 'Яндекс.Карты',
			self::POINT_PROPERTY => 'Координаты точки',
			self::BINARY_PROPERTY => 'Двоичные данные',
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
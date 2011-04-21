<?php
	final class YandexMapProperty extends BaseProperty
	{
		private $x = null, $y = null, $scale = null;

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			try {
				list($this->x, $this->y, $this->scale) = explode(',', $this->value);
			} catch (BaseException $e) {
				$this->value = null;
			}
		}

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('width', 'integer', 'Ширина карты', 600);
			$this->addParameter('height', 'integer', 'Высота карты', 400);
			$this->addParameter('scale', 'integer', 'Масштаб карты', 12);
			$this->addParameter('centerX', 'float', 'Начальная точка (X)', 37.64);
			$this->addParameter('centerY', 'float', 'Начальная точка (Y)', 55.76);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::VARCHAR)->setSize(255);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" size="255" required="false" />';
		}

		public function add2form(Form $form)
		{
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(255)->
				addImportFilter(Filter::trim());

			return $form->add($primitive);
		}

		public function x($precision = 6)
		{
			return round($this->x, $precision);
		}

		public function y($precision = 6)
		{
			return round($this->y, $precision);
		}

		public function scale()
		{
			return $this->scale;
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('x', $this->x(3))->
				set('y', $this->y(3));

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementView()
		{
			$centerX = $this->x() ? $this->x() : $this->getParameterValue('centerX');
			$centerY = $this->y() ? $this->y() : $this->getParameterValue('centerY');
			$scale = $this->scale() ? $this->scale() : $this->getParameterValue('scale');

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('width', $this->getParameterValue('width'))->
				set('height', $this->getParameterValue('height'))->
				set('centerX', $centerX)->
				set('centerY', $centerY)->
				set('scale', $scale)->
				set('value', $this->value)->
				set('x', $this->x())->
				set('y', $this->y())->
				set('coords', $this->x(3).', '.$this->y(3));

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}
	}
?>
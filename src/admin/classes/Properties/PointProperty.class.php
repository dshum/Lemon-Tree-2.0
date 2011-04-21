<?php
	final class PointProperty extends BaseProperty
	{
		private $x = null, $y = null;

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			try {
				list($this->x, $this->y) = explode(',', $this->value);
			} catch (BaseException $e) {
				$this->value = null;
			}
		}

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('map', 'string', 'Изображение карты', '/i/map.gif');
			$this->addParameter('point', 'string', 'Изображение точки', '/i/point.gif');

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

		public function x()
		{
			return (int)$this->x;
		}

		public function y()
		{
			return (int)$this->y;
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('x', $this->x())->
				set('y', $this->y())->
				set('value', $this->value());

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementView()
		{
			$pointImageRelPath = $this->getParameterValue('point');
			$mapImageRelPath = $this->getParameterValue('map');

			$pointImageAbsPath = DOCUMENT_ROOT.$pointImageRelPath;
			$mapImageAbsPath = DOCUMENT_ROOT.$mapImageRelPath;

			if($pointImageRelPath && file_exists($pointImageAbsPath)) {
				$pointImagePath = PATH_WEB.$pointImageRelPath;
			} else {
				$pointImageAbsPath = PATH_IMG.'point.gif';
				$pointImagePath = PATH_WEB_IMG.'point.gif';
			}

			if($mapImageRelPath && file_exists($mapImageAbsPath)) {
				$mapImagePath = PATH_WEB.$mapImageRelPath;
				list(
					$mapImageWidth,
					$mapImageHeight,
					$mapImageType,
					$mapImageAttr
				) = getimagesize($mapImageAbsPath);
			} else {
				$mapImageAbsPath = PATH_IMG.'0.gif';
				$mapImagePath = PATH_WEB_IMG.'0.gif';
				$mapImageWidth = 600;
				$mapImageHeight = 400;
			}

			list(
				$pointImageWidth,
				$pointImageHeight,
				$pointImageType,
				$pointImageAttr
			) = getimagesize($pointImageAbsPath);

			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $this->value)->
				set('x', $this->x())->
				set('y', $this->y())->
				set('pointImagePath', $pointImagePath)->
				set('mapImagePath', $mapImagePath)->
				set('pointImageWidth', $pointImageWidth)->
				set('pointImageHeight', $pointImageHeight)->
				set('mapImageWidth', $mapImageWidth)->
				set('mapImageHeight', $mapImageHeight);

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}
	}
?>
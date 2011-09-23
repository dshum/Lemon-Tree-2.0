<?php
	final class ImageProperty extends BaseProperty
	{
		private static $allowedMimeTypes = array(
			'image/png',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
		);
		private static $thumbnail_prefix = 'thumbnail_';
		private static $dir_mod = 0755;
		private static $file_mod = 0644;

		private $folder = null;

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$item = $property->getItem();
			$itemClass = $item->getClass();
			$this->folder = $itemClass->dao()->getTable().DIRECTORY_SEPARATOR;
		}

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('maxFilesizeKb', 'integer', 'Максимальный размер файла (Кб)', 2048);
			$this->addParameter('maxWidth', 'integer', 'Максимальная ширина изображения (пиксели)', 800);
			$this->addParameter('maxHeight', 'integer', 'Максимальная высота изображения (пиксели)', 600);
			$this->addParameter('keepOriginalName', 'boolean', 'Не изменять название файла', false);
			$this->addParameter('resizeWidth', 'integer', 'Изменить ширину изображения до', 0);
			$this->addParameter('resizeHeight', 'integer', 'Изменить высоту изображения до', 0);
			$this->addParameter('thumbnailWidth', 'integer', 'Ширина тумбнайла', 0);
			$this->addParameter('thumbnailHeight', 'integer', 'Высота тумбнайла', 0);
			$this->addParameter('jpegQuality', 'integer', 'Качество JPG, %', 60);

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
			$maxFilesizeKb = $this->getParameterValue('maxFilesizeKb');
			$maxWidth = $this->getParameterValue('maxWidth');
			$maxHeight = $this->getParameterValue('maxHeight');

			return
				$form->
				add(
					Primitive::boolean($this->property->getPropertyName().'_drop')
				)->
				add(
					Primitive::image($this->property->getPropertyName())->
					setAllowedMimeTypes(self::$allowedMimeTypes)->
					setMax($maxFilesizeKb * 1024)->
					setMaxWidth($maxWidth)->
					setMaxHeight($maxHeight)
				);
		}

		public function isUpdate()
		{
			return true;
		}

		public function path()
		{
			return PATH_WEB_LTDATA.$this->folder.$this->value;
		}

		public function abspath()
		{
			return PATH_LTDATA.$this->folder.$this->value;
		}

		public function relpath()
		{
			return DIRECTORY_SEPARATOR.FOLDER_LTDATA.DIRECTORY_SEPARATOR.$this->folder.$this->value;
		}

		public function src()
		{
			return $this->path();
		}

		public function width()
		{
			if($this->value && file_exists($this->abspath())) {
				try {
					list($width, $height, $type, $attr) = getimagesize($this->abspath());
					return $width;
				} catch (BaseException $e) {
					return 0;
				}
			} else {
				return 0;
			}
		}

		public function height()
		{
			if($this->value && file_exists($this->abspath())) {
				try {
					list($width, $height, $type, $attr) = getimagesize($this->abspath());
					return $height;
				} catch (BaseException $e) {
					return 0;
				}
			} else {
				return 0;
			}
		}

		public function exists()
		{
			return $this->value && file_exists($this->abspath());
		}

		public function filename()
		{
			return $this->value;
		}

		public function filesize()
		{
			return $this->exists() ? filesize($this->abspath()) : 0;
		}

		public function filesize_kb($precision = 0)
		{
			return round($this->filesize() / 1024, $precision);
		}

		public function filesize_mb($precision = 0)
		{
			return round($this->filesize() / 1024 / 1024, $precision);
		}

		public function thumbnail_path()
		{
			return PATH_WEB_LTDATA.$this->folder.self::$thumbnail_prefix.$this->value;
		}

		public function thumbnail_abspath()
		{
			return PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$this->value;
		}

		public function thumbnail_src()
		{
			return $this->thumbnail_path();
		}

		public function thumbnail_width()
		{
			try {
				list($width, $height, $type, $attr) = getimagesize($this->thumbnail_abspath());
				return $width;
			} catch (BaseException $e) {
				return 0;
			}
		}

		public function thumbnail_height()
		{
			try {
				list($width, $height, $type, $attr) = getimagesize($this->thumbnail_abspath());
				return $height;
			} catch (BaseException $e) {
				return 0;
			}
		}

		public function thumbnail_exists()
		{
			return $this->value && file_exists($this->thumbnail_abspath());
		}

		public function thumbnail_filename()
		{
			return self::$thumbnail_prefix.$this->value;
		}

		public function thumbnail_filesize()
		{
			return $this->thumbnail_exists() ? filesize($this->thumbnail_abspath()) : 0;
		}

		public function thumbnail_filesize_kb($precision = 0)
		{
			return round($this->thumbnail_filesize() / 1024, $precision);
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('value', $this->value)->
				set('src', $this->src());

			$viewName = 'properties/'.get_class($this).'.elementList';

			return $this->render($model, $viewName);
		}

		public function getEditElementView()
		{
			$model =
				Model::create()->
				set('propertyName', $this->property->getPropertyName())->
				set('propertyDescription', $this->property->getPropertyDescription())->
				set('value', $this->value)->
				set('exists', $this->exists())->
				set('path', $this->path())->
				set('width', $this->width())->
				set('height', $this->height())->
				set('filesize_kb', $this->filesize_kb(1))->
				set('filename', $this->filename())->
				set('thumbnail_exists', $this->thumbnail_exists())->
				set('thumbnail_path', $this->thumbnail_path())->
				set('thumbnail_width', $this->thumbnail_width())->
				set('thumbnail_height', $this->thumbnail_height())->
				set('thumbnail_filesize_kb', $this->thumbnail_filesize_kb(1))->
				set('thumbnail_filename', $this->thumbnail_filename())->
				set('maxFilesizeKb', $this->getParameterValue('maxFilesizeKb'))->
				set('maxWidth', $this->getParameterValue('maxWidth'))->
				set('maxHeight', $this->getParameterValue('maxHeight'));

			$viewName = 'properties/'.get_class($this).'.editElement';

			return $this->render($model, $viewName);
		}

		public function set(Form $form)
		{
			$file = $form->getValue($this->property->getPropertyName());
			$drop = $form->getValue($this->property->getPropertyName().'_drop');

			if($drop) {

				$this->drop();
				$setter = $this->property->setter();
				$this->element->$setter(null);

			} elseif($file) {

				$this->drop();

				$primitive = $form->get($this->property->getPropertyName());
				$name = $primitive->getOriginalName();
				$ext = $this->getExtension($name);

				if($this->getParameterValue('keepOriginalName')) {
					$filename = RussianTextUtils::toTranslit($name);
				} elseif($this->value) {
					$array = explode(".", $this->value);
					$filename = array_shift($array).'.'.$ext;
				} else {
					$filename = sprintf(
						'%s_%s.%s',
						$this->property->getPropertyName(),
						substr(md5(rand()), 0, 8),
						$ext
					);
				}

				try {

					if(!file_exists(PATH_LTDATA.$this->folder)) {
						mkdir(PATH_LTDATA.$this->folder, self::$dir_mod);
					}

					$jpegQuality = $this->getParameterValue('jpegQuality');

					$thumbnailWidth = $this->getParameterValue('thumbnailWidth');
					$thumbnailHeight = $this->getParameterValue('thumbnailHeight');

					if(
						$thumbnailWidth > 0
						|| $this->getParameterValue('thumbnailHeight') > 0
					) {
						ImageUtils::resizeAndCopyImage(
							$file,
							PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$filename,
							$thumbnailWidth,
							$thumbnailHeight,
							$jpegQuality
						);
						chmod(
							PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$filename,
							self::$file_mod
						);
					}

					$resizeWidth = $this->getParameterValue('resizeWidth');
					$resizeHeight = $this->getParameterValue('resizeHeight');
					$resizeQuality = $this->getParameterValue('resizeQuality');

					if(
						$resizeWidth > 0
						|| $resizeHeight > 0
					) {
						ImageUtils::resizeAndCopyImage(
							$file,
							PATH_LTDATA.$this->folder.$filename,
							$resizeWidth,
							$resizeHeight,
							$jpegQuality
						);
						chmod(
							PATH_LTDATA.$this->folder.$filename,
							self::$file_mod
						);
						unlink($file);
					} else {
						$primitive->copyToPath(
							PATH_LTDATA.$this->folder.$filename
						);
						chmod(
							PATH_LTDATA.$this->folder.$filename,
							self::$file_mod
						);
					}

					$setter = $this->property->setter();
					$this->element->$setter($filename);

				} catch (BaseException $e) {}
			}
		}

		public function drop()
		{
			try {
				if(file_exists(PATH_LTDATA.$this->folder.$this->value)) {
					unlink(PATH_LTDATA.$this->folder.$this->value);
				}
				if(file_exists(PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$this->value)) {
					unlink(PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$this->value);
				}
			} catch (BaseException $e) {}
		}

		private function getExtension($name)
		{
			$array = explode(".", $name);
			return (sizeof($array) > 1) ? strtolower(array_pop($array)) : null;
		}
	}
?>
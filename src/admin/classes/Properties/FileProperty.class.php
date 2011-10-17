<?php
	final class FileProperty extends BaseProperty
	{
		private static $allowedMimeTypes = array(
			'text/plain',
			'application/pdf',
			'application/vnd.ms-excel', 'application/octet-stream', 'application/x-excel',
			'application/vnd.ms-powerpoint', 'application/msword',
			'image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/vnd.microsoft.icon',
			'application/x-shockwave-flash',
			'audio/mpeg', 'audio/x-ms-wma', 'audio/vnd.rn-realaudio', 'audio/x-wav',
			'video/mpeg', 'video/mp4', 'video/quicktime', 'video/x-ms-wmv',
			'application/zip', 'application/x-rar-compressed', 'application/x-tar',
		);
		private static $dir_mod = 0755;
		private static $file_mod = 0644;

		private $folder = null;

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$item = $property->getItem();
			$itemClass = $item->getClass();
			$this->folder = $itemClass->dao()->getTable();
		}

		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('maxFilesizeKb', 'integer', 'Максимальный размер файла (Кб)', 2048);
			$this->addParameter('keepOriginalName', 'boolean', 'Не изменять название файла', false);

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

			return
				$form->
				add(
					Primitive::boolean($this->property->getPropertyName().'_drop')
				)->
				add(
					Primitive::file($this->property->getPropertyName())->
					setAllowedMimeTypes(self::$allowedMimeTypes)->
					setMax($maxFilesizeKb * 1024)
				);
		}

		public function isUpdate()
		{
			return true;
		}

		public function path()
		{
			return PATH_WEB_LTDATA.$this->folder.'/'.$this->value;
		}

		public function abspath()
		{
			return PATH_LTDATA.$this->folder.DIRECTORY_SEPARATOR.$this->value;
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

		public function exists()
		{
			return $this->value && file_exists($this->abspath());
		}

		public function getElementListView()
		{
			$model =
				Model::create()->
				set('value', $this->value)->
				set('path', $this->path());

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
				set('filesize_kb', $this->filesize_kb(1))->
				set('filename', $this->filename())->
				set('maxFilesizeKb', $this->getParameterValue('maxFilesizeKb'));

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
					$array = explode('.', $this->value);
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

					$primitive->copyToPath(
						PATH_LTDATA.$this->folder.DIRECTORY_SEPARATOR.$filename
					);
					chmod(
						PATH_LTDATA.$this->folder.DIRECTORY_SEPARATOR.$filename,
						self::$file_mod
					);

					$setter = $this->property->setter();
					$this->element->$setter($filename);

				} catch (BaseException $e) {}
			}
		}

		public function drop()
		{
			if(file_exists(PATH_LTDATA.$this->folder.DIRECTORY_SEPARATOR.$this->value)) {
				try {
					unlink(PATH_LTDATA.$this->folder.DIRECTORY_SEPARATOR.$this->value);
				} catch (BaseException $e) {}
			}
		}

		private function getExtension($name)
		{
			$array = explode(".", $name);
			return (sizeof($array) > 1) ? strtolower(array_pop($array)) : null;
		}
	}
?>
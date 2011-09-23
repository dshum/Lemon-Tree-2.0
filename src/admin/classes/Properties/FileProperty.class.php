<?php
	final class FileProperty extends BaseProperty
	{
		private $folder = null;
		private static $allowedMimeTypes = array(
			'text/plain',
			'application/pdf',
			'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/msword',
			'image/gif', 'image/jpeg', 'image/png', 'image/tiff', 'image/vnd.microsoft.icon',
			'application/x-shockwave-flash',
			'audio/mpeg', 'audio/x-ms-wma', 'audio/vnd.rn-realaudio', 'audio/x-wav',
			'video/mpeg', 'video/mp4', 'video/quicktime', 'video/x-ms-wmv',
			'application/zip', 'application/x-rar-compressed', 'application/x-tar',
		);

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::VARCHAR)->setSize(255);

			$this->folder = $this->property->getItem()->getDefaultTableName().DIRECTORY_SEPARATOR;

			$this->addParameter('maxFilesizeKb', 'integer', 'Максимальный размер файла (Кб)', 2048);
			$this->addParameter('keepOriginalName', 'boolean', 'Не изменять название файла', false);
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
			return PATH_WEB_LTDATA.$this->folder.$this->value;
		}

		public function abspath()
		{
			return PATH_LTDATA.$this->folder.$this->value;
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

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':<br>';
			if($this->exists()) {
				$str .= '<span class="mini">Загружен файл: <a class="dark_grey" href="'.$this->path().'" target="_blank">'.$this->value.'</a>, '.$this->filesize_kb(1).' Кб</span><br>';
			}
			$str .= '<script type="text/javascript">';
			$str .= '$(function() {';
			$str .= '$(\'input:file[name='.$this->property->getPropertyName().']\').change(function() {';
			$str .= '$(\'input:checkbox[name='.$this->property->getPropertyName().'_drop]\').each(function() {this.checked = false;});';
			$str .= '});';
			$str .= '$(\'input:checkbox[name='.$this->property->getPropertyName().'_drop]\').click(function() {';
			$str .= 'if(this.checked) {';
			$str .= '$(\'input:file[name='.$this->property->getPropertyName().']\').val(\'\');';
			$str .= '}';
			$str .= '});';
			$str .= '});';
			$str .= '</script>';
			$str .= '<input type="file" name="'.$this->property->getPropertyName().'" value="'.$this->value.'" class="file-field"><br>';
			if($this->getParameterValue('maxFilesizeKb') > 0) {
				$str .= '<small class="red">Максимальный размер файла '.$this->getParameterValue('maxFilesizeKb').' Кб</small><br>';
			}
			if($this->exists()) {
				$str .= '<div class="file_del_check"><input type="checkbox" id="'.$this->property->getPropertyName().'_drop" name="'.$this->property->getPropertyName().'_drop" value="1" title="Удалить"><label for="'.$this->property->getPropertyName().'_drop">Удалить</label></div>';
			}
			$str .= '<br>';
			return $str;
		}

		public function printOnElementList()
		{
			if($this->value) {
				$str = '<a href="'.$this->path().'" target="_blank">'.$this->value.'</a>';
				return $str;
			} else {
				return null;
			}
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
						mkdir(PATH_LTDATA.$this->folder, 0755);
					}

					$primitive->copyTo(PATH_LTDATA.$this->folder, $filename);

					$setter = $this->property->setter();
					$this->element->$setter($filename);

				} catch (BaseException $e) {}
			}
		}

		public function drop()
		{
			if(file_exists(PATH_LTDATA.$this->folder.$this->value)) {
				try {
					unlink(PATH_LTDATA.$this->folder.$this->value);
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
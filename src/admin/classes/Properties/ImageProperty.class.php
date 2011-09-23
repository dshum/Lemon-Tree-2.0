<?php
	final class ImageProperty extends BaseProperty
	{
		private $folder = null;
		private static $allowedMimeTypes = array(
			'image/png',
			'image/gif',
			'image/jpeg'
		);
		private static $thumbnail_prefix = 'thumbnail_';
		private static $dir_mod = 0755;
		private static $file_mod = 0644;

		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::VARCHAR)->setSize(255);

			$this->folder = $this->property->getItem()->getDefaultTableName().DIRECTORY_SEPARATOR;

			$this->addParameter('maxFilesizeKb', 'integer', 'Максимальный размер файла (Кб)', 2048);
			$this->addParameter('maxWidth', 'integer', 'Максимальная ширина изображения (пиксели)', 800);
			$this->addParameter('maxHeight', 'integer', 'Максимальная высота изображения (пиксели)', 600);
			$this->addParameter('keepOriginalName', 'boolean', 'Не изменять название файла', false);
			$this->addParameter('resizeWidth', 'integer', 'Изменить ширину изображения до', 0);
			$this->addParameter('resizeHeight', 'integer', 'Изменить высоту изображения до', 0);
			$this->addParameter('thumbnailWidth', 'integer', 'Ширина тумбнайла', 0);
			$this->addParameter('thumbnailHeight', 'integer', 'Высота тумбнайла', 0);
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

		public function thumbnail_filename()
		{
			return self::$thumbnail_prefix.$this->value;
		}

		public function thumbnail_filesize()
		{
			return $this->exists() ? filesize($this->thumbnail_abspath()) : 0;
		}

		public function thumbnail_filesize_kb($precision = 0)
		{
			return round($this->thumbnail_filesize() / 1024, $precision);
		}

		public function thumbnail_exists()
		{
			return $this->value && file_exists($this->thumbnail_abspath());
		}

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':<br>';
			if($this->exists()) {
				$str .= '<table><tr valign="top">';
				$str .= '<td>';
				$str .= '<span class="mini">Загружено изображение: <a class="dark_grey" href="'.$this->path().'" target="_blank">'.$this->value.'</a>, <span title="Размер изображения">'.$this->width().'&#215;'.$this->height().'</span> пикселов, '.$this->filesize_kb(1).' Кб</span><br>';
				$str .= '<img class="pict" src="'.$this->path().'?random='.md5(rand()).'" width="'.$this->width().'" height="'.$this->height().'" alt=""><br>';
				$str .= '</td>';
				if($this->thumbnail_exists()) {
					$str .= '<td>';
					$str .= '<span class="mini">Загружен тумбнайл: <a class="dark_grey" href="'.$this->thumbnail_path().'" target="_blank">'.self::$thumbnail_prefix.$this->value.'</a>, <span title="Размер изображения">'.$this->thumbnail_width().'&#215;'.$this->thumbnail_height().'</span> пикселов, '.$this->thumbnail_filesize_kb(1).' Кб</span><br>';
					$str .= '<img class="pict" src="'.$this->thumbnail_path().'?random='.md5(rand()).'" width="'.$this->thumbnail_width().'" height="'.$this->thumbnail_height().'" alt=""><br>';
					$str .= '</td>';
				}
				$str .= '</table>';
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
			if($this->getParameterValue('maxWidth') > 0 && $this->getParameterValue('maxHeight') > 0) {
				$str .= '<small class="red">Максимальный размер изображения '.$this->getParameterValue('maxWidth').'&#215;'.$this->getParameterValue('maxHeight').' пикселей</small><br>';
			} elseif($this->getParameterValue('maxWidth') > 0) {
				$str .= '<small class="red">Максимальная ширина изображения '.$this->getParameterValue('maxWidth').' пикселей</small><br>';
			} elseif($this->getParameterValue('maxHeight') > 0) {
				$str .= '<small class="red">Максимальная высота изображения '.$this->getParameterValue('maxHeight').' пикселей</small><br>';
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
				$str = '<img src="'.$this->src().'" alt="'.$this->value.'">';
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
						mkdir(PATH_LTDATA.$this->folder, self::$dir_mod);
					}

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
							$thumbnailHeight
						);
						chmod(
							PATH_LTDATA.$this->folder.self::$thumbnail_prefix.$filename,
							self::$file_mod
						);
					}

					$resizeWidth = $this->getParameterValue('resizeWidth');
					$resizeHeight = $this->getParameterValue('resizeHeight');

					if(
						$resizeWidth > 0
						|| $resizeHeight > 0
					) {
						ImageUtils::resizeAndCopyImage(
							$file,
							PATH_LTDATA.$this->folder.$filename,
							$resizeWidth,
							$resizeHeight
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
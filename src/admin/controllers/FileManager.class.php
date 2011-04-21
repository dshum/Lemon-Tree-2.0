<?php
	final class FileManager extends MethodMappedController
	{
		private $rootDir = null;
		private $allowedTypes = array(
			'media' => array("avi", "wmv", "mpg", "mpeg", "wav", "wma", "mid", "mp3", "swf"),
			'image' => array("gif", "jpg", "jpeg", "png"),
			'document' => array("doc", "rtf", "xls", "csv", "pdf", "ppt", "txt", "fb2"),
		);

		public function __construct()
		{
			$this->rootDir = PATH_LTDATA.TABLE_PREFIX.'file_manager'.DIRECTORY_SEPARATOR;

			$this->
			setMethodMapping('removeDir', 'removeDir')->
			setMethodMapping('makeDir', 'makeDir')->
			setMethodMapping('upload', 'upload')->
			setMethodMapping('drop', 'drop')->
			setMethodMapping('show', 'show')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function show(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::string('url')->
				setDefault('')
			)->
			add(
				Primitive::string('type')->
				setDefault('')
			)->
			add(
				Primitive::string('folder')->
				setDefault('')
			)->
			import($request->getGet());

			$url = $form->getActualValue('url');
			$type = $form->getActualValue('type');
			$folder = $form->getActualValue('folder');

			$parentList = $this->getParentList($folder);
			$dirList = $this->getDirList($folder);
			$fileList = $this->getFileList($folder, $type);

			$model->set('url', $url);
			$model->set('type', $type);
			$model->set('folder', $folder);
			$model->set('parentList', $parentList);
			$model->set('dirList', $dirList);
			$model->set('fileList', $fileList);

			return ModelAndView::create()->
				setModel($model)->
				setView('FileManager');
		}

		private function getParentList($folder)
		{
			if(!$folder) return array();

			$parentList = explode(DIRECTORY_SEPARATOR, $folder);

			return $parentList;
		}

		private function getDirList($folder)
		{
			$dirList = array();

			$dir = opendir($this->rootDir.$folder);

			while($filename = readdir($dir)) {
				if($filename == '.' || $filename == '..') continue;

				if(is_dir($this->rootDir.$folder.DIRECTORY_SEPARATOR.$filename)) {
					$dirList[] = $filename;
				}
			}

			sort($dirList);

			return $dirList;
		}

		private function getFileList($folder, $type)
		{
			$fileList = array();

			$dir = opendir($this->rootDir.$folder);

			while($filename = readdir($dir)) {
				if($filename == '.' || $filename == '..') continue;

				if(is_dir($this->rootDir.$folder.DIRECTORY_SEPARATOR.$filename)) continue;

				if(
					$type
					&& is_array($this->allowedTypes[$type])
					&& !in_array(
						$this->getExtension($filename),
						$this->allowedTypes[$type]
					)
				) continue;

				$fileList[] = $filename;
			}

			sort($fileList);

			return $fileList;
		}

		private function getExtension($name)
		{
			$array = explode(".", $name);
			return (sizeof($array) > 1) ? strtolower(array_pop($array)) : null;
		}
	}
?>
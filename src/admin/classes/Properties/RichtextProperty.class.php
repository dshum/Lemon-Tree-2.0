<?php
	final class RichtextProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);

			$this->dataType = DataType::create(DataType::TEXT);

			$this->addParameter('typograph', 'boolean', 'Типографика', true);
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" required="false" />';
		}

		public function add2form(Form $form)
		{
			$primitive = Primitive::string($this->property->getPropertyName())->
				setMax(65536);
			$primitive->addImportFilter(Filter::trim());
			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(RussianTypograph::me());
			}
			return $form->add($primitive);
		}

		public function editOnElement()
		{
			$str = $this->property->getPropertyDescription().':<br>';
			$str .= '<table class="ie"><tr><td><textarea id="'.$this->property->getPropertyName().'" name="'.$this->property->getPropertyName().'" class="prop" style="height: 350px;" tinymce="true">'.$this->value.'</textarea></td></tr></table><br>';
			$str .= <<<JS

<script type="text/javascript">
	tinyMCE.init({
		mode : "none",
		language : "ru",
		theme : "advanced",
		plugins : "inlinepopups,style,table,advhr,advimage,advlink,media,searchreplace,print,paste,fullscreen,visualchars,xhtmlxtras",

		theme_advanced_buttons1 : "newdocument,search,replace,print,|,cut,copy,paste,pastetext,pasteword,|,undo,redo,|,link,unlink,anchor,image,media,|,advhr,charmap,|,tablecontrols",
		theme_advanced_buttons2 : "styleprops,attribs,removeformat,|,styleselect,formatselect,|,bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,visualchars,code",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_path_location : "bottom",

		content_css : "/f/style.css",
		file_browser_callback : "myFileBrowser",
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing : false,
		forced_root_block : false,
		apply_source_formatting: true,
		nonbreaking_force_tab : true,
		button_tile_map : true,
		entity_encoding : "raw",
		verify_html : false,
		convert_urls : false
	});
	tinyMCE.execCommand('mceAddControl', false, '{$this->property->getPropertyName()}');
</script>

JS;
			return $str;
		}
	}
?>
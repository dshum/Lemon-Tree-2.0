<?php
	final class RichtextProperty extends BaseProperty
	{
		public function setParameters()
		{
			parent::setParameters();

			$this->addParameter('defaultCss', 'string', 'Файл CSS', '/f/style.css');
			$this->addParameter('typograph', 'boolean', 'Типографика', true);

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::TEXT);
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
				$primitive->addImportFilter(MyRussianTypograph::me());
			}
			return $form->add($primitive);
		}
	}
?>
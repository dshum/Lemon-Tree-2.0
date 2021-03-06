<?php
	final class RichtextProperty extends BaseProperty
	{
		const SIZE = 65536;

		public function setParameters()
		{
			parent::setParameters();

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
			$primitive =
				Primitive::string($this->property->getPropertyName())->
				setMax(self::SIZE)->
				addImportFilter(Filter::trim());

			if($this->getParameterValue('typograph')) {
				$primitive->addImportFilter(RussianTypograph::me());
			}

			return $form->add($primitive);
		}
	}
?>
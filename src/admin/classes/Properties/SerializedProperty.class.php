<?php
	final class SerializedProperty extends BaseProperty
	{
		const SIZE = 16777216;

		public function setParameters()
		{
			parent::setParameters();

			return $this;
		}

		public function getDataType()
		{
			return DataType::create(DataType::TEXT)->setName('MEDIUMBLOB');
		}

		public function meta()
		{
			return '<property name="'.$this->property->getPropertyName().'" type="String" required="false" />';
		}

		public function isUpdate()
		{
			return true;
		}

		public function set(Form $form)
		{
			return false;
		}

		public function unserialize()
		{
			if(!$this->value) return null;

			try {
				return unserialize($this->value);
			} catch (BaseException $e) {}

			return false;
		}
	}
?>
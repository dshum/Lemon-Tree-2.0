<?php
	final class OneToManyProperty extends BaseProperty
	{
		public function __construct($property, $element)
		{
			parent::__construct($property, $element);
		}

		public function meta()
		{
			$fetchClass = $this->property->getFetchClass();
			$type = $fetchClass ? $fetchClass : 'Integer';
			return '<property name="'.$this->property->getPropertyName().'" type="'.$type.'" relation="OneToMany" />';
		}

		public function column()
		{
			return null;
		}

		public function add2form(Form $form)
		{
			return $form;
		}

		public function editOnElement()
		{
			return null;
		}

		public function printOnElementList()
		{
			return null;
		}

		public function editOnElementList()
		{
			return null;
		}
	}
?>
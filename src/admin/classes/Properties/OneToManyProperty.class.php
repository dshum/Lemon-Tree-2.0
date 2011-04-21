<?php
	final class OneToManyProperty extends BaseProperty
	{
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

		public function getEditElementView()
		{
			return null;
		}

		public function getElementListView()
		{
			return null;
		}

		public function getEditElementListView()
		{
			return null;
		}

		public function getElementSearchView()
		{
			return null;
		}
	}
?>
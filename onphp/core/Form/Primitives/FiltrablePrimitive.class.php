<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id: FiltrablePrimitive.class.php 5127 2008-05-02 10:36:56Z voxus $ */

	/**
	 * Basis for Primitives which can be filtered.
	 * 
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	abstract class FiltrablePrimitive extends RangedPrimitive
	{
		private $importFilter	= null;
		private $displayFilter 	= null;
		
		public function __construct($name)
		{
			parent::__construct($name);
			
			$this->displayFilter = new FilterChain();
			$this->importFilter = new FilterChain();
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function setDisplayFilter(FilterChain $chain)
		{
			$this->displayFilter = $chain;
			
			return $this;
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function addDisplayFilter(Filtrator $filter)
		{
			$this->displayFilter->add($filter);
			
			return $this;
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function dropDisplayFilters()
		{
			$this->displayFilter = new FilterChain();
			
			return $this;
		}
		
		/**
		 * @deprecated by getFormValue
		**/
		public function getDisplayValue()
		{
			if (is_array($value = $this->getActualValue())) {
				foreach ($value as &$element)
					$element = $this->displayFilter->apply($element);
				
				return $value;
			}
			
			return $this->displayFilter->apply($value);
		}
		
		public function getFormValue()
		{
			return $this->displayFilter->apply(
				parent::getFormValue()
			);
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function setImportFilter(FilterChain $chain)
		{
			$this->importFilter = $chain;
			
			return $this;
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function addImportFilter(Filtrator $filter)
		{
			$this->importFilter->add($filter);
			
			return $this;
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		public function dropImportFilters()
		{
			$this->importFilter = new FilterChain();
			
			return $this;
		}
		
		/**
		 * @return FilterChain
		**/
		public function getImportFilter()
		{
			return $this->importFilter;
		}
		
		/**
		 * @return FilterChain
		**/
		public function getDisplayFilter()
		{
			return $this->displayFilter;
		}
		
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			if (!is_array($scope[$this->name])) {
				$value = (string) $scope[$this->name];
				$this->applyImportFilters($value);
				$scope[$this->name] = $value;
			} else {
				$this->applyImportFilters($scope[$this->name]);
			}
			
			return parent::import($scope);
		}
		
		/**
		 * @return FiltrablePrimitive
		**/
		protected function applyImportFilters(&$value)
		{
			if (is_array($value))
				foreach ($value as &$element)
					$element = $this->importFilter->apply($element);
			else
				$value = $this->importFilter->apply($value);
			
			return $this;
		}
	}
?>
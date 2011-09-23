<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: PrimitivePolymorphicIdentifier.class.php 5104 2008-05-02 10:34:55Z voxus $ */

	/**
	 * Hint: use raw values like 'City.42' or 'Country.42' where City and
	 * Country are childrens of base class GeoLocation, for example.
	**/
	final class PrimitivePolymorphicIdentifier extends PrimitiveIdentifier
	{
		const WRONG_CID_FORMAT	= 201;
		const WRONG_CLASS		= 202;
		
		const DELIMITER			= '.';
		
		private $baseClassName	= null;
		
		public static function export($value)
		{
			if ($value === null)
				return null;
			
			Assert::isInstance($value, 'Identifiable');
			
			return get_class($value).self::DELIMITER.$value->getId();
		}
			
		/**
		 * @throws WrongStateException
		**/
		public function of($class)
		{
			throw new WrongStateException(
				'of() must not be called directly, use ofBase()'
			);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitivePolymorphicIdentifier
		**/
		public function ofBase($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			Assert::isTrue(
				ClassUtils::isInstanceof($className, 'DAOConnected'),
				"class '{$className}' must implement DAOConnected interface"
			);
			
			$this->baseClassName = $className;
			
			return $this;
		}
		
		public function getBaseClassName()
		{
			return $this->baseClassName;
		}
		
		public function setValue($value)
		{
			Assert::isInstance($value, $this->baseClassName);
			
			parent::of(get_class($value));
			
			return parent::setValue($value);
		}
		
		public function exportValue()
		{
			if ($this->value === null)
				return null;
			
			return self::export($this->value);
		}
		
		public function importValue($value)
		{
			return $this->import(
				array(
					$this->getName() => self::export($value)
				)
			);
		}
		
		public function import(array $scope)
		{
			$savedRaw = null;
			
			if (isset($scope[$this->name]) && $scope[$this->name]) {
				$savedRaw = $scope[$this->name];
				
				$this->error = null;
				
				try {
					
					list($class, $id) = explode(self::DELIMITER, $savedRaw, 2);
					
				} catch (BaseException $e) {
					
					$this->setError(self::WRONG_CID_FORMAT);
					
				}
				
				if (
					!$this->error
					&& !ClassUtils::isInstanceOf($class, $this->baseClassName)
				) {
					
					$this->setError(self::WRONG_CLASS);
					
				}
				
				
				if (!$this->error) {
					parent::of($class);
				
					$scope[$this->name] = $id;
				}
				
			} else {
				// we need some class in any case
				parent::of($this->baseClassName);
			}
			
			if (!$this->error)
				$result = parent::import($scope);
			else {
				$this->value = null;
				$result = false;
			}
			
			if ($savedRaw)
				$this->raw = $savedRaw;
			
			return $result;
		}
	}
?>
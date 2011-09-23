<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id: PrimitiveArray.class.php 5124 2008-05-02 10:36:39Z voxus $ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveArray extends FiltrablePrimitive
	{
		/**
		 * Fetching strategy for incoming containers:
		 * 
		 * null - do nothing;
		 * true - lazy fetch;
		 * false - full fetch.
		**/
		private $fetchMode = null;
		
		public function getTypeName()
		{
			return 'HashMap';
		}
		
		public function isObjectType()
		{
			return false;
		}
		
		/**
		 * @return PrimitiveArray
		**/
		public function setFetchMode($ternary)
		{
			Assert::isTernaryBase($ternary);
			
			$this->fetchMode = $ternary;
			
			return $this;
		}
		
		public function importValue($value)
		{
			if ($value instanceof UnifiedContainer) {
				if (
					($this->fetchMode !== null)
					&& ($value->getParentObject()->getId())
				) {
					if ($value->isLazy() === $this->fetchMode) {
						$value = $value->getList();
					} else {
						$className = get_class($value);
						
						$containter = new $className(
							$value->getParentObject(),
							$this->fetchMode
						);
						
						$value = $containter->getList();
					}
				} elseif (!$value->isFetched())
					return null;
			}
			
			if (is_array($value))
				return $this->import(array($this->getName() => $value));
			
			return false;
		}
	}
?>
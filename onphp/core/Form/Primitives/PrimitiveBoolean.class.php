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
/* $Id: PrimitiveBoolean.class.php 5104 2008-05-02 10:34:55Z voxus $ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveBoolean extends BasePrimitive
	{
		public function import(array $scope)
		{
			if (isset($scope[$this->name]))
				$this->value = true;
			else
				$this->value = false;
			
			return $this->imported = true;
		}
		
		public function importValue($value)
		{
			if (
				false === $value
				|| null === $value
			)
				$this->value = false;
			else
				$this->value = true;
			
			return $this->imported = true;
		}
		
		public function isImported()
		{
			return ($this->imported && $this->value);
		}
	}
?>
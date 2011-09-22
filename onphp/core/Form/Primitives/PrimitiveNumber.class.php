<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	abstract class PrimitiveNumber extends FiltrablePrimitive
	{
		abstract protected function checkNumber($number);
		abstract protected function castNumber($number);

		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			$this->value = $scope[$this->name];

			$this->selfFilter();

			try {
				$this->checkNumber($this->value);
			} catch (WrongArgumentException $e) {
				return false;
			}

			$this->value = $this->castNumber($this->value);

			if (
				!(null !== $this->min && $this->value < $this->min)
				&& !(null !== $this->max && $this->value > $this->max)
			) {
				return true;
			} else {
				$this->value = null;
			}

			return false;
		}
	}
?>
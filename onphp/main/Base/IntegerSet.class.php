<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: IntegerSet.class.php 5085 2008-05-02 10:33:14Z voxus $ */

	/**
	 * Integer's set.
	 * 
	 * @ingroup Helpers
	**/
	final class IntegerSet extends Range
	{
		public static function create(
			$min = Integer::SIGNED_MIN,
			$max = Integer::SIGNED_MAX
		)
		{
			return new IntegerSet($min, $max);
		}
		
		public function contains($value)
		{
			if (
				$this->getMin() <= $value
				&& $value <= $this->getMax()
			)
				return true;
			else
				return false;
		}
	}
?>
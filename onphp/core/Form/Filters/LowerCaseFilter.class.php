<?php
/***************************************************************************
 *   Copyright (C) 2007 by Vladimir A. Altuchov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: LowerCaseFilter.class.php 3994 2007-08-08 10:11:04Z voxus $ */

	/**
	  * @ingroup Filters
	**/
	final class LowerCaseFilter extends BaseFilter
	{
		/**
		 * @return LowerCaseFilter
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function apply($value)
		{
			return mb_strtolower($value);
		}
	}
?>
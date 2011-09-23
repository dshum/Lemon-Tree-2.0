<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sergey M. Skachkov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: UpperCaseFilter.class.php 4189 2007-09-17 13:32:13Z myserg $ */

	/**
	  * @ingroup Filters
	**/
	final class UpperCaseFilter extends BaseFilter
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
			return mb_strtoupper($value);
		}
	}
?>
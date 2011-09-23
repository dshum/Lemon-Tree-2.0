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
/* $Id: UrlEncodeFilter.class.php 3884 2007-07-27 11:20:30Z voxus $ */

	/**
	 * @ingroup Filters
	**/
	final class UrlEncodeFilter extends BaseFilter
	{
		/**
		 * @return UrlEncodeFilter
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function apply($value)
		{
			return urlencode($value);
		}
	}
?>
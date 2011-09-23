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
/* $Id: NormalizeUrlFilter.class.php 5130 2008-05-02 10:51:30Z voxus $ */

	/**
	 * @see RegulatedPrimitive::addImportFilter()
	 * 
	 * @ingroup Filters
	**/
	final class NormalizeUrlFilter implements Filtrator
	{
		/**
		 * @return NormalizeUrlFilter
		**/
		public static function create()
		{
			return new self;
		}
		
		
		public function apply($value)
		{
			$url =
				HttpUrl::create()->
				parse($value)->
				ensureAbsolute()->
				normalize();
			
			return $url->toString();
		}
	}
?>
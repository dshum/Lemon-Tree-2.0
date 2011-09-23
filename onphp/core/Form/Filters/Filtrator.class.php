<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: Filtrator.class.php 4516 2007-11-06 02:30:06Z voxus $ */

	/**
	 * Interface for primitive's filters.
	 * 
	 * @see FiltrablePrimitive::getDisplayValue()
	 * 
	 * @ingroup Filters
	 * @ingroup Module
	**/
	interface Filtrator
	{
		public function apply($value);
	}
?>
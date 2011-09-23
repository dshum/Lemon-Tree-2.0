<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: TimestampRangeType.class.php 5087 2008-05-02 10:33:29Z voxus $ */

	/**
	 * @ingroup MetaTypes
	**/
	class TimestampRangeType extends DateRangeType
	{
		public function toPrimitive()
		{
			return 'Primitive::timestampRange';
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: SQLRightJoin.class.php 5391 2008-08-04 11:51:28Z voxus $ */

	/**
	 * @ingroup OSQL
	**/
	final class SQLRightJoin extends SQLBaseJoin
	{
		public function toDialectString(Dialect $dialect)
		{
			return parent::baseToString($dialect, 'RIGHT ');
		}
	}
?>
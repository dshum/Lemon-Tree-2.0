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
/* $Id: SQLTableName.class.php 3886 2007-07-27 11:20:41Z voxus $ */

	/**
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	interface SQLTableName extends DialectString
	{
		public function getTable();
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: LogicalObject.class.php 3885 2007-07-27 11:20:34Z voxus $ */

	/**
	 * Support interface for Form's logic rules.
	 * 
	 * @ingroup Logic
	**/
	interface LogicalObject extends DialectString
	{
		public function toBoolean(Form $form);
	}
?>
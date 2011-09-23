<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: ObjectBuilder.class.php 4965 2008-03-21 17:12:14Z dedmajor $ */

	abstract class ObjectBuilder extends PrototypedBuilder
	{
		protected function createEmpty()
		{
			return $this->proto->createObject();
		}
	}
?>
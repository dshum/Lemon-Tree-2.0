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
/* $Id: DTOMessage.class.php 4967 2008-03-21 18:04:30Z dedmajor $ */

	abstract class DTOMessage implements PrototypedEntity
	{
		final public function makeDto()
		{
			return
				ObjectToDTOConverter::create(
					$this->entityProto()
				)->
					make($this);
		}
	}
?>
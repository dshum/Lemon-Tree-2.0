<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: UnsignedSmallIntegerType.class.php 5087 2008-05-02 10:33:29Z voxus $ */

	/**
	 * @ingroup MetaTypes
	**/
	final class UnsignedSmallIntegerType extends SmallIntegerType
	{
		public function getSize()
		{
			return 2 & LightMetaProperty::UNSIGNED_FLAG;
		}
		
		public function toColumnType()
		{
			return
				parent::toColumnType()
				."->\n"
				.'setUnsigned(true)';
		}
	}
?>
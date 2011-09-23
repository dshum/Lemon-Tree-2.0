<?php
/****************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                      *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id: PrimitiveFloat.class.php 5124 2008-05-02 10:36:39Z voxus $ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveFloat extends FiltrablePrimitive
	{
		public function getTypeName()
		{
			return 'Float';
		}
		
		public function isObjectType()
		{
			return false;
		}
	}
?>
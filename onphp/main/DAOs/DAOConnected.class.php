<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: DAOConnected.class.php 3889 2007-07-27 11:20:54Z voxus $ */

	/**
	 * Helper for identifying object's DAO.
	 * 
	 * @ingroup DAOs
	 * @ingroup Module
	**/
	interface DAOConnected extends Identifiable
	{
		public static function dao();
	}
?>
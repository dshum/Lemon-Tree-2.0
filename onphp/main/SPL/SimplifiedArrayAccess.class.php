<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: SimplifiedArrayAccess.class.php 3892 2007-07-27 11:21:02Z voxus $ */

	/**
	 * @ingroup onSPL
	**/
	interface SimplifiedArrayAccess
	{
		public function clean();
		public function isEmpty();
		
		public function getList();
		
		public function set($name, $var);
		public function get($name);
		public function has($name);
		public function drop($name);
	}
?>
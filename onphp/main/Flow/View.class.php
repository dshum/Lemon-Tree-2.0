<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: View.class.php 5212 2008-06-18 16:08:33Z dedmajor $ */

	/**
	 * @ingroup Flow
	**/
	interface View
	{
		const ERROR_VIEW = 'error';
		
		public function render(Model $model = null);
	}
?>
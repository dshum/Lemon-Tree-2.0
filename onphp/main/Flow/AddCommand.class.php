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
/* $Id: AddCommand.class.php 4461 2007-11-04 20:41:33Z voxus $ */

	/**
	 * @ingroup Flow
	**/
	class AddCommand extends MakeCommand
	{
		/**
		 * @return AddCommand
		**/
		public static function create()
		{
			return new self;
		}
		
		protected function daoMethod()
		{
			return 'add';
		}
	}
?>
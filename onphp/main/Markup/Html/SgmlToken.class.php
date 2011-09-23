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
/* $Id: SgmlToken.class.php 4509 2007-11-04 20:45:49Z voxus $ */

	/**
	 * @ingroup Html
	 * @ingroup Module
	**/
	class SgmlToken
	{
		private $value	= null;
		
		/**
		 * @return SgmlToken
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>
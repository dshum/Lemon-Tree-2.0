<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id: BaseException.class.php 4461 2007-11-04 20:41:33Z voxus $ */

	/**
	 * @ingroup Exceptions
	 * @ingroup Module
	**/
	class BaseException extends Exception
	{
		public function __toString()
		{
			return
				"[$this->message] in: \n".
				$this->getTraceAsString();
		}
	}
?>
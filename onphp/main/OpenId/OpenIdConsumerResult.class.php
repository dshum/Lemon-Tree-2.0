<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: OpenIdConsumerResult.class.php 4680 2007-12-05 09:36:39Z voxus $ */

	/**
	 * @ingroup OpenId
	**/
	interface OpenIdConsumerResult
	{
		/**
		 * @return bool
		**/
		public function isOk();
	}
?>
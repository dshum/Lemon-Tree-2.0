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
/* $Id: MailBuilder.class.php 3891 2007-07-27 11:21:01Z voxus $ */

	/**
	 * @ingroup Mail
	**/
	interface MailBuilder
	{
		/// returns encoded body as string
		public function getEncodedBody();
		
		/// returns all related headers as string
		public function getHeaders();
	}
?>
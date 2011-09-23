<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: KeyPair.class.php 5107 2008-05-02 10:35:12Z voxus $ */

	/**
	 * @ingroup Crypto
	**/
	interface KeyPair
	{
		/**
		 * @return ExternalBigInteger
		**/
		public function getPublic();
		
		/**
		 * @return ExternalBigInteger
		**/
		public function getPrivate();
	}
?>
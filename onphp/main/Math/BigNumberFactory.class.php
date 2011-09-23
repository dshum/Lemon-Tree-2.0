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
/* $Id: BigNumberFactory.class.php 5107 2008-05-02 10:35:12Z voxus $ */

	/**
	 * @ingroup Math
	**/
	abstract class BigNumberFactory extends Singleton
	{
		/**
		 * @return ExternalBigInteger
		**/
		abstract public function makeNumber($number, $base = 10);
		
		/**
		 * make number from big-endian signed two's complement binary notation
		 * @return ExternalBigInteger
		**/
		abstract public function makeFromBinary($binary);
		
		/**
		 * @param $stop maximum random number
		 * @return ExternalBigInteger
		**/
		abstract public function makeRandom($stop, RandomSource $source);
	}
?>
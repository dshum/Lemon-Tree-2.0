<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Vladimir A. Altuchov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: IpAddress.class.php 5276 2008-07-09 18:08:13Z voxus $ */

	/**
	 * @ingroup Ip
	**/
	class IpAddress implements Stringable
	{
		private $longIp = null;
		
		/**
		 * @return IpAddress
		**/
		public static function create($ip)
		{
			return new self($ip);
		}
		
		public function __construct($ip)
		{
			$this->setIp($ip);
		}
		
		/**
		 * @return IpAddress
		**/
		public function setIp($ip)
		{
			if (ip2long($ip) === false)
				throw new WrongArgumentException('wrong ip given');
			
			$this->longIp = ip2long($ip);
			
			return $this;
		}
		
		public function getLongIp()
		{
			return $this->longIp;
		}
		
		public function toString()
		{
			return long2ip($this->longIp);
		}
		
		public function toSignedInt()
		{
			$unsignedMax = 4294967295;
			$signedMax = 2147483647;
			
			if ($this->longIp > $signedMax)
				return $this->longIp - $unsignedMax - 1;
			else
				return $this->longIp;
		}
	}
?>
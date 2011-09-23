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
/* $Id: DiffieHellmanKeyPair.class.php 5107 2008-05-02 10:35:12Z voxus $ */

	/**
	 * @see http://tools.ietf.org/html/rfc2631
	 * 
	 * @ingroup Crypto
	**/
	final class DiffieHellmanKeyPair implements KeyPair
	{
		private $private	= null;
		private $public		= null;
		private $parameters	= null;
		
		public function __construct(DiffieHellmanParameters $parameters)
		{
			$this->parameters = $parameters;
		}
		
		/**
		 * @return DiffieHellmanKeyPair
		**/
		public static function create(DiffieHellmanParameters $parameters)
		{
			return new self($parameters);
		}
		
		/**
		 * @return DiffieHellmanKeyPair
		**/
		public static function generate(
			DiffieHellmanParameters $parameters,
			RandomSource $randomSource
		)
		{
			$result = new self($parameters);
			
			$factory = $parameters->getModulus()->getFactory();
			
			$result->private = $factory->makeRandom(
				$parameters->getModulus(),
				$randomSource
			);
			
			$result->public = $parameters->getGen()->modPow(
				$result->private,
				$parameters->getModulus()
			);
			
			return $result;
		}
		
		/**
		 * @return DiffieHellmanKeyPair
		**/
		public function setPrivate(ExternalBigInteger $private)
		{
			$this->private = $private;
			return $this;
		}
		
		/**
		 * @return ExternalBigInteger
		**/
		public function getPrivate()
		{
			return $this->private;
		}
		
		/**
		 * @return DiffieHellmanKeyPair
		**/
		public function setPublic(ExternalBigInteger $public)
		{
			$this->public = $public;
			return $this;
		}
		
		/**
		 * @return ExternalBigInteger
		**/
		public function getPublic()
		{
			return $this->public;
		}
		
		/**
		 * @return ExternalBigInteger
		**/
		public function makeSharedKey(ExternalBigInteger $otherSitePublic)
		{
			Assert::brothers($this->private, $otherSitePublic);
			
			return $otherSitePublic->modPow(
				$this->private,
				$this->parameters->getModulus()
			);
		}
	}
?>
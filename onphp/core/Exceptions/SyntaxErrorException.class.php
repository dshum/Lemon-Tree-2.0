<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id: SyntaxErrorException.class.php 5410 2008-08-11 09:23:57Z voxus $ */

	/**
	 * @ingroup Exceptions
	**/
	final class SyntaxErrorException extends BaseException
	{
		private $errorLine		= null;
		private $errorPosition	= null;
		
		public function __construct(
			$message, $errorLine = null, $errorPosition = null, $code = 0
		)
		{
			parent::__construct($message, $code);
			
			$this->errorLine = $errorLine;
			$this->errorPosition = $errorPosition;
		}
		
		public function getErrorLine()
		{
			return $this->errorLine;
		}
		
		public function getErrorPosition()
		{
			return $this->errorPosition;
		}
		
		public function __toString()
		{
			return
				'[error at line '
				.(Assert::checkInteger($this->errorLine) ? $this->errorLine : 'unknown')
				.', position '
				.(Assert::checkInteger($this->errorPosition) ? $this->errorPosition : 'unknown')
				.": {$this->message}] in: \n".
				$this->getTraceAsString();
		}
	}
?>
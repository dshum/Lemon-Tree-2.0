<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2008-10-10 19:42:46                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoUserDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'cytrus_user';
		}
		
		public function getObjectName()
		{
			return 'User';
		}
		
		public function getSequence()
		{
			return 'cytrus_user_id';
		}
	}
?>
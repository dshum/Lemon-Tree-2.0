<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2008-10-10 19:42:46                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoElementPermissionDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'cytrus_element_permission';
		}
		
		public function getObjectName()
		{
			return 'ElementPermission';
		}
		
		public function getSequence()
		{
			return 'cytrus_element_permission_id';
		}
	}
?>
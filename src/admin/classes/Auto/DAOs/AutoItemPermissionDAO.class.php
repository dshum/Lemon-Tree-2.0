<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2008-10-10 19:42:46                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoItemPermissionDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'cytrus_item_permission';
		}
		
		public function getObjectName()
		{
			return 'ItemPermission';
		}
		
		public function getSequence()
		{
			return 'cytrus_item_permission_id';
		}
	}
?>
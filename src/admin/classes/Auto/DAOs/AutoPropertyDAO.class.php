<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2009-03-10 19:25:37                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoPropertyDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'cytrus_property';
		}
		
		public function getObjectName()
		{
			return 'Property';
		}
		
		public function getSequence()
		{
			return 'cytrus_property_id';
		}
	}
?>
<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2009-03-04 22:45:02                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoProtoGroup extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'identifier', 'Group', 4, true, true, false, null, null),
				'parent' => LightMetaProperty::fill(new LightMetaProperty(), 'parent', 'parent_id', 'identifier', 'Group', 4, false, false, false, 1, 2),
				'groupDescription' => LightMetaProperty::fill(new LightMetaProperty(), 'groupDescription', 'group_description', 'string', null, 255, true, true, false, null, null),
				'ownerPermission' => LightMetaProperty::fill(new LightMetaProperty(), 'ownerPermission', 'owner_permission', 'integer', null, 4, true, true, false, null, null),
				'groupPermission' => LightMetaProperty::fill(new LightMetaProperty(), 'groupPermission', 'group_permission', 'integer', null, 4, true, true, false, null, null),
				'worldPermission' => LightMetaProperty::fill(new LightMetaProperty(), 'worldPermission', 'world_permission', 'integer', null, 4, true, true, false, null, null),
				'isDeveloper' => LightMetaProperty::fill(new LightMetaProperty(), 'isDeveloper', 'is_developer', 'boolean', null, null, true, true, false, null, null),
				'isAdmin' => LightMetaProperty::fill(new LightMetaProperty(), 'isAdmin', 'is_admin', 'boolean', null, null, true, true, false, null, null)
			);
		}
	}
?>
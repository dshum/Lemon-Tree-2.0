<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2009-07-24 14:51:10                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoProtoElement extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'Element', 4, true, true, false, null, null),
				'elementName' => LightMetaProperty::fill(new LightMetaProperty(), 'elementName', 'element_name', 'string', null, 255, true, true, false, null, null),
				'elementOrder' => LightMetaProperty::fill(new LightMetaProperty(), 'elementOrder', 'element_order', 'integer', null, 4, false, true, false, null, null),
				'status' => LightMetaProperty::fill(new LightMetaProperty(), 'status', null, 'string', null, 50, true, true, false, null, null),
				'elementPath' => LightMetaProperty::fill(new LightMetaProperty(), 'elementPath', 'element_path', 'string', null, 255, false, true, false, null, null),
				'group' => LightMetaProperty::fill(new LightMetaProperty(), 'group', 'group_id', 'identifier', 'Group', 4, false, false, false, 1, 3),
				'user' => LightMetaProperty::fill(new LightMetaProperty(), 'user', 'user_id', 'identifier', 'User', 4, false, false, false, 1, 3)
			);
		}
	}
?>
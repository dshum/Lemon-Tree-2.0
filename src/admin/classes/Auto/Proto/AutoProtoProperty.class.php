<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2009-03-10 19:25:37                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoProtoProperty extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'identifier', 'Property', 4, true, true, false, null, null),
				'item' => LightMetaProperty::fill(new LightMetaProperty(), 'item', 'item_id', 'identifier', 'Item', 4, true, false, false, 1, 3),
				'propertyName' => LightMetaProperty::fill(new LightMetaProperty(), 'propertyName', 'property_name', 'string', null, 50, true, true, false, null, null),
				'propertyClass' => LightMetaProperty::fill(new LightMetaProperty(), 'propertyClass', 'property_class', 'string', null, 50, true, true, false, null, null),
				'propertyDescription' => LightMetaProperty::fill(new LightMetaProperty(), 'propertyDescription', 'property_description', 'string', null, 255, true, true, false, null, null),
				'propertyOrder' => LightMetaProperty::fill(new LightMetaProperty(), 'propertyOrder', 'property_order', 'integer', null, 4, false, true, false, null, null),
				'isRequired' => LightMetaProperty::fill(new LightMetaProperty(), 'isRequired', 'is_required', 'boolean', null, null, false, true, false, null, null),
				'isShow' => LightMetaProperty::fill(new LightMetaProperty(), 'isShow', 'is_show', 'boolean', null, null, false, true, false, null, null),
				'fetchClass' => LightMetaProperty::fill(new LightMetaProperty(), 'fetchClass', 'fetch_class', 'string', null, 50, false, true, false, null, null),
				'fetchStrategyId' => LightMetaProperty::fill(new LightMetaProperty(), 'fetchStrategyId', 'fetch_strategy_id', 'integer', null, 4, false, true, false, null, null),
				'onDelete' => LightMetaProperty::fill(new LightMetaProperty(), 'onDelete', 'on_delete', 'string', null, 50, false, true, false, null, null),
				'isParent' => LightMetaProperty::fill(new LightMetaProperty(), 'isParent', 'is_parent', 'boolean', null, null, false, true, false, null, null)
			);
		}
	}
?>
<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2009-07-24 14:51:10                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoProtoBind2Item extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'Bind2Item', 4, true, true, false, null, null),
				'item' => LightMetaProperty::fill(new LightMetaProperty(), 'item', 'item_id', 'identifier', 'Item', 4, true, false, false, 1, 3),
				'bindItem' => LightMetaProperty::fill(new LightMetaProperty(), 'bindItem', 'bind_item_id', 'identifier', 'Item', 4, true, false, false, 1, 3)
			);
		}
	}
?>
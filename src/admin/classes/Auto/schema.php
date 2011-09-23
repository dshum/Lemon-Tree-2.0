<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2009-10-15 10:34:58                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	$schema = new DBSchema();
	
	$schema->
		addTable(
			DBTable::create('cytrus_item')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'item_name'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'item_description'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'item_order'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'class_type'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'parent_class'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(255),
					'main_property_description'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TEXT),
					'main_property_parameters'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN),
					'is_folder'
				)->
				setDefault(false)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'path_prefix'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'order_field'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN),
					'order_direction'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'per_page'
				)->
				setDefault(0)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_property')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'item_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'property_name'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'property_class'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'property_description'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'property_order'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TEXT),
					'property_parameters'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN),
					'is_required'
				)->
				setDefault(false)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN),
					'is_show'
				)->
				setDefault(false)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'fetch_class'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'fetch_strategy_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(50),
					'on_delete'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN),
					'is_parent'
				)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_bind_to_item')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'item_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'bind_item_id'
				)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_bind_to_element')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'element_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'bind_item_id'
				)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_group')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'parent_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'group_description'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'owner_permission'
				)->
				setDefault(0)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'group_permission'
				)->
				setDefault(0)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'world_permission'
				)->
				setDefault(0)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN)->
					setNull(false),
					'is_developer'
				)->
				setDefault(false)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::BOOLEAN)->
					setNull(false),
					'is_admin'
				)->
				setDefault(false)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_user')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'group_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'user_name'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'user_password'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'user_description'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setSize(255),
					'user_email'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TEXT),
					'user_parameters'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TIMESTAMP)->
					setNull(false),
					'registration_date'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TIMESTAMP)->
					setNull(false),
					'login_date'
				)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_user_action')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'user_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'action_type_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TEXT),
					'elements'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'url'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::TIMESTAMP)->
					setNull(false),
					'date'
				)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_item_permission')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'group_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'item_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'owner_permission'
				)->
				setDefault(0)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'group_permission'
				)->
				setDefault(0)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'world_permission'
				)->
				setDefault(0)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('cytrus_element_permission')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'group_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'element_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'permission'
				)->
				setDefault(0)
			)
		);
	
	$schema->
		addTable(
			DBTable::create('root')->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER)->
					setNull(false),
					'id'
				)->
				setPrimaryKey(true)->
				setAutoincrement(true)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'element_name'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'element_order'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(50),
					'status'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::VARCHAR)->
					setNull(false)->
					setSize(255),
					'element_path'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'group_id'
				)
			)->
			addColumn(
				DBColumn::create(
					DataType::create(DataType::INTEGER),
					'user_id'
				)
			)
		);
	
	// cytrus_property.item_id -> cytrus_item.id
	$schema->
		getTableByName('cytrus_property')->
		getColumnByName('item_id')->
		setReference(
			$schema->
				getTableByName('cytrus_item')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_bind_to_item.item_id -> cytrus_item.id
	$schema->
		getTableByName('cytrus_bind_to_item')->
		getColumnByName('item_id')->
		setReference(
			$schema->
				getTableByName('cytrus_item')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_bind_to_item.bind_item_id -> cytrus_item.id
	$schema->
		getTableByName('cytrus_bind_to_item')->
		getColumnByName('bind_item_id')->
		setReference(
			$schema->
				getTableByName('cytrus_item')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_bind_to_element.bind_item_id -> cytrus_item.id
	$schema->
		getTableByName('cytrus_bind_to_element')->
		getColumnByName('bind_item_id')->
		setReference(
			$schema->
				getTableByName('cytrus_item')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_group.parent_id -> cytrus_group.id
	$schema->
		getTableByName('cytrus_group')->
		getColumnByName('parent_id')->
		setReference(
			$schema->
				getTableByName('cytrus_group')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_user.group_id -> cytrus_group.id
	$schema->
		getTableByName('cytrus_user')->
		getColumnByName('group_id')->
		setReference(
			$schema->
				getTableByName('cytrus_group')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_user_action.user_id -> cytrus_user.id
	$schema->
		getTableByName('cytrus_user_action')->
		getColumnByName('user_id')->
		setReference(
			$schema->
				getTableByName('cytrus_user')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_item_permission.group_id -> cytrus_group.id
	$schema->
		getTableByName('cytrus_item_permission')->
		getColumnByName('group_id')->
		setReference(
			$schema->
				getTableByName('cytrus_group')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_item_permission.item_id -> cytrus_item.id
	$schema->
		getTableByName('cytrus_item_permission')->
		getColumnByName('item_id')->
		setReference(
			$schema->
				getTableByName('cytrus_item')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
	// cytrus_element_permission.group_id -> cytrus_group.id
	$schema->
		getTableByName('cytrus_element_permission')->
		getColumnByName('group_id')->
		setReference(
			$schema->
				getTableByName('cytrus_group')->
				getColumnByName('id'),
				ForeignChangeAction::restrict(),
				ForeignChangeAction::cascade()
			);
	
?>
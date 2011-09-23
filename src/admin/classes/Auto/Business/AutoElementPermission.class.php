<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1 at 2009-03-10 19:17:16                           *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoElementPermission extends IdentifiableObject
	{
		protected $group = null;
		protected $groupId = null;
		protected $elementId = null;
		protected $permission = 0;
		
		/**
		 * @return Group
		**/
		public function getGroup()
		{
			if (!$this->group) {
				$this->group = Group::dao()->getById($this->groupId);
			}
			
			return $this->group;
		}
		
		public function getGroupId()
		{
			return $this->groupId;
		}
		
		/**
		 * @return Group
		**/
		public function setGroup(Group $group)
		{
			$this->group = $group;
			$this->groupId = $group->getId();
			
			return $this;
		}
		
		/**
		 * @return Group
		**/
		public function setGroupId($id)
		{
			$this->group = null;
			$this->groupId = $id;
			
			return $this;
		}
		
		/**
		 * @return ElementPermission
		**/
		public function dropGroup()
		{
			$this->group = null;
			$this->groupId = null;
			
			return $this;
		}
		
		public function getElementId()
		{
			return $this->elementId;
		}
		
		/**
		 * @return ElementPermission
		**/
		public function setElementId($elementId)
		{
			$this->elementId = $elementId;
			
			return $this;
		}
		
		public function getPermission()
		{
			return $this->permission;
		}
		
		/**
		 * @return ElementPermission
		**/
		public function setPermission($permission)
		{
			$this->permission = $permission;
			
			return $this;
		}
	}
?>
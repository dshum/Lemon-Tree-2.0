<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2010-01-21 00:50:11                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoGroup extends IdentifiableObject
	{
		protected $parent = null;
		protected $parentId = null;
		protected $groupDescription = null;
		protected $ownerPermission = 0;
		protected $groupPermission = 0;
		protected $worldPermission = 0;
		protected $isSearch = false;
		protected $isDeveloper = false;
		protected $isAdmin = false;
		
		/**
		 * @return Group
		**/
		public function getParent()
		{
			if (!$this->parent && $this->parentId) {
				$this->parent = Group::dao()->getById($this->parentId);
			}
			
			return $this->parent;
		}
		
		public function getParentId()
		{
			return $this->parentId;
		}
		
		/**
		 * @return Group
		**/
		public function setParent(Group $parent)
		{
			$this->parent = $parent;
			$this->parentId = $parent->getId();
			
			return $this;
		}
		
		/**
		 * @return Group
		**/
		public function setParentId($id)
		{
			$this->parent = null;
			$this->parentId = $id;
			
			return $this;
		}
		
		/**
		 * @return Group
		**/
		public function dropParent()
		{
			$this->parent = null;
			$this->parentId = null;
			
			return $this;
		}
		
		public function getGroupDescription()
		{
			return $this->groupDescription;
		}
		
		/**
		 * @return Group
		**/
		public function setGroupDescription($groupDescription)
		{
			$this->groupDescription = $groupDescription;
			
			return $this;
		}
		
		public function getOwnerPermission()
		{
			return $this->ownerPermission;
		}
		
		/**
		 * @return Group
		**/
		public function setOwnerPermission($ownerPermission)
		{
			$this->ownerPermission = $ownerPermission;
			
			return $this;
		}
		
		public function getGroupPermission()
		{
			return $this->groupPermission;
		}
		
		/**
		 * @return Group
		**/
		public function setGroupPermission($groupPermission)
		{
			$this->groupPermission = $groupPermission;
			
			return $this;
		}
		
		public function getWorldPermission()
		{
			return $this->worldPermission;
		}
		
		/**
		 * @return Group
		**/
		public function setWorldPermission($worldPermission)
		{
			$this->worldPermission = $worldPermission;
			
			return $this;
		}
		
		public function getIsSearch()
		{
			return $this->isSearch;
		}
		
		public function isIsSearch()
		{
			return $this->isSearch;
		}
		
		/**
		 * @return Group
		**/
		public function setIsSearch($isSearch = false)
		{
			$this->isSearch = ($isSearch === true);
			
			return $this;
		}
		
		public function getIsDeveloper()
		{
			return $this->isDeveloper;
		}
		
		public function isIsDeveloper()
		{
			return $this->isDeveloper;
		}
		
		/**
		 * @return Group
		**/
		public function setIsDeveloper($isDeveloper = false)
		{
			$this->isDeveloper = ($isDeveloper === true);
			
			return $this;
		}
		
		public function getIsAdmin()
		{
			return $this->isAdmin;
		}
		
		public function isIsAdmin()
		{
			return $this->isAdmin;
		}
		
		/**
		 * @return Group
		**/
		public function setIsAdmin($isAdmin = false)
		{
			$this->isAdmin = ($isAdmin === true);
			
			return $this;
		}
	}
?>
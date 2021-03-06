<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2011-02-27 10:54:22                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoProperty extends IdentifiableObject
	{
		protected $item = null;
		protected $itemId = null;
		protected $propertyName = null;
		protected $propertyClass = null;
		protected $propertyDescription = null;
		protected $propertyOrder = null;
		protected $propertyParameters = null;
		protected $isRequired = false;
		protected $isShow = false;
		protected $fetchClass = null;
		protected $fetchStrategyId = null;
		protected $onDelete = null;
		protected $isParent = null;
		
		/**
		 * @return Item
		**/
		public function getItem()
		{
			if (!$this->item && $this->itemId) {
				$this->item = Item::dao()->getById($this->itemId);
			}
			
			return $this->item;
		}
		
		public function getItemId()
		{
			return $this->itemId;
		}
		
		/**
		 * @return Property
		**/
		public function setItem(Item $item)
		{
			$this->item = $item;
			$this->itemId = $item->getId();
			
			return $this;
		}
		
		/**
		 * @return Property
		**/
		public function setItemId($id)
		{
			$this->item = null;
			$this->itemId = $id;
			
			return $this;
		}
		
		/**
		 * @return Property
		**/
		public function dropItem()
		{
			$this->item = null;
			$this->itemId = null;
			
			return $this;
		}
		
		public function getPropertyName()
		{
			return $this->propertyName;
		}
		
		/**
		 * @return Property
		**/
		public function setPropertyName($propertyName)
		{
			$this->propertyName = $propertyName;
			
			return $this;
		}
		
		public function getPropertyClass()
		{
			return $this->propertyClass;
		}
		
		/**
		 * @return Property
		**/
		public function setPropertyClass($propertyClass)
		{
			$this->propertyClass = $propertyClass;
			
			return $this;
		}
		
		public function getPropertyDescription()
		{
			return $this->propertyDescription;
		}
		
		/**
		 * @return Property
		**/
		public function setPropertyDescription($propertyDescription)
		{
			$this->propertyDescription = $propertyDescription;
			
			return $this;
		}
		
		public function getPropertyOrder()
		{
			return $this->propertyOrder;
		}
		
		/**
		 * @return Property
		**/
		public function setPropertyOrder($propertyOrder)
		{
			$this->propertyOrder = $propertyOrder;
			
			return $this;
		}
		
		public function getPropertyParameters()
		{
			return $this->propertyParameters;
		}
		
		/**
		 * @return Property
		**/
		public function setPropertyParameters($propertyParameters)
		{
			$this->propertyParameters = $propertyParameters;
			
			return $this;
		}
		
		public function getIsRequired()
		{
			return $this->isRequired;
		}
		
		public function isIsRequired()
		{
			return $this->isRequired;
		}
		
		/**
		 * @return Property
		**/
		public function setIsRequired($isRequired = false)
		{
			$this->isRequired = ($isRequired === true);
			
			return $this;
		}
		
		public function getIsShow()
		{
			return $this->isShow;
		}
		
		public function isIsShow()
		{
			return $this->isShow;
		}
		
		/**
		 * @return Property
		**/
		public function setIsShow($isShow = false)
		{
			$this->isShow = ($isShow === true);
			
			return $this;
		}
		
		public function getFetchClass()
		{
			return $this->fetchClass;
		}
		
		/**
		 * @return Property
		**/
		public function setFetchClass($fetchClass)
		{
			$this->fetchClass = $fetchClass;
			
			return $this;
		}
		
		public function getFetchStrategyId()
		{
			return $this->fetchStrategyId;
		}
		
		/**
		 * @return Property
		**/
		public function setFetchStrategyId($fetchStrategyId)
		{
			$this->fetchStrategyId = $fetchStrategyId;
			
			return $this;
		}
		
		public function getOnDelete()
		{
			return $this->onDelete;
		}
		
		/**
		 * @return Property
		**/
		public function setOnDelete($onDelete)
		{
			$this->onDelete = $onDelete;
			
			return $this;
		}
		
		public function getIsParent()
		{
			return $this->isParent;
		}
		
		public function isIsParent()
		{
			return $this->isParent;
		}
		
		/**
		 * @return Property
		**/
		public function setIsParent($isParent = false)
		{
			$this->isParent = ($isParent === true);
			
			return $this;
		}
	}
?>
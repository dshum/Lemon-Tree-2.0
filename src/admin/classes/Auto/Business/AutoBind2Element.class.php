<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2009-07-24 14:51:10                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoBind2Element extends IdentifiableObject
	{
		protected $elementId = null;
		protected $bindItem = null;
		protected $bindItemId = null;
		
		public function getElementId()
		{
			return $this->elementId;
		}
		
		/**
		 * @return Bind2Element
		**/
		public function setElementId($elementId)
		{
			$this->elementId = $elementId;
			
			return $this;
		}
		
		/**
		 * @return Item
		**/
		public function getBindItem()
		{
			if (!$this->bindItem && $this->bindItemId) {
				$this->bindItem = Item::dao()->getById($this->bindItemId);
			}
			
			return $this->bindItem;
		}
		
		public function getBindItemId()
		{
			return $this->bindItemId;
		}
		
		/**
		 * @return Bind2Element
		**/
		public function setBindItem(Item $bindItem)
		{
			$this->bindItem = $bindItem;
			$this->bindItemId = $bindItem->getId();
			
			return $this;
		}
		
		/**
		 * @return Bind2Element
		**/
		public function setBindItemId($id)
		{
			$this->bindItem = null;
			$this->bindItemId = $id;
			
			return $this;
		}
		
		/**
		 * @return Bind2Element
		**/
		public function dropBindItem()
		{
			$this->bindItem = null;
			$this->bindItemId = null;
			
			return $this;
		}
	}
?>
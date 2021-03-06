<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.0.8.99 at 2010-01-21 00:32:10                      *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/
/* $Id$ */

	abstract class AutoUserAction extends IdentifiableObject
	{
		protected $user = null;
		protected $userId = null;
		protected $actionTypeId = null;
		protected $comments = null;
		protected $url = null;
		protected $date = null;
		
		/**
		 * @return User
		**/
		public function getUser()
		{
			if (!$this->user && $this->userId) {
				$this->user = User::dao()->getById($this->userId);
			}
			
			return $this->user;
		}
		
		public function getUserId()
		{
			return $this->userId;
		}
		
		/**
		 * @return UserAction
		**/
		public function setUser(User $user)
		{
			$this->user = $user;
			$this->userId = $user->getId();
			
			return $this;
		}
		
		/**
		 * @return UserAction
		**/
		public function setUserId($id)
		{
			$this->user = null;
			$this->userId = $id;
			
			return $this;
		}
		
		/**
		 * @return UserAction
		**/
		public function dropUser()
		{
			$this->user = null;
			$this->userId = null;
			
			return $this;
		}
		
		public function getActionTypeId()
		{
			return $this->actionTypeId;
		}
		
		/**
		 * @return UserAction
		**/
		public function setActionTypeId($actionTypeId)
		{
			$this->actionTypeId = $actionTypeId;
			
			return $this;
		}
		
		public function getComments()
		{
			return $this->comments;
		}
		
		/**
		 * @return UserAction
		**/
		public function setComments($comments)
		{
			$this->comments = $comments;
			
			return $this;
		}
		
		public function getUrl()
		{
			return $this->url;
		}
		
		/**
		 * @return UserAction
		**/
		public function setUrl($url)
		{
			$this->url = $url;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getDate()
		{
			return $this->date;
		}
		
		/**
		 * @return UserAction
		**/
		public function setDate(Timestamp $date)
		{
			$this->date = $date;
			
			return $this;
		}
		
		/**
		 * @return UserAction
		**/
		public function dropDate()
		{
			$this->date = null;
			
			return $this;
		}
	}
?>
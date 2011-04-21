<?php
	final class Group extends AutoGroup implements Prototyped, DAOConnected
	{
		/**
		 * @return Group
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return GroupDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('GroupDAO');
		}

		/**
		 * @return ProtoGroup
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoGroup');
		}

		public function getParentList()
		{
			$parentList = array();

			$group = clone $this;
			$count = 0;

			while($count < 100 && $group) {
				$count++;
				$group = $group->getParent();
				if($group) {
					$parentList[] = $group;
				}
			}

			krsort($parentList);

			return $parentList;
		}

		public function isAllowed()
		{
			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			$parentList = $this->getParentList();

			foreach($parentList as $parent) {
				if($parent->getId() == $loggedUserGroup->getId()) {
					return true;
				}
			}

			return false;
		}

		public function isAddAllowed()
		{
			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			if($this->getId() == $loggedUserGroup->getId()) {
				return true;
			}

			$parentList = $this->getParentList();

			foreach($parentList as $parent) {
				if($parent->getId() == $loggedUserGroup->getId()) {
					return true;
				}
			}

			return false;
		}
	}
?>
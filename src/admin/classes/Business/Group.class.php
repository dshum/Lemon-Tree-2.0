<?php
	final class Group extends AutoGroup implements Prototyped, DAOConnected
	{
		const SYSTEM_USER_GROUP_ID = 1;

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
	}
?>
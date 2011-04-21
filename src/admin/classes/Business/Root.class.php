<?php
	class Root extends AutoRoot implements Prototyped, DAOConnected
	{
		const RootID = 1;
		const TrashID = 2;
		const HellID = 3;

		/**
		 * @return Root
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return RootDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('RootDAO');
		}

		/**
		 * @return ProtoRoot
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoRoot');
		}

		public static function me()
		{
			return self::dao()->getById(self::RootID);
		}

		public static function trash()
		{
			return self::dao()->getById(self::TrashID);
		}

		public static function hell()
		{
			return self::dao()->getById(self::HellID);
		}

		public function getItem()
		{
			return null;
		}

		public function getItemId()
		{
			return null;
		}

		public function getParent()
		{
			return null;
		}

		public function getParentList()
		{
			return array();
		}
	}
?>
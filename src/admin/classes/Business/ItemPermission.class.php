<?php
	final class ItemPermission extends AutoItemPermission implements Prototyped, DAOConnected
	{
		/**
		 * @return ItemPermission
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return ItemPermissionDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('ItemPermissionDAO');
		}

		/**
		 * @return ProtoItemPermission
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoItemPermission');
		}
	}
?>
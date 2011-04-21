<?php
	final class ElementPermission extends AutoElementPermission implements Prototyped, DAOConnected
	{
		/**
		 * @return ElementPermission
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return ElementPermissionDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('ElementPermissionDAO');
		}

		/**
		 * @return ProtoElementPermission
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoElementPermission');
		}
	}
?>
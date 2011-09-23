<?php
	final class User extends AutoUser implements Prototyped, DAOConnected
	{
		const LABEL = 'LTUser';

		private static $loggedUser = null;

		/**
		 * @return User
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return UserDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('UserDAO');
		}

		/**
		 * @return ProtoUser
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoUser');
		}
	}
?>
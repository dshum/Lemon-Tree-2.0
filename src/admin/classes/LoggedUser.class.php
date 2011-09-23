<?php
	class LoggedUser
	{
		private static $label = null;
		private static $class = null;
		private static $user = null;

		public function create()
		{
			return new self();
		}

		public static function setLabel($label)
		{
			self::$label = $label;
		}

		public static function getLabel()
		{
			return self::$label;
		}

		public static function setClass($class)
		{
			self::$class = $class;
		}

		public static function getClass()
		{
			return self::$class;
		}

		public static function setUser($user)
		{
			if(!self::$label) {
				throw new WrongStateException('User label is not instantiated.');
			} elseif(!self::$class) {
				throw new WrongStateException('User class is not instantiated.');
			} elseif(!ClassUtils::isInstanceOf($user, self::$class)) {
				throw new WrongArgumentException('User class must be instance of '.self::$class.'.');
			} else {
				self::$user = $user;
			}
		}

		public static function getUser()
		{
			return self::$user;
		}

		public static function dropUser()
		{
			self::$user = null;
		}

		public static function isLoggedIn()
		{
			return
				self::$label && self::$class && self::$user
				&& ClassUtils::isInstanceOf(self::$user, self::$class);
		}
	}
?>
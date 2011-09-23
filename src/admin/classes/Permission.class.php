<?php
	final class Permission
	{
		const PERMISSION_DENIED_ID = 0;
		const PERMISSION_READ_ID = 1;
		const PERMISSION_WRITE_ID = 2;
		const PERMISSION_DROP_ID = 3;
		const PERMISSION_FULL_ID = 4;

		private static $permissionNameList = array(
			self::PERMISSION_DENIED_ID => 'Доступ закрыт',
			self::PERMISSION_READ_ID => 'Только чтение',
			self::PERMISSION_WRITE_ID => 'Чтение и запись',
			self::PERMISSION_DROP_ID => 'Право на удаление',
			self::PERMISSION_FULL_ID => 'Полный доступ',
		);

		private static $permissionTitleList = array(
			self::PERMISSION_DENIED_ID => 'Доступ<br>закрыт',
			self::PERMISSION_READ_ID => 'Только<br>чтение',
			self::PERMISSION_WRITE_ID => 'Чтение и<br>запись',
			self::PERMISSION_DROP_ID => 'Право на<br>удаление',
			self::PERMISSION_FULL_ID => 'Полный<br>доступ',
		);

		public static function create()
		{
			return new self();
		}

		public static function getPermissionNameList()
		{
			return self::$permissionNameList;
		}

		public static function getPermissionTitleList()
		{
			return self::$permissionTitleList;
		}

		public static function getPermissionName($id)
		{
			return
				isset(self::$permissionNameList[$id])
				? self::$permissionNameList[$id]
				: null;
		}

		public static function permissionExists($id)
		{
			return isset(self::$permissionNameList[$id]);
		}
	}
?>
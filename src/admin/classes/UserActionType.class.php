<?php
	final class UserActionType
	{
		const ACTION_TYPE_ADD_ELEMENT_ID = 1;
		const ACTION_TYPE_SAVE_ELEMENT_ID = 2;
		const ACTION_TYPE_SAVE_ELEMENT_LIST_ID = 3;
		const ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID = 4;
		const ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID = 5;
		const ACTION_TYPE_DROP_ELEMENT_ID = 6;
		const ACTION_TYPE_DROP_ELEMENT_LIST_ID = 7;
		const ACTION_TYPE_RESTORE_ELEMENT_ID = 8;
		const ACTION_TYPE_RESTORE_ELEMENT_LIST_ID = 9;
		const ACTION_TYPE_MOVE_ELEMENT_LIST_ID = 10;
		const ACTION_TYPE_ORDER_ELEMENT_LIST_ID = 11;
		const ACTION_TYPE_PLUGIN_ID = 12;
		const ACTION_TYPE_LOGIN_ID = 13;

		private static $actionTypeNameList = array(
			self::ACTION_TYPE_ADD_ELEMENT_ID => 'Добавление элемента',
			self::ACTION_TYPE_SAVE_ELEMENT_ID => 'Сохранение элемента',
			self::ACTION_TYPE_SAVE_ELEMENT_LIST_ID => 'Сохранение списка элементов',
			self::ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID => 'Удаление элемента в корзину',
			self::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID => 'Удаление списка элементов в корзину',
			self::ACTION_TYPE_DROP_ELEMENT_ID => 'Удаление элемента',
			self::ACTION_TYPE_DROP_ELEMENT_LIST_ID => 'Удаление списка элементов',
			self::ACTION_TYPE_RESTORE_ELEMENT_ID => 'Восстановление элемента из корзины',
			self::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID => 'Восстановление списка элементов из корзины',
			self::ACTION_TYPE_MOVE_ELEMENT_LIST_ID => 'Перемещение списка элементов',
			self::ACTION_TYPE_ORDER_ELEMENT_LIST_ID => 'Сортировка списка элементов',
			self::ACTION_TYPE_PLUGIN_ID => 'Плагин',
			self::ACTION_TYPE_LOGIN_ID => 'Авторизация',
		);

		public static function create()
		{
			return new self();
		}

		public static function getActionTypeNameList()
		{
			return self::$actionTypeNameList;
		}

		public static function getActionTypeName($id)
		{
			return
				isset(self::$actionTypeNameList[$id])
				? self::$actionTypeNameList[$id]
				: null;
		}

		public static function actionTypeExists($id)
		{
			return isset(self::$actionTypeNameList[$id]);
		}
	}
?>
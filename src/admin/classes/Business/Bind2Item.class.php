<?php
	final class Bind2Item extends AutoBind2Item implements Prototyped, DAOConnected
	{
		/**
		 * @return Bind2Item
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return Bind2ItemDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('Bind2ItemDAO');
		}

		/**
		 * @return ProtoBind2Item
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoBind2Item');
		}
	}
?>
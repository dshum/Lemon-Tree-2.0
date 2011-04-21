<?php
	final class Bind2Element extends AutoBind2Element implements Prototyped, DAOConnected
	{
		/**
		 * @return Bind2Element
		**/
		public static function create()
		{
			return new self;
		}

		/**
		 * @return Bind2ElementDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('Bind2ElementDAO');
		}

		/**
		 * @return ProtoBind2Element
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoBind2Element');
		}
	}
?>
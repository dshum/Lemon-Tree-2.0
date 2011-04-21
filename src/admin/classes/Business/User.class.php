<?php
	final class User extends AutoUser implements Prototyped, DAOConnected
	{
		const LABEL = 'LRUser';

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

		public function getUnserializedParameters()
		{
			try {
				return unserialize($this->userParameters);
			} catch (BaseException $e) {}

			return null;
		}

		public function parameterExists($name)
		{
			$unserializedParameters = $this->getUnserializedParameters();

			return isset($unserializedParameters[$name]);
		}

		public function getParameter($name)
		{
			$unserializedParameters = $this->getUnserializedParameters();

			return
				isset($unserializedParameters[$name])
				? $unserializedParameters[$name]
				: null;
		}

		public function setParameter($name, $value)
		{
			$unserializedParameters = $this->getUnserializedParameters();

			$unserializedParameters[$name] = $value;

			$userParameters = serialize($unserializedParameters);

			$this->setUserParameters($userParameters);

			try {
				$this->dao()->save($this);
			} catch (DatabaseException $e) {}

			return $this;
		}
	}
?>
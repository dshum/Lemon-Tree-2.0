<?php
	final class UserLog extends Singleton implements Instantiatable
	{
		private $logOn = false;

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setLogOn()
		{
			$this->logOn = true;
		}

		public function setLogOff()
		{
			$this->logOn = false;
		}

		public function isLogOn()
		{
			return $this->logOn;
		}

		public function log($actionTypeId, $comments)
		{
			if(!$this->isLogOn()) return null;

			if(!LoggedUser::isLoggedIn()) return null;

			if(!UserActionType::actionTypeExists($actionTypeId)) return null;

			$loggedUser = LoggedUser::getUser();

			$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

			$date = Timestamp::makeNow();

			$userAction =
				UserAction::create()->
				setUser($loggedUser)->
				setActionTypeId($actionTypeId)->
				setComments($comments)->
				setUrl($url)->
				setDate($date);

			try {
				$userAction = UserAction::dao()->add($userAction);
				return $userAction;
			} catch (DatabaseException $e) {
				ErrorMessageUtils::sendMessage($e);
			}

			return null;
		}
	}
?>
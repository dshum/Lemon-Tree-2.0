<?php
	final class UserLog extends Singleton implements Instantiatable
	{
		private $logOn = false;

		private $defaultActionTypeMap = array();
		private $userActionTypeMap = array();

		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function setLogOn()
		{
			$this->logOn = true;

			return $this;
		}

		public function setLogOff()
		{
			$this->logOn = false;

			return $this;
		}

		public function isLogOn()
		{
			return $this->logOn;
		}

		public function addDefaultActionType($actionTypeId)
		{
			$this->defaultActionTypeMap[$actionTypeId] = $actionTypeId;

			return $this;
		}

		public function addUserActionType($userName, $actionTypeId)
		{
			$this->userActionTypeMap[$userName][$actionTypeId] = $actionTypeId;

			return $this;
		}

		public function log($actionTypeId, $comments)
		{
			if(!$this->isLogOn()) return null;

			if(!LoggedUser::isLoggedIn()) return null;

			$loggedUser = LoggedUser::getUser();

			if(!$loggedUser instanceof User) return null;

			if(!UserActionType::actionTypeExists($actionTypeId)) return null;

			if(
				UserActionType::isExcludeActionType($actionTypeId)
				&& !isset($this->defaultActionTypeMap[$actionTypeId])
				&& !isset($this->userActionTypeMap[$loggedUser->getUserName()][$actionTypeId])
			) return null;

			$method =
				isset($_SERVER['REQUEST_METHOD'])
				? strtolower($_SERVER['REQUEST_METHOD'])
				: 'get';

			if($method == 'post') {

				$referer =
					isset($_SERVER["HTTP_REFERER"])
					? $_SERVER['HTTP_REFERER']
					: '';

				$url = $referer;

			} else {

				$server =
					isset($_SERVER['HTTP_HOST'])
					? $_SERVER['HTTP_HOST']
					: (defined('HTTP_HOST') ? HTTP_HOST : '');

				$uri =
					isset($_SERVER['REQUEST_URI'])
					? $_SERVER['REQUEST_URI']
					: '';

				$url = 'http://'.$server.$uri;

			}

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
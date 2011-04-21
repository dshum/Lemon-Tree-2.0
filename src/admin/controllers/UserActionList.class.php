<?php
	final class UserActionList extends MethodMappedController
	{
		private static $perPage = 100;
		private static $pageLimit = 15;

		public function __construct()
		{
			$this->
			setMethodMapping('show', 'showList')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			if(!$loggedUser->getGroup()->getIsAdmin()) {
				return
					ModelAndView::create()->
					setModel(
						Model::create()->
						set('userId', $loggedUser->getId())
					)->
					setView(new RedirectToView('UserEdit'));
			}

			return parent::handleRequest($request);
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

			$actionTypeList = UserActionType::getActionTypeNameList();

			$today = Date::makeToday();
			$yesterday = $today->spawn('-1 day');
			$week = $today->spawn('-1 week');
			$month = $today->spawn('-1 month');
			$total = UserAction::dao()->getFirstDate();

			$form =
				Form::create()->
				add(
					Primitive::identifier('userId')->
					of('User')
				)->
				add(
					Primitive::choice('actionType')->
					setList($actionTypeList)
				)->
				add(
					Primitive::string('text')
				)->
				add(
					Primitive::date('dateFrom')
				)->
				add(
					Primitive::date('dateTo')
				)->
				add(
					Primitive::integer('page')
				)->
				import($request->getGet());

			$currentUser = $form->getValue('userId');

			if($currentUser) {
				$currentGroup = $currentUser->getGroup();
				$parentList = $currentGroup->getParentList();
			} else {
				$currentGroup = null;
				$parentList = array();
			}

			$actionTypeId = $form->getValue('actionType');

			$text = $form->getValue('text');

			$dateFrom =
				$form->getValue('dateFrom')
				? $form->getValue('dateFrom')
				: $total;

			$dateTo =
				$form->getValue('dateTo')
				? $form->getValue('dateTo')
				: Date::makeToday();

			if($dateTo->toString() < $dateFrom->toString()) {
				$tmp = $dateTo;
				$dateTo = $dateFrom;
				$dateFrom = $tmp;
			}

			$currentPage = $form->getValue('page');

			$userActionListCriteria =
				Criteria::create(UserAction::dao())->
				add(
					Expression::gtEq(
						new DBField('date', UserAction::dao()->getTable()),
						new DBValue($dateFrom->toString())
					)
				)->
				add(
					Expression::lt(
						new DBField('date', UserAction::dao()->getTable()),
						new DBValue($dateTo->spawn('+1 day')->toString())
					)
				)->
				addOrder(
					OrderBy::create(
						new DBField('date', UserAction::dao()->getTable())
					)->desc()
				);

			if($currentUser) {
				$userActionListCriteria->
				add(
					Expression::eqId(
						new DBField('user_id', UserAction::dao()->getTable()),
						$currentUser
					)
				);
			}

			if($actionTypeId) {
				$userActionListCriteria->
				add(
					Expression::eq(
						new DBField('action_type_id', UserAction::dao()->getTable()),
						new DBValue($actionTypeId)
					)
				);
			}

			if($text) {
				$userActionListCriteria->
				add(
					Expression::like(
						new DBField('comments', UserAction::dao()->getTable()),
						new DBValue('%'.$text.'%')
					)
				);
			}

			$pager =
				CriteriaPager::create($userActionListCriteria)->
				setPerpage(self::$perPage)->
				setPageLimit(self::$pageLimit)->
				setCurrentPage($currentPage);

			$userActionList = $pager->getList();

			$sizeofUserActionList = sizeof($userActionList);

			$model->set('actionTypeList', $actionTypeList);
			$model->set('today', $today);
			$model->set('yesterday', $yesterday);
			$model->set('week', $week);
			$model->set('month', $month);
			$model->set('total', $total);
			$model->set('currentUser', $currentUser);
			$model->set('currentGroup', $currentGroup);
			$model->set('parentList', $parentList);
			$model->set('actionTypeId', $actionTypeId);
			$model->set('text', $text);
			$model->set('dateFrom', $dateFrom);
			$model->set('dateTo', $dateTo);
			$model->set('pager', $pager);
			$model->set('userActionList', $userActionList);
			$model->set('sizeofUserActionList', $sizeofUserActionList);

			return ModelAndView::create()->
				setModel($model)->
				setView('UserActionList');
		}
	}
?>
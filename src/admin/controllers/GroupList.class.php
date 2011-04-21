<?php
	final class GroupList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('show', 'show')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function show(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			$model = Model::create();

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

			$form =
				Form::create()->
				add(
					Primitive::identifier('groupId')->
					of('Group')->
					required()
				)->
				import($_GET);

			if($form->getErrors()) {
				$currentGroup = $loggedUser->getGroup();
			} else {
				$currentGroup = $form->getValue('groupId');
				if(
					$currentGroup->getId() != $loggedUser->getGroup()->getId()
					&& !$currentGroup->isAllowed()
				) {
					$currentGroup = $loggedUser->getGroup();
				}
			}

			$parentList = $currentGroup->getParentList();

			$groupList =
				Criteria::create(Group::dao())->
				add(
					Expression::eqId(
						new DBField('parent_id', Group::dao()->getTable()),
						$currentGroup
					)
				)->
				addOrder(
					new DBField('group_description', Group::dao()->getTable())
				)->
				getList();

			if($currentGroup->getId() == $loggedUser->getGroup()->getId()) {
				$userList = array($loggedUser);
			} else {
				$userList =
					Criteria::create(User::dao())->
					add(
						Expression::eqId(
							new DBField('group_id', User::dao()->getTable()),
							$currentGroup
						)
					)->
					addOrder(
						new DBField('user_name', User::dao()->getTable())
					)->
					getList();
			}

			$model->set('currentGroup', $currentGroup);
			$model->set('parentList', $parentList);
			$model->set('groupList', $groupList);
			$model->set('userList', $userList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('GroupList');
		}
	}
?>
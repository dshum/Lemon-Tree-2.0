<?php
	final class GroupList extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('dropGroup', 'dropGroup')->
			setMethodMapping('dropUser', 'dropUser')->
			setMethodMapping('show', 'showList')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function dropGroup(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::identifier('groupId')->
				of('Group')->
				required()
			)->
			import($_POST);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				$loggedUser = LoggedUser::getUser();
				$group = $loggedUser->getGroup();

				if($group->getId() == $currentGroup->getId()) {
					$model->set('selfGroup', true);
				} else {
					$countGroup = Group::dao()->getCountByParent($currentGroup);
					$countUser = User::dao()->getCountByGroup($currentGroup);

					if($countGroup || $countUser) {
						$model->set('notEmpty', true);
					} else {
						Group::dao()->drop($currentGroup);
					}
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupList');
		}

		public function dropUser(HttpRequest $request)
		{
			$model = Model::create();

			$form = Form::create();

			$form->
			add(
				Primitive::identifier('userId')->
				of('User')->
				required()
			)->
			import($_POST);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentUser = $form->getValue('userId');

				$loggedUser = LoggedUser::getUser();

				if($loggedUser->getId() == $currentUser->getId()) {
					$model->set('selfUser', true);
				} else {
					User::dao()->drop($currentUser);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupList');
		}

		public function showList(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('groupId')->
					of('Group')->
					required()
				)->
				import($_GET);

			if($form->getErrors()) {
				$currentGroup = Group::dao()->getById(Group::SYSTEM_USER_GROUP_ID);
			} else {
				$currentGroup = $form->getValue('groupId');
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
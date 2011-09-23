<?php
	final class UserEdit extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('add', 'addUser')->
				setMethodMapping('save', 'saveUser')->
				setMethodMapping('create', 'createUser')->
				setMethodMapping('edit', 'editUser')->
				setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function addUser(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeAddForm();

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');
				$userPassword = md5($form->getValue('userPassword'));

				$currentUser =
					User::create()->
					setGroup($currentGroup)->
					setUserName($form->getValue('userName'))->
					setUserPassword($userPassword)->
					setUserDescription($form->getValue('userDescription'))->
					setUserEmail($form->getValue('userEmail'))->
					setRegistrationDate(Timestamp::makeNow())->
					setLoginDate(Timestamp::makeNow());


				try {
					# Add user
					$currentUser = User::dao()->add($currentUser);
				} catch (DatabaseException $e) {
					$model->set('addUserError', true);
				}

				$model->set('groupId', $currentGroup->getId());
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/UserEdit');
		}

		public function saveUser(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeSaveForm();

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentUser = $form->getValue('userId');
				$userPassword = md5($form->getValue('userPassword'));

				$currentUser->
				setUserName($form->getValue('userName'))->
				setUserDescription($form->getValue('userDescription'))->
				setUserEmail($form->getValue('userEmail'));

				if($form->getValue('userPassword')) {
					$currentUser->setUserPassword($userPassword);
				}

				try {
					# Save user
					$currentUser = User::dao()->save($currentUser);
				} catch (BaseException $e) {
					$model->set('saveUserError', true);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/UserEdit');
		}

		public function createUser(HttpRequest $request)
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

				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('GroupList'));

			} else {

				$currentGroup = $form->getValue('groupId');
				$currentUser =
					User::create()->
					setGroup($currentGroup)->
					setRegistrationDate(Timestamp::makeNow())->
					setLoginDate(Timestamp::makeNow());
				$parentList = $currentGroup->getParentList();


				$model->set('mode', 'edit');
				$model->set('currentUser', $currentUser);
				$model->set('currentGroup', $currentGroup);
				$model->set('parentList', $parentList);

				return
					ModelAndView::create()->
					setModel($model)->
					setView('UserEdit');
			}
		}

		public function editUser(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('userId')->
					of('User')->
					required()
				)->
				import($_GET);

			if($form->getErrors()) {

				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('GroupList'));

			} else {

				$currentUser = $form->getValue('userId');
				$currentGroup = $currentUser->getGroup();
				$parentList = $currentGroup->getParentList();


				$model->set('mode', 'edit');
				$model->set('currentUser', $currentUser);
				$model->set('currentGroup', $currentGroup);
				$model->set('parentList', $parentList);

				return
					ModelAndView::create()->
					setModel($model)->
					setView('UserEdit');
			}
		}

		private function makeAddForm()
		{
			$form = Form::create();

			$form->
			add(
				Primitive::identifier('groupId')->
				of('Group')->
				required()
			)->
			add(
				Primitive::string('userName')->
				setAllowedPattern(RegexpUtils::getLoginPattern())->
				required()->
				setMax(50)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('userPassword')->
				required()->
				setMin(4)->
				setMax(50)
			)->
			add(
				Primitive::string('userDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::string('userEmail')->
				setAllowedPattern(RegexpUtils::getEmailPattern())->
				optional()->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			import($_POST);

			return $form;
		}

		private function makeSaveForm()
		{
			$form = Form::create();

			$form->
			add(
				Primitive::identifier('userId')->
				of('User')->
				required()
			)->
			add(
				Primitive::string('userName')->
				setAllowedPattern(RegexpUtils::getLoginPattern())->
				required()->
				setMax(50)->
				addImportFilter(Filter::trim())
			)->
			add(
				Primitive::string('userPassword')->
				optional()->
				setMin(4)->
				setMax(50)
			)->
			add(
				Primitive::string('userDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::string('userEmail')->
				setAllowedPattern(RegexpUtils::getEmailPattern())->
				optional()->
				setMax(255)->
				addImportFilter(Filter::trim())
			)->
			import($_POST);

			return $form;
		}
	}
?>
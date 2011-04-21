<?php
	final class UserEdit extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('drop', 'drop')->
			setMethodMapping('add', 'add')->
			setMethodMapping('save', 'save')->
			setMethodMapping('create', 'create')->
			setMethodMapping('edit', 'edit')->
			setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function drop(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('userId')->
					of('User')->
					required()
				)->
				import($request->getPost());

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentUser = $form->getValue('userId');
				$currentGroup = $currentUser->getGroup();

				if($currentGroup->isAllowed()) {
					try {
						User::dao()->dropUser($currentUser);
					} catch (DatabaseException $e) {
						$model->set('dropUserError', true);
					}
				} else {
					$model->set('deniedDropUserError', true);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/UserEdit');
		}

		public function add(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			$model = Model::create();

			$form = $this->makeAddForm($request);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				if($currentGroup->isAllowed()) {

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
						$currentUser = User::dao()->add($currentUser);
						$model->set('groupId', $currentGroup->getId());
					} catch (DatabaseException $e) {
						$model->set('addUserError', true);
					}

				} else {

					$model->set('deniedAddUserError', true);

				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/UserEdit');
		}

		public function save(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			$model = Model::create();

			$form = $this->makeSaveForm($request);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentUser = $form->getValue('userId');

				$currentGroup = $currentUser->getGroup();

				if(
					$currentUser->getId() == $loggedUser->getId()
					|| $currentGroup->isAllowed()
				) {

					$userPassword = md5($form->getValue('userPassword'));

					$currentUser->
					setUserName($form->getValue('userName'))->
					setUserDescription($form->getValue('userDescription'))->
					setUserEmail($form->getValue('userEmail'));

					if($form->getValue('userPassword')) {
						$currentUser->setUserPassword($userPassword);
					}

					try {
						$currentUser = User::dao()->save($currentUser);
					} catch (BaseException $e) {
						$model->set('saveUserError', true);
					}

				} else {

					$model->set('deniedSaveUserError', true);

				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/UserEdit');
		}

		public function create(HttpRequest $request)
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
			}

			$currentGroup = $form->getValue('groupId');

			if(!$currentGroup->isAllowed()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('GroupList'));
			}

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

		public function edit(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();

			$model = Model::create();

			$requestUri = $request->getServerVar('REQUEST_URI');
			Session::assign('browseLastUrl', $requestUri);

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
			}

			$currentUser = $form->getValue('userId');

			$currentGroup = $currentUser->getGroup();

			if(
				$currentUser->getId() != $loggedUser->getId()
				&& !$currentGroup->isAllowed()
			) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('GroupList'));
			}

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

		private function makeAddForm(HttpRequest $request)
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
			import($request->getPost());

			return $form;
		}

		private function makeSaveForm(HttpRequest $request)
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
			import($request->getPost());

			return $form;
		}
	}
?>
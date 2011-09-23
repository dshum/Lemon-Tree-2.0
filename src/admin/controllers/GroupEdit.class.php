<?php
	final class GroupEdit extends MethodMappedController
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

		public function drop(HttpRequest $request)
		{
			$loggedUser = LoggedUser::getUser();
			$loggedUserGroup = $loggedUser->getGroup();

			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('groupId')->
					of('Group')->
					required()
				)->
				import($request->getPost());

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				if($currentGroup->isAllowed()) {

					$countGroup = Group::dao()->getCountByParent($currentGroup);
					$countUser = User::dao()->getCountByGroup($currentGroup);

					if($countGroup || $countUser) {
						$model->set('notEmptyGroupError', true);
					} else {
						try {
							Group::dao()->dropGroup($currentGroup);
						} catch (DatabaseException $e) {
							$model->set('dropGroupError', true);
						}
					}

				} else {

					$model->set('deniedDropGroupError', true);

				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupEdit');
		}

		public function add(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeAddForm($request);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$parentGroup = $form->getValue('parentId');

				if($parentGroup->isAddAllowed()) {

					$currentGroup =
						Group::create()->
						setParent($parentGroup)->
						setGroupDescription($form->getValue('groupDescription'))->
						setOwnerPermission($form->getValue('ownerPermission'))->
						setGroupPermission($form->getValue('groupPermission'))->
						setWorldPermission($form->getValue('worldPermission'))->
						setIsDeveloper($form->getValue('isDeveloper'))->
						setIsAdmin($form->getValue('isAdmin'));

					try {
						$currentGroup = Group::dao()->add($currentGroup);
						$model->set('parentId', $currentGroup->getParent()->getId());
					} catch (DatabaseException $e) {
						$model->set('addGroupError', true);
					}

				} else {

					$model->set('deniedAddGroupError', true);

				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupEdit');
		}

		public function save(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeSaveForm($request);

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				if($currentGroup->isAllowed()) {

					$currentGroup->
					setGroupDescription($form->getValue('groupDescription'))->
					setOwnerPermission($form->getValue('ownerPermission'))->
					setGroupPermission($form->getValue('groupPermission'))->
					setWorldPermission($form->getValue('worldPermission'))->
					setIsDeveloper($form->getValue('isDeveloper'))->
					setIsAdmin($form->getValue('isAdmin'));

					try {
						$currentGroup = Group::dao()->save($currentGroup);
					} catch (DatabaseException $e) {
						$model->set('saveGroupError', true);
					}

				} else {

					$model->set('deniedSaveGroupError', true);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupEdit');
		}

		public function create(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(
					Primitive::identifier('parentId')->
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

			$parentGroup = $form->getValue('parentId');

			if(!$parentGroup->isAddAllowed()) {
				return
					ModelAndView::create()->
					setModel(Model::create())->
					setView(new RedirectToView('GroupList'));
			}

			$currentGroup =
				Group::create()->
				setParent($parentGroup);

			$parentList = $currentGroup->getParentList();

			$model->set('parentGroup', $parentGroup);
			$model->set('currentGroup', $currentGroup);
			$model->set('parentList', $parentList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('GroupEdit');
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

			$parentList = $currentGroup->getParentList();

			$model->set('currentGroup', $currentGroup);
			$model->set('parentList', $parentList);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('GroupEdit');
		}

		private function makeAddForm(HttpRequest $request)
		{
			$form = Form::create();

			$form->
			add(
				Primitive::identifier('parentId')->
				of('Group')->
				required()
			)->
			add(
				Primitive::string('groupDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::choice('ownerPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::choice('groupPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::choice('worldPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::boolean('isDeveloper')
			)->
			add(
				Primitive::boolean('isAdmin')
			)->
			import($request->getPost());

			return $form;
		}

		private function makeSaveForm(HttpRequest $request)
		{
			$form = Form::create();

			$form->
			add(
				Primitive::identifier('groupId')->
				of('Group')->
				required()
			)->
			add(
				Primitive::string('groupDescription')->
				required()->
				setMax(255)->
				addImportFilter(Filter::trim())->
				addImportFilter(Filter::stripTags())
			)->
			add(
				Primitive::choice('ownerPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::choice('groupPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::choice('worldPermission')->
				setList(Permission::getPermissionNameList())->
				required()
			)->
			add(
				Primitive::boolean('isDeveloper')
			)->
			add(
				Primitive::boolean('isAdmin')
			)->
			import($request->getPost());

			return $form;
		}
	}
?>
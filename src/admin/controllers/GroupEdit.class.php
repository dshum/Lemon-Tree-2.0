<?php
	final class GroupEdit extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('add', 'addGroup')->
				setMethodMapping('save', 'saveGroup')->
				setMethodMapping('create', 'createGroup')->
				setMethodMapping('edit', 'editGroup')->
				setDefaultAction('edit');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function addGroup(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeAddForm();

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup =
					Group::create()->
					setParent($form->getValue('parentId'))->
					setGroupDescription($form->getValue('groupDescription'))->
					setOwnerPermission($form->getValue('ownerPermission'))->
					setGroupPermission($form->getValue('groupPermission'))->
					setWorldPermission($form->getValue('worldPermission'))->
					setIsDeveloper($form->getValue('isDeveloper'))->
					setIsAdmin($form->getValue('isAdmin'));

				try {
					# Add group
					$currentGroup = Group::dao()->add($currentGroup);
				} catch (DatabaseException $e) {
					$model->set('addGroupError', true);
				}

				$model->set('parentId', $currentGroup->getParent()->getId());
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupEdit');
		}

		public function saveGroup(HttpRequest $request)
		{
			$model = Model::create();

			$form = $this->makeSaveForm();

			if($form->getErrors()) {

				$model->set('form', $form);

			} else {

				$currentGroup = $form->getValue('groupId');

				$currentGroup->
				setGroupDescription($form->getValue('groupDescription'))->
				setOwnerPermission($form->getValue('ownerPermission'))->
				setGroupPermission($form->getValue('groupPermission'))->
				setWorldPermission($form->getValue('worldPermission'))->
				setIsDeveloper($form->getValue('isDeveloper'))->
				setIsAdmin($form->getValue('isAdmin'));

				try {
					# Save group
					$currentGroup = Group::dao()->save($currentGroup);
				} catch (DatabaseException $e) {
					$model->set('saveGroupError', true);
				}
			}

			return
				ModelAndView::create()->
				setModel($model)->
				setView('request/GroupEdit');
		}

		public function createGroup(HttpRequest $request)
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

			} else {

				$parentGroup = $form->getValue('parentId');
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
		}

		public function editGroup(HttpRequest $request)
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
				$parentList = $currentGroup->getParentList();

				$model->set('currentGroup', $currentGroup);
				$model->set('parentList', $parentList);

				return
					ModelAndView::create()->
					setModel($model)->
					setView('GroupEdit');
			}
		}

		private function makeAddForm()
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
			import($_POST);

			return $form;
		}

		private function makeSaveForm()
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
			import($_POST);

			return $form;
		}
	}
?>